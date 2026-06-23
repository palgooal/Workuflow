@extends('layouts.app')

@section('title', 'معاملة جديدة')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('transactions.index') }}" class="text-muted hover:text-ink transition-colors">المعاملات</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">جديدة</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <x-page-header title="إضافة معاملة جديدة" subtitle="سجّل دخلاً أو مصروفاً جديداً" />

    <form method="POST" action="{{ route('transactions.store') }}">
        @csrf
        @include('transactions._form')
    </form>
</div>
@endsection
