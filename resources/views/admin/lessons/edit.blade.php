@extends('admin.layout')

@section('title', 'Sửa bài học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa bài học: {{ $lesson->title }}</h1>
    <a href="{{ route('admin.lessons.preview', $lesson) }}" class="vh-btn vh-btn-ghost">👁 Xem trước</a>
</div>

@include('admin.lessons.form', [
    'lesson' => $lesson,
    'skills' => $skills,
    'action' => route('admin.lessons.update', $lesson),
    'method' => 'PUT',
])
@endsection
