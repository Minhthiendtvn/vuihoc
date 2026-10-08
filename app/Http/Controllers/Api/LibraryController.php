<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;

/**
 * API v1 — thư viện môn học / chủ đề (chỉ nội dung đã xuất bản).
 * GET /api/v1/subjects
 * GET /api/v1/subjects/{slug}/topics
 */
class LibraryController extends Controller
{
    public function subjects()
    {
        $subjects = Subject::published()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'slug'        => $s->slug,
                'icon'        => $s->icon,
                'color'       => $s->color,
                'description' => $s->description,
                'topics_count'=> $s->topics()->published()->count(),
            ]);

        return response()->json(['data' => $subjects]);
    }

    public function topics(string $slug)
    {
        $subject = Subject::published()->where('slug', $slug)->firstOrFail();

        $topics = $subject->topics()->published()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Topic $t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'slug'        => $t->slug,
                'icon'        => $t->icon,
                'description' => $t->description,
                'grade_min'   => $t->grade_min,
                'grade_max'   => $t->grade_max,
                'skills_count'=> $t->skills()->count(),
                'lessons_count' => $t->skills()->withCount(['lessons' => fn ($q) => $q->published()])->get()->sum('lessons_count'),
            ]);

        return response()->json([
            'subject' => ['id' => $subject->id, 'name' => $subject->name, 'slug' => $subject->slug],
            'data'    => $topics,
        ]);
    }
}
