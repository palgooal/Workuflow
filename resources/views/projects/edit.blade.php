@extends('layouts.app')

@section('title', 'تعديل: ' . $project->name)

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">المشاريع</a>
    <span class="text-gray-300">/</span>
    <a href="{{ route('projects.show', $project) }}" class="text-gray-500 hover:text-gray-700">{{ $project->name }}</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">تعديل المشروع</h1>
        <p class="mt-1 text-sm text-gray-500">عدّل تفاصيل المشروع حسب الحاجة</p>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf
        @method('PUT')
        @include('projects._form')
    </form>
</div>
@endsection
