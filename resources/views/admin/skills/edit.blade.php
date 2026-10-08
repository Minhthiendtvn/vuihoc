@extends('admin.layout')

@section('title', 'Sửa kỹ năng')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa kỹ năng: {{ $skill->name }}</h1>
</div>

@include('admin.skills.form', [
    'skill' => $skill,
    'topics' => $topics,
    'action' => route('admin.skills.update', $skill),
    'method' => 'PUT',
])
@endsection
