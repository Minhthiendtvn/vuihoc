@extends('admin.layout')

@section('title', 'Thêm môn học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">📚 Thêm môn học</h1>
</div>

@include('admin.subjects.form', [
    'subject' => null,
    'action' => route('admin.subjects.store'),
    'method' => 'POST',
])
@endsection
