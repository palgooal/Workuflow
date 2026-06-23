@extends('layouts.app')
@section('title', 'التزام متكرر جديد')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('recurring.index') }}" class="text-muted hover:text-ink transition-colors">الالتزامات المتكررة</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">جديد</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <x-page-header title="التزام متكرر جديد" subtitle="سجّل دفعة ثابتة تتكرر تلقائياً بشكل دوري" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('recurring.store') }}">
            @csrf
            @include('recurring._form')
        </form>
    </div>

</div>
@endsection
