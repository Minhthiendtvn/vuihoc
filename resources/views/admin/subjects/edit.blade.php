@extends('admin.layout')

@section('title', 'Sửa môn học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa môn học: {{ $subject->name }}</h1>
</div>

@include('admin.subjects.form', [
    'subject' => $subject,
    'action' => route('admin.subjects.update', $subject),
    'method' => 'PUT',
])
@endsection
