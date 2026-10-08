@extends('admin.layout')

@section('title', 'Sửa huy hiệu')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Sửa huy hiệu: {{ $badge->name }}</h1>
</div>

@include('admin.badges.form', [
    'badge' => $badge,
    'criteriaLabels' => $criteriaLabels,
    'action' => route('admin.badges.update', $badge),
    'method' => 'PUT',
])
@endsection
