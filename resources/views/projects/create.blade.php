@extends('layouts.app')

@section('title', 'مشروع جديد')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">المشاريع</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">مشروع جديد</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">إنشاء مشروع جديد</h1>
        <p class="mt-1 text-sm text-gray-500">حدد تفاصيل المشروع لتبدأ بتتبع أرباحه وتكاليفه</p>
    </div>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        @include('projects._form')
    </form>
</div>
@endsection
