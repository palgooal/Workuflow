@extends('layouts.app')

@section('title', 'إضافة عضو فريق')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('team.index') }}" class="text-muted hover:text-ink transition-colors">الفريق</a>
    <span class="text-muted/60">/</span>
    <span class="text-slate-700">إضافة عضو</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-ink tracking-tight">إضافة عضو جديد</h1>
        <p class="mt-1 text-sm text-muted">أدخل بيانات العضو لتعيينه على المشاريع</p>
    </div>

    <form method="POST" action="{{ route('team.store') }}">
        @csrf
        @include('team._form')
    </form>
</div>
@endsection
