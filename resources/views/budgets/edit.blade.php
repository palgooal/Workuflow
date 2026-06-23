@extends('layouts.app')
@section('title', 'تعديل الميزانية')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('budget.index') }}" class="text-muted hover:text-ink transition-colors">الميزانيات</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <x-page-header title="تعديل الميزانية" subtitle="عدّل المبلغ أو الفترة أو التصنيف" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('budget.update', $budget) }}">
            @csrf @method('PUT')
            @include('budgets._form')
        </form>
    </div>

</div>
@endsection
