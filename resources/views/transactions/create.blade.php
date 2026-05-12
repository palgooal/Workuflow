@extends('layouts.app')

@section('title', 'معاملة جديدة')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('transactions.index') }}" class="text-gray-500 hover:text-gray-700">المعاملات</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">جديدة</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">إضافة معاملة جديدة</h1>
        <p class="mt-1 text-sm text-gray-500">سجّل دخلاً أو مصروفاً جديداً</p>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}">
        @csrf
        @include('transactions._form')
    </form>
</div>
@endsection
