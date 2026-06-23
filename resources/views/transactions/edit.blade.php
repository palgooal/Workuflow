@extends('layouts.app')

@section('title', 'تعديل معاملة')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('transactions.index') }}" class="text-muted hover:text-ink transition-colors">المعاملات</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">
    <x-page-header title="تعديل المعاملة" :subtitle="$transaction->description" />

    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
        @csrf
        @method('PUT')
        @php $preProject = null; @endphp
        @include('transactions._form')
    </form>
</div>
@endsection
