@extends('layouts.app')

@section('title', 'تعديل معاملة')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('transactions.index') }}" class="text-gray-500 hover:text-gray-700">المعاملات</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">تعديل المعاملة</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $transaction->description }}</p>
    </div>

    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
        @csrf
        @method('PUT')
        @php $preProject = null; @endphp
        @include('transactions._form')
    </form>
</div>
@endsection
