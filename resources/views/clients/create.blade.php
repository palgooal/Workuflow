@extends('layouts.app')

@section('title', 'إضافة عميل')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('clients.index') }}" class="text-gray-500 hover:text-gray-700">العملاء</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">إضافة عميل</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">إضافة عميل جديد</h1>
        <p class="mt-1 text-sm text-gray-500">أدخل بيانات العميل لربطه بمشاريعك</p>
    </div>

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('clients._form')
    </form>
</div>
@endsection
