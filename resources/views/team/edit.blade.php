@extends('layouts.app')

@section('title', 'تعديل عضو الفريق')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('team.index') }}" class="text-gray-500 hover:text-gray-700">الفريق</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">تعديل: {{ $teamMember->name }}</span>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">تعديل بيانات العضو</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $teamMember->name }}</p>
    </div>

    <form method="POST" action="{{ route('team.update', $teamMember) }}">
        @csrf
        @method('PUT')
        @include('team._form')
    </form>
</div>
@endsection
