@extends('layouts.app')
@section('title', 'تعديل: ' . $wallet->name)

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('wallets.index') }}" class="text-muted hover:text-ink transition-colors">الصناديق</a>
    <span class="text-muted/60">/</span>
    <a href="{{ route('wallets.show', $wallet) }}" class="text-muted hover:text-ink transition-colors">{{ $wallet->name }}</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <x-page-header :title="'تعديل: '.$wallet->name" subtitle="عدّل بيانات الصندوق وإعداداته" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('wallets.update', $wallet) }}">
            @csrf @method('PUT')
            @include('wallets._form')
        </form>
    </div>

</div>
@endsection
