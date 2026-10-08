<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningContentImporter;
use App\Services\LearningSqlParser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class LearningContentImportController extends Controller
{
    public function index(Request $request)
    {
        $pending = $request->session()->get('learning_import');
        if ($pending && $pending['expires'] < time()) {
            $this->discard($request);
            $pending = null;
        }

        return view('admin.imports.index', [
            'pending' => $pending,
            'history' => DB::table('learning_content_imports')->latest('id')->limit(20)->get(),
        ]);
    }

    public function preview(Request $request, LearningSqlParser $parser, LearningContentImporter $importer)
    {
        $request->validate(['sql_file' => ['required', 'file', 'extensions:sql', 'max:32768']]);
        $this->discard($request);
        $path = null;
        try {
            foreach (Storage::disk('local')->files('learning-imports') as $stale) {
                if (Storage::disk('local')->lastModified($stale) < time() - 3600) {
                    Storage::disk('local')->delete($stale);
                }
            }
            $file = $request->file('sql_file');
            $data = $parser->parse(file_get_contents($file->getRealPath()));
            $plan = $importer->analyze($data);
            $path = $file->store('learning-imports', 'local');
            if (! $path) {
                throw new RuntimeException('Không lưu được file tạm. Kiểm tra quyền ghi storage.');
            }
            $request->session()->put('learning_import', [
                'path' => $path, 'filename' => mb_substr(basename($file->getClientOriginalName()), 0, 200),
                'hash' => hash_file('sha256', Storage::disk('local')->path($path)),
                'admin_id' => $request->user()->id, 'expires' => time() + 1800,
                'token' => bin2hex(random_bytes(32)), 'counts' => $plan['counts'], 'plan_hash' => $plan['hash'],
            ]);

            return redirect()->route('admin.imports.index');
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            return $this->failure($error);
        }
    }

    public function store(Request $request, LearningSqlParser $parser, LearningContentImporter $importer)
    {
        $request->validate(['token' => ['required', 'string'], 'confirm' => ['accepted']]);
        // File cache lock serializes imports across all admin sessions on one hosting server.
        $lock = Cache::store('file')->lock('learning-content-import', 300);
        if (! $lock->get()) {
            return back()->withErrors(['sql_file' => 'Đang có lượt import khác. Vui lòng thử lại sau.']);
        }
        try {
            $pending = $request->session()->get('learning_import');
            if (! $pending || ! isset($pending['plan_hash']) || $pending['admin_id'] !== $request->user()->id || $pending['expires'] < time() || ! hash_equals($pending['token'], $request->string('token')->toString())) {
                throw new RuntimeException('Preview đã hết hạn hoặc đã sử dụng. Vui lòng upload lại.');
            }
            $path = Storage::disk('local')->path($pending['path']);
            if (! is_file($path) || ! hash_equals($pending['hash'], hash_file('sha256', $path))) {
                throw new RuntimeException('File tạm bị thiếu hoặc đã thay đổi. Vui lòng upload lại.');
            }
            set_time_limit(180);
            $data = $parser->parse(file_get_contents($path));
            $importer->import($data, $request->user()->id, $pending['filename'], $pending['hash'], $pending['plan_hash']);

            return redirect()->route('admin.imports.index')->with('success', 'Đã cập nhật dữ liệu học tập. Dữ liệu không có trong file được giữ nguyên.');
        } catch (Throwable $error) {
            return $this->failure($error);
        } finally {
            $this->discard($request);
            $lock->release();
        }
    }

    public function cancel(Request $request)
    {
        $this->discard($request);

        return redirect()->route('admin.imports.index');
    }

    private function discard(Request $request): void
    {
        $pending = $request->session()->pull('learning_import');
        if ($pending) {
            Storage::disk('local')->delete($pending['path']);
        }
    }

    private function failure(Throwable $error)
    {
        if ($error instanceof RuntimeException && ! $error instanceof QueryException) {
            $message = $error->getMessage();
        } else {
            report($error);
            $message = 'Không thể xử lý dữ liệu: schema, giá trị hoặc khóa dữ liệu không hợp lệ. Nếu lỗi xảy ra khi import, toàn bộ thay đổi đã rollback. Xem log ứng dụng để kiểm tra.';
        }

        return redirect()->route('admin.imports.index')->withErrors(['sql_file' => $message]);
    }
}
