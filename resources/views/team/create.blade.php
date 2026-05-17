@extends('layouts.app')

@section('title', 'إضافة عضو فريق')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('team.index') }}" class="text-gray-500 hover:text-gray-700">الفريق</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">إضافة عضو</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">إضافة عضو جديد</h1>
        <p class="mt-1 text-sm text-gray-500">أدخل بيانات العضو لتعيينه على المشاريع</p>
    </div>

    <form method="POST" action="{{ route('team.store') }}">
        @csrf
        @include('team._form')
    </form>
</div>
@endsection
