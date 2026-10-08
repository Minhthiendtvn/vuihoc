<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $lessons = Lesson::with('skill.topic')->orderBy('title')->get();

        $questions = Question::with(['lesson.skill.topic'])
            ->when($request->filled('lesson_id'), fn ($q) => $q->where('lesson_id', $request->input('lesson_id')))
            ->when($request->filled('grade'), fn ($q) => $q->where('grade', (int) $request->input('grade')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.questions.index', compact('questions', 'lessons'));
    }

    public function create(Request $request)
    {
        $lessons = Lesson::with('skill.topic')->orderBy('title')->get();
        $initial = $this->initialData(null, $request);

        return view('admin.questions.create', [
            'lessons' => $lessons,
            'initial' => $initial,
            'presetLesson' => $request->input('lesson_id'),
            'presetType' => $request->input('game_type', 'quiz'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($request, $validated) {
            $question = Question::create([
                'lesson_id'   => $validated['lesson_id'],
                'game_type'   => $validated['game_type'],
                'prompt'      => $validated['prompt'],
                'explanation' => $validated['explanation'] ?? null,
                'difficulty'  => $validated['difficulty'],
                'points'      => $validated['points'],
                'sort_order'  => $validated['sort_order'] ?? 0,
                'grade'       => $this->resolveGrade($validated),
                'is_demo'     => false,
            ]);

            $this->syncChildren($question, $request);
        });

        return redirect()->route('admin.questions.index')
            ->with('success', 'Đã thêm câu hỏi mới.');
    }

    public function edit(Request $request, Question $question)
    {
        $lessons = Lesson::with('skill.topic')->orderBy('title')->get();
        $initial = $this->initialData($question, $request);

        return view('admin.questions.edit', [
            'question' => $question,
            'lessons' => $lessons,
            'initial' => $initial,
            'presetLesson' => null,
            'presetType' => null,
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($request, $question, $validated) {
            $question->update([
                'lesson_id'   => $validated['lesson_id'],
                'game_type'   => $validated['game_type'],
                'prompt'      => $validated['prompt'],
                'explanation' => $validated['explanation'] ?? null,
                'difficulty'  => $validated['difficulty'],
                'points'      => $validated['points'],
                'sort_order'  => $validated['sort_order'] ?? 0,
                'grade'       => $this->resolveGrade($validated),
            ]);

            $this->syncChildren($question, $request);
        });

        return redirect()->route('admin.questions.index')
            ->with('success', 'Đã cập nhật câu hỏi.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('admin.questions.index')
            ->with('success', 'Đã xóa câu hỏi.');
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    private function validateQuestion(Request $request): array
    {
        $validator = Validator::make(
            $request->all(),
            $this->baseRules($request->input('game_type')),
            $this->messages()
        );

        $validator->after(fn (ValidatorInstance $v) => $this->afterValidate($v, $request));

        return $validator->validate();
    }

    private function baseRules(?string $type): array
    {
        $rules = [
            'lesson_id'   => 'required|exists:lessons,id',
            'game_type'   => 'required|in:quiz,matching,sort,fill',
            'prompt'      => 'required|string',
            'explanation' => 'nullable|string',
            'difficulty'  => 'required|in:de,trung_binh,kho',
            'grade'       => 'nullable|integer|min:6|max:12',
            'points'      => 'required|integer|min:1|max:100',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
        ];

        switch ($type) {
            case 'quiz':
                $rules['options'] = 'required|array|min:2';
                $rules['options.*.text'] = 'required|string|max:500';
                $rules['correct_index'] = 'required|integer|min:0';
                break;
            case 'matching':
                $rules['pairs'] = 'required|array|min:2';
                $rules['pairs.*.left_text'] = 'required|string|max:255';
                $rules['pairs.*.right_text'] = 'required|string|max:255';
                break;
            case 'sort':
                $rules['sort_items'] = 'required|array|min:2';
                $rules['sort_items.*.item_text'] = 'required|string|max:255';
                $rules['sort_items.*.category'] = 'required|string|max:100';
                break;
            case 'fill':
                $rules['fill_answers'] = 'required|array|min:1';
                $rules['fill_answers.*.blank_index'] = 'required|integer|min:0';
                $rules['fill_answers.*.answer_text'] = 'required|string';
                break;
        }

        return $rules;
    }

    private function afterValidate(ValidatorInstance $validator, Request $request): void
    {
        $type = $request->input('game_type');

        if ($type === 'quiz') {
            $raw = $request->input('options', []);
            $filled = array_values(array_filter($raw, fn ($o) => trim($o['text'] ?? '') !== ''));
            if (count($filled) < 2) {
                $validator->errors()->add('options', 'Cần ít nhất 2 đáp án có nội dung.');
            }
            $ci = (int) $request->input('correct_index', -1);
            if ($ci < 0 || $ci >= count($raw)) {
                $validator->errors()->add('correct_index', 'Vui lòng chọn đúng 1 đáp án đúng.');
            }
        }

        if ($type === 'matching') {
            $filled = array_values(array_filter(
                $request->input('pairs', []),
                fn ($p) => trim($p['left_text'] ?? '') !== '' && trim($p['right_text'] ?? '') !== ''
            ));
            if (count($filled) < 2) {
                $validator->errors()->add('pairs', 'Cần ít nhất 2 cặp ghép có nội dung.');
            }
        }

        if ($type === 'sort') {
            $filled = array_values(array_filter(
                $request->input('sort_items', []),
                fn ($i) => trim($i['item_text'] ?? '') !== '' && trim($i['category'] ?? '') !== ''
            ));
            if (count($filled) < 2) {
                $validator->errors()->add('sort_items', 'Cần ít nhất 2 mục có nội dung.');
                return;
            }
            $categories = array_unique(array_map(
                fn ($i) => mb_strtolower(trim($i['category'])),
                $filled
            ));
            if (count($categories) < 2) {
                $validator->errors()->add('sort_items', 'Cần ít nhất 2 nhóm (category) khác nhau.');
            }
        }

        if ($type === 'fill') {
            $blanks = substr_count((string) $request->input('prompt', ''), '___');
            if ($blanks < 1) {
                $validator->errors()->add('prompt', 'Đề bài phải chứa ít nhất một chỗ trống “___”.');
                return;
            }
            $hasAnswer = [];
            foreach ($request->input('fill_answers', []) as $row) {
                $idx = (int) ($row['blank_index'] ?? -1);
                $answers = array_filter(array_map(
                    'trim',
                    preg_split('/\r\n|\r|\n/', (string) ($row['answer_text'] ?? ''))
                ));
                if ($idx >= 0 && $idx < $blanks && count($answers) > 0) {
                    $hasAnswer[$idx] = true;
                }
            }
            for ($i = 0; $i < $blanks; $i++) {
                if (empty($hasAnswer[$i])) {
                    $validator->errors()->add('fill_answers', 'Chỗ trống số '.($i + 1).' chưa có đáp án.');
                }
            }
        }
    }

    private function messages(): array
    {
        return [
            'lesson_id.required' => 'Vui lòng chọn bài học.',
            'lesson_id.exists'   => 'Bài học đã chọn không tồn tại.',
            'game_type.required' => 'Vui lòng chọn loại game.',
            'game_type.in'       => 'Loại game không hợp lệ.',
            'prompt.required'    => 'Vui lòng nhập đề bài (prompt).',
            'difficulty.required' => 'Vui lòng chọn độ khó.',
            'difficulty.in'      => 'Độ khó không hợp lệ.',
            'grade.integer'      => 'Khối lớp phải là số nguyên.',
            'grade.min'          => 'Khối lớp phải từ 6 đến 12.',
            'grade.max'          => 'Khối lớp phải từ 6 đến 12.',
            'points.required'    => 'Vui lòng nhập điểm.',
            'points.integer'     => 'Điểm phải là số nguyên.',
            'points.min'         => 'Điểm tối thiểu là 1.',
            'points.max'         => 'Điểm tối đa là 100.',
            'options.required'   => 'Vui lòng nhập danh sách đáp án.',
            'options.min'        => 'Cần ít nhất 2 đáp án.',
            'options.*.text.required' => 'Mỗi đáp án cần có nội dung.',
            'options.*.text.max' => 'Mỗi đáp án không quá 500 ký tự.',
            'correct_index.required' => 'Vui lòng chọn đáp án đúng.',
            'pairs.required'     => 'Vui lòng nhập danh sách cặp ghép.',
            'pairs.min'          => 'Cần ít nhất 2 cặp ghép.',
            'pairs.*.left_text.required'  => 'Mỗi cặp cần có nội dung cột trái.',
            'pairs.*.right_text.required' => 'Mỗi cặp cần có nội dung cột phải.',
            'sort_items.required' => 'Vui lòng nhập danh sách mục.',
            'sort_items.min'     => 'Cần ít nhất 2 mục.',
            'sort_items.*.item_text.required' => 'Mỗi mục cần có nội dung.',
            'sort_items.*.category.required' => 'Mỗi mục cần có nhóm (category).',
            'fill_answers.required' => 'Vui lòng nhập đáp án cho các chỗ trống.',
            'fill_answers.*.blank_index.required' => 'Mỗi dòng đáp án cần có số thứ tự chỗ trống.',
            'fill_answers.*.answer_text.required' => 'Mỗi dòng đáp án cần có nội dung.',
        ];
    }

    // ------------------------------------------------------------------
    // Lưu dữ liệu con theo loại game
    // ------------------------------------------------------------------

    /**
     * Nếu không chọn khối lớp, mặc định theo grade của bài học chứa câu hỏi.
     */
    private function resolveGrade(array $validated): ?int
    {
        if (! empty($validated['grade'])) {
            return (int) $validated['grade'];
        }

        return Lesson::find($validated['lesson_id'])?->grade;
    }

    private function syncChildren(Question $question, Request $request): void
    {
        // Xóa dữ liệu con cũ (kể cả khi đổi loại game) rồi tạo lại.
        $question->options()->delete();
        $question->pairs()->delete();
        $question->sortItems()->delete();
        $question->fillAnswers()->delete();

        switch ($request->input('game_type')) {
            case 'quiz':
                $correct = (int) $request->input('correct_index', 0);
                $order = 0;
                foreach ($request->input('options', []) as $i => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if ($text === '') {
                        continue;
                    }
                    $question->options()->create([
                        'option_text' => $text,
                        'is_correct'  => ((int) $i) === $correct,
                        'sort_order'  => $order++,
                    ]);
                }
                break;

            case 'matching':
                $order = 0;
                foreach ($request->input('pairs', []) as $pair) {
                    $left = trim($pair['left_text'] ?? '');
                    $right = trim($pair['right_text'] ?? '');
                    if ($left === '' || $right === '') {
                        continue;
                    }
                    $question->pairs()->create([
                        'left_text'  => $left,
                        'right_text' => $right,
                        'sort_order' => $order++,
                    ]);
                }
                break;

            case 'sort':
                $order = 0;
                foreach ($request->input('sort_items', []) as $item) {
                    $text = trim($item['item_text'] ?? '');
                    $category = trim($item['category'] ?? '');
                    if ($text === '' || $category === '') {
                        continue;
                    }
                    $question->sortItems()->create([
                        'item_text'  => $text,
                        'category'   => $category,
                        'sort_order' => $order++,
                    ]);
                }
                break;

            case 'fill':
                $order = 0;
                foreach ($request->input('fill_answers', []) as $row) {
                    $blankIndex = (int) ($row['blank_index'] ?? 0);
                    $answers = array_filter(array_map(
                        'trim',
                        preg_split('/\r\n|\r|\n/', (string) ($row['answer_text'] ?? ''))
                    ));
                    foreach ($answers as $answer) {
                        $question->fillAnswers()->create([
                            'blank_index' => $blankIndex,
                            'answer_text' => $answer,
                            'sort_order'  => $order++,
                        ]);
                    }
                }
                break;
        }
    }

    // ------------------------------------------------------------------
    // Dữ liệu khởi tạo cho form Alpine (ưu tiên old() khi validate lỗi)
    // ------------------------------------------------------------------

    private function initialData(?Question $question, Request $request): array
    {
        if ($question === null) {
            return [
                'gameType'     => old('game_type', $request->input('game_type', 'quiz')),
                'correctIndex' => (int) old('correct_index', 0),
                'options'      => old('options', []),
                'pairs'        => old('pairs', []),
                'sortItems'    => old('sort_items', []),
                'fillRows'     => old('fill_answers', []),
            ];
        }

        $question->load(['options', 'pairs', 'sortItems', 'fillAnswers']);

        $options = $question->options->sortBy('sort_order')->values();
        $correctIndex = $options->search(fn ($o) => $o->is_correct);

        return [
            'gameType'     => old('game_type', $question->game_type),
            'correctIndex' => (int) old('correct_index', $correctIndex === false ? 0 : $correctIndex),
            'options'      => old('options', $options->map(fn ($o) => ['text' => $o->option_text])->values()->all()),
            'pairs'        => old('pairs', $question->pairs->sortBy('sort_order')->values()->map(fn ($p) => [
                'left_text' => $p->left_text, 'right_text' => $p->right_text,
            ])->all()),
            'sortItems'    => old('sort_items', $question->sortItems->sortBy('sort_order')->values()->map(fn ($i) => [
                'item_text' => $i->item_text, 'category' => $i->category,
            ])->all()),
            'fillRows'     => old('fill_answers', $question->fillAnswers
                ->sortBy('sort_order')->values()
                ->groupBy('blank_index')
                ->map(fn ($g, $idx) => [
                    'blank_index' => (int) $idx,
                    'answer_text' => $g->pluck('answer_text')->implode("\n"),
                ])->values()->all()),
        ];
    }
}
