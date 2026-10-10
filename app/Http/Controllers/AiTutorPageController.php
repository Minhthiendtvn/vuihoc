<?php

namespace App\Http\Controllers;

use App\Models\Subject;

class AiTutorPageController extends Controller
{
    public function index()
    {
        $subjects = Subject::published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('ai.tutor', compact('subjects'));
    }
}
