@extends('admin.layout')

@section('title', 'Thêm huy hiệu')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🏅 Thêm huy hiệu</h1>
</div>

@include('admin.badges.form', [
    'badge' => null,
    'criteriaLabels' => $criteriaLabels,
    'action' => route('admin.badges.store'),
    'method' => 'POST',
])
@endsection
