@extends('admin.layout')

@section('title', 'Sửa câu hỏi')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa câu hỏi #{{ $question->id }}</h1>
    <a href="{{ route('admin.lessons.preview', $question->lesson_id) }}" class="vh-btn vh-btn-ghost">👁 Xem trước bài học</a>
</div>

@include('admin.questions.form', [
    'question' => $question,
    'lessons' => $lessons,
    'initial' => $initial,
    'presetLesson' => null,
    'action' => route('admin.questions.update', $question),
    'method' => 'PUT',
])
@endsection
