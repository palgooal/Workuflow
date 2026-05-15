@extends('layouts.app')

@section('title', 'تعديل عميل')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('clients.index') }}" class="text-gray-500 hover:text-gray-700">العملاء</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">تعديل: {{ $client->name }}</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">تعديل بيانات العميل</h1>
    </div>

    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PATCH')
        @include('clients._form')
    </form>
</div>
@endsection
