@extends('layouts.app')
@section('title', 'ميزانية جديدة')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('budget.index') }}" class="text-muted hover:text-ink transition-colors">الميزانيات</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">جديدة</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <x-page-header title="إنشاء ميزانية جديدة" subtitle="حدد المبلغ والفترة لتتبع إنفاقك" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('budget.store') }}">
            @csrf
            @include('budgets._form')
        </form>
    </div>

</div>
@endsection
