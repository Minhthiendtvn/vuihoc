@extends('admin.layout')

@section('title', 'Thêm bài học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">📖 Thêm bài học</h1>
</div>

@include('admin.lessons.form', [
    'lesson' => null,
    'skills' => $skills,
    'action' => route('admin.lessons.store'),
    'method' => 'POST',
])
@endsection
