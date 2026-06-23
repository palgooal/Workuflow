@extends('layouts.app')

@section('title', 'إضافة عميل')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('clients.index') }}" class="text-muted hover:text-ink transition-colors">العملاء</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">إضافة عميل</span>
@endsection

@section('content')
<div class="max-w-xl space-y-5">
    <x-page-header title="إضافة عميل جديد" subtitle="أدخل بيانات العميل لربطه بمشاريعك" />

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('clients._form')
    </form>
</div>
@endsection
