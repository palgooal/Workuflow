@extends('layouts.app')

@section('title', 'مشروع جديد')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('projects.index') }}" class="text-muted hover:text-ink transition-colors">المشاريع</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">مشروع جديد</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <x-page-header title="إنشاء مشروع جديد" subtitle="حدد تفاصيل المشروع لتبدأ بتتبع أرباحه وتكاليفه" />

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        @include('projects._form')
    </form>
</div>
@endsection
