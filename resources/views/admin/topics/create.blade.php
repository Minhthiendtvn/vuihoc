@extends('admin.layout')

@section('title', 'Thêm chủ đề')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🗂️ Thêm chủ đề</h1>
</div>

@include('admin.topics.form', [
    'topic' => null,
    'subjects' => $subjects,
    'action' => route('admin.topics.store'),
    'method' => 'POST',
])
@endsection
