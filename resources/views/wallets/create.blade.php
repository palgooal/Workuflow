@extends('layouts.app')
@section('title', 'صندوق جديد')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('wallets.index') }}" class="text-muted hover:text-ink transition-colors">الصناديق</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">جديد</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <x-page-header title="إنشاء صندوق جديد" subtitle="أضف صندوقاً أو حساباً لتتبع رصيدك" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('wallets.store') }}">
            @csrf
            @include('wallets._form')
        </form>
    </div>

</div>
@endsection
