@extends('admin.layout')

@section('title', 'Sửa chủ đề')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa chủ đề: {{ $topic->name }}</h1>
</div>

@include('admin.topics.form', [
    'topic' => $topic,
    'subjects' => $subjects,
    'action' => route('admin.topics.update', $topic),
    'method' => 'PUT',
])
@endsection
