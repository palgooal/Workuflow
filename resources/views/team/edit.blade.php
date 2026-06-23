@extends('layouts.app')

@section('title', 'تعديل عضو الفريق')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('team.index') }}" class="text-muted hover:text-ink transition-colors">الفريق</a>
    <span class="text-muted/60">/</span>
    <span class="text-slate-700">تعديل: {{ $teamMember->name }}</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-ink tracking-tight">تعديل بيانات العضو</h1>
        <p class="mt-1 text-sm text-muted">{{ $teamMember->name }}</p>
    </div>

    <form method="POST" action="{{ route('team.update', $teamMember) }}">
        @csrf
        @method('PUT')
        @include('team._form')
    </form>
</div>
@endsection
