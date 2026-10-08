@extends('admin.layout')

@section('title', 'Thêm câu hỏi')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">❓ Thêm câu hỏi</h1>
</div>

@include('admin.questions.form', [
    'question' => null,
    'lessons' => $lessons,
    'initial' => $initial,
    'presetLesson' => $presetLesson,
    'action' => route('admin.questions.store'),
    'method' => 'POST',
])
@endsection
