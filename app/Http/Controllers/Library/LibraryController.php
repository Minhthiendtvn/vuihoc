<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Controller thư viện VuiHoc — agent Library (phase 2).
 * Khách chưa đăng nhập vẫn xem được; nút chơi/yêu thích chuyển về trang đăng nhập.
 */
class LibraryController extends Controller
{
    /** Trang chủ: hero, môn nổi bật, chủ đề mới, được yêu thích nhất. */
    public function index()
    {
        $subjects = Subject::published()
            ->withCount(['topics as published_topics_count' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grade = active_profile()?->grade;

        $latestTopics = Topic::published()
            ->when($grade, fn ($q) => $q->forGrade($grade))
            ->with('subject')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // Chủ đề/bài học được yêu thích nhiều nhất.
        $topFavs = Favorite::selectRaw('target_type, target_id, COUNT(*) as fav_count')
            ->groupBy('target_type', 'target_id')
            ->orderByDesc('fav_count')
            ->limit(8)
            ->get();

        $mostLoved = $this->hydrateFavorites($topFavs);

        // Gợi ý khi chưa có dữ liệu yêu thích: bài học mới nhất.
        $suggestions = $mostLoved->isEmpty()
            ? Lesson::published()
                ->with(['skill.topic.subject'])
                ->orderByDesc('id')
                ->limit(4)
                ->get()
            : collect();

        return view('library.index', compact('subjects', 'latestTopics', 'mostLoved', 'suggestions'));
    }

    /** Trang thư viện: tìm kiếm + lọc theo môn, khối lớp, độ khó. */
    public function library(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $subjectSlug = $request->input('mon', '');
        $gradeFilter = (int) $request->input('khoi', 0);
        $difficulty = $request->input('do_kho', '');

        $difficulties = config('vuihoc.difficulties', []);
        $subjects = Subject::published()->orderBy('sort_order')->orderBy('name')->get();

        $grade = $gradeFilter >= 6 && $gradeFilter <= 12 ? $gradeFilter : null;

        if (! $request->has('khoi')) {
            // Mặc định lọc theo khối lớp của hồ sơ đang dùng — đồng nhất với
            // trang môn học. Trước đây trang này hiện cả chủ đề ngoài khối,
            // bấm vào bị 404 trắng trang.
            $grade = active_profile()?->grade;
            $gradeFilter = $grade ?? 0;
        }

        // --- Chủ đề khớp tìm kiếm / bộ lọc ---
        $topics = Topic::published()
            ->with('subject')
            ->withCount(['skills as published_lessons_count' => fn ($q2) => $q2->whereHas('lessons', fn ($l) => $l->published())])
            ->when($subjectSlug, fn ($query) => $query->whereHas('subject', fn ($s) => $s->where('slug', $subjectSlug)))
            ->when($grade, fn ($query) => $query->forGrade($grade))
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(60)
            ->get();

        // --- Bài học khớp tìm kiếm / bộ lọc ---
        $lessons = Lesson::published()
            ->withQuestionTypeCounts()
            ->with(['skill.topic.subject'])
            ->when($subjectSlug, fn ($query) => $query->whereHas('skill.topic.subject', fn ($s) => $s->where('slug', $subjectSlug)))
            ->when($grade, fn ($query) => $query->whereHas('skill.topic', fn ($t) => $t->forGrade($grade)))
            ->when($grade, fn ($query) => $query->where(function ($w) use ($grade) {
                $w->where('grade', $grade)->orWhereNull('grade');
            }))
            ->when($difficulty !== '' && isset($difficulties[$difficulty]), fn ($query) => $query->where('difficulty', $difficulty))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhereHas('skill', fn ($s) => $s->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('skill.topic', fn ($t) => $t->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('skill.topic.subject', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(60)
            ->get();

        return view('library.library', [
            'q' => $q,
            'subjectSlug' => $subjectSlug,
            'gradeFilter' => $gradeFilter,
            'difficulty' => $difficulty,
            'difficulties' => $difficulties,
            'subjects' => $subjects,
            'topics' => $topics,
            'lessons' => $lessons,
        ]);
    }

    /** Trang môn học: thông tin môn + danh sách chủ đề (lọc theo khối lớp của hồ sơ). */
    public function subject(string $slug)
    {
        $subject = Subject::published()->where('slug', $slug)->firstOrFail();

        $grade = active_profile()?->grade;

        $topics = $subject->topics()
            ->published()
            ->when($grade, fn ($q) => $q->forGrade($grade))
            ->withCount(['skills as skills_count'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('library.subject', compact('subject', 'topics', 'grade'));
    }

    /** Trang chủ đề: breadcrumb + skills → lessons (chỉ published) + số câu hỏi theo kiểu chơi. */
    public function topic(string $slug)
    {
        $topic = Topic::published()->where('slug', $slug)->firstOrFail();
        $topic->load(['subject', 'skills' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')]);

        $grade = active_profile()?->grade;
        // Ngoài khoảng khối của chủ đề → đưa về trang môn học kèm thông báo
        // (điều kiện đồng nhất với scope forGrade: grade_min <= grade <= grade_max).
        if ($grade && ($grade < $topic->grade_min || $grade > $topic->grade_max)) {
            // Không đá 404 trắng trang: đưa về trang môn học kèm thông báo rõ ràng.
            return redirect()->route('library.subject', $topic->subject->slug)
                ->with('info', "Chủ đề “{$topic->name}” dành cho khối lớp {$topic->grade_min}–{$topic->grade_max}.");
        }

        $gameTypes = array_keys(config('vuihoc.game_types', []));

        foreach ($topic->skills as $skill) {
            $skill->setRelation(
                'lessons',
                Lesson::published()
                    ->where('skill_id', $skill->id)
                    ->withQuestionTypeCounts()
                    ->when($grade, fn ($q) => $q->where(function ($w) use ($grade) {
                        $w->where('grade', $grade)->orWhereNull('grade');
                    }))
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get()
            );
        }

        $favLessonIds = $this->favoriteIds('lesson');
        $favTopicIds = $this->favoriteIds('topic');

        return view('library.topic', compact('topic', 'grade', 'gameTypes', 'favLessonIds', 'favTopicIds'));
    }

    /** Trang chi tiết bài học. */
    public function lesson(string $slug)
    {
        $lesson = Lesson::published()
            ->where('slug', $slug)
            ->withQuestionTypeCounts()
            ->with(['skill.topic.subject'])
            ->firstOrFail();

        $gameTypes = config('vuihoc.game_types', []);
        $difficulties = config('vuihoc.difficulties', []);

        // Chặn bài ngoài khối lớp của hồ sơ (đồng nhất với scope forGrade của chủ đề).
        $grade = active_profile()?->grade;
        $lessonTopic = $lesson->skill->topic ?? null;
        if ($grade && $lessonTopic && ($grade < $lessonTopic->grade_min || $grade > $lessonTopic->grade_max)) {
            return redirect()->route('library.subject', $lessonTopic->subject->slug)
                ->with('info', "Bài học này dành cho khối lớp {$lessonTopic->grade_min}–{$lessonTopic->grade_max}.");
        }

        // Bài tiếp theo trong cùng skill (published, theo sort_order rồi id).
        $nextLesson = Lesson::published()
            ->where('skill_id', $lesson->skill_id)
            ->where(function ($q) use ($lesson) {
                $q->where('sort_order', '>', $lesson->sort_order)
                    ->orWhere(function ($q2) use ($lesson) {
                        $q2->where('sort_order', '=', $lesson->sort_order)
                            ->where('id', '>', $lesson->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $isFav = $this->isFavorite('lesson', $lesson->id);

        return view('library.lesson', compact('lesson', 'gameTypes', 'difficulties', 'nextLesson', 'isFav'));
    }

    /** Danh sách yêu thích của hồ sơ đang hoạt động. */
    public function favorites()
    {
        $profile = active_profile();
        if (! $profile) {
            return redirect()->route('auth.login')->with('info', 'Bạn cần đăng nhập để xem danh sách yêu thích.');
        }

        $favs = Favorite::where('profile_id', $profile->id)
            ->orderByDesc('id')
            ->get();

        $lessonIds = $favs->where('target_type', 'lesson')->pluck('target_id')->all();
        $topicIds = $favs->where('target_type', 'topic')->pluck('target_id')->all();

        $favLessons = Lesson::published()->whereIn('id', $lessonIds)
            ->with(['skill.topic.subject'])->get()->sortBy(fn ($l) => array_search($l->id, $lessonIds))->values();
        $favTopics = Topic::published()->whereIn('id', $topicIds)
            ->with('subject')->get()->sortBy(fn ($t) => array_search($t->id, $topicIds))->values();

        return view('library.favorites', compact('favLessons', 'favTopics'));
    }

    /** Bật/tắt yêu thích (cần hồ sơ đang hoạt động). */
    public function toggleFavorite(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('auth.login')->with('info', 'Bạn cần đăng nhập để lưu mục yêu thích.');
        }

        $profile = active_profile();
        if (! $profile) {
            return back()->with('error', 'Tài khoản của bạn chưa có hồ sơ học viên để lưu mục yêu thích.');
        }

        $data = $request->validate([
            'target_type' => 'required|in:lesson,topic',
            'target_id' => 'required|integer',
        ]);

        // Chỉ cho yêu thích nội dung đã xuất bản.
        $exists = $data['target_type'] === 'lesson'
            ? Lesson::published()->where('id', $data['target_id'])->exists()
            : Topic::published()->where('id', $data['target_id'])->exists();

        if (! $exists) {
            return back()->with('error', 'Nội dung này không tồn tại hoặc chưa được xuất bản.');
        }

        $fav = Favorite::where('profile_id', $profile->id)
            ->where('target_type', $data['target_type'])
            ->where('target_id', $data['target_id'])
            ->first();

        if ($fav) {
            $fav->delete();
            $message = 'Đã bỏ khỏi danh sách yêu thích.';
        } else {
            Favorite::create([
                'profile_id' => $profile->id,
                'target_type' => $data['target_type'],
                'target_id' => $data['target_id'],
            ]);
            $message = 'Đã thêm vào danh sách yêu thích. ❤️';
        }

        return back()->with('success', $message);
    }

    /**
     * URL bắt đầu chơi: ưu tiên route gameplay.start (agent Gameplay),
     * nếu chưa có thì dùng URL theo đúng CONTRACT.
     */
    public static function playUrl(Lesson $lesson, string $gameType): string
    {
        if (Route::has('gameplay.start')) {
            return route('gameplay.start', ['lesson' => $lesson->slug, 'game_type' => $gameType]);
        }

        return url("/bai-hoc/{$lesson->slug}/choi/{$gameType}");
    }

    /** Dựng collection các model (lesson/topic đã published) từ thống kê yêu thích. */
    protected function hydrateFavorites($topFavs)
    {
        $lessonIds = $topFavs->where('target_type', 'lesson')->pluck('target_id')->all();
        $topicIds = $topFavs->where('target_type', 'topic')->pluck('target_id')->all();

        $lessons = Lesson::published()->whereIn('id', $lessonIds)->with(['skill.topic.subject'])->get()
            ->keyBy('id');
        $topics = Topic::published()->whereIn('id', $topicIds)->with('subject')->get()
            ->keyBy('id');

        $items = collect();
        foreach ($topFavs as $row) {
            $model = $row->target_type === 'lesson'
                ? ($lessons[$row->target_id] ?? null)
                : ($topics[$row->target_id] ?? null);
            if ($model) {
                $model->fav_count = $row->fav_count;
                $items->push($model);
            }
        }

        return $items;
    }

    /** Danh sách id đã yêu thích theo loại (rỗng nếu chưa đăng nhập). */
    protected function favoriteIds(string $type): array
    {
        $profile = active_profile();
        if (! $profile) {
            return [];
        }

        return Favorite::where('profile_id', $profile->id)
            ->where('target_type', $type)
            ->pluck('target_id')
            ->all();
    }

    protected function isFavorite(string $type, int $id): bool
    {
        return in_array($id, $this->favoriteIds($type), true);
    }
}
