@extends('layouts.app')

@section('title', 'تعديل: ' . $project->name)

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('projects.index') }}" class="text-muted hover:text-ink transition-colors">المشاريع</a>
    <span class="text-muted/60">/</span>
    <a href="{{ route('projects.show', $project) }}" class="text-muted hover:text-ink transition-colors">{{ $project->name }}</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">
    <x-page-header title="تعديل المشروع" subtitle="عدّل تفاصيل المشروع حسب الحاجة" />

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf
        @method('PUT')
        @include('projects._form')
    </form>
</div>
@endsection
