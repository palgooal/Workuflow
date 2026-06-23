@extends('layouts.app')

@section('title', 'تعديل عميل')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('clients.index') }}" class="text-muted hover:text-ink transition-colors">العملاء</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل: {{ $client->name }}</span>
@endsection

@section('content')
<div class="max-w-xl space-y-5">
    <x-page-header title="تعديل بيانات العميل" :subtitle="$client->name" />

    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PATCH')
        @include('clients._form')
    </form>
</div>
@endsection
