@extends('admin.layout')

@section('title', 'Thêm kỹ năng')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🎯 Thêm kỹ năng</h1>
</div>

@include('admin.skills.form', [
    'skill' => null,
    'topics' => $topics,
    'action' => route('admin.skills.store'),
    'method' => 'POST',
])
@endsection
