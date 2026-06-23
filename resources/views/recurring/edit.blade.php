@extends('layouts.app')
@section('title', 'تعديل الالتزام المتكرر')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('recurring.index') }}" class="text-muted hover:text-ink transition-colors">الالتزامات المتكررة</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <x-page-header title="تعديل الالتزام المتكرر" subtitle="عدّل تفاصيل الدفعة الثابتة المتكررة" />

    <div class="dash-card p-6 sm:p-7">
        <form method="POST" action="{{ route('recurring.update', $recurring) }}">
            @csrf @method('PUT')
            @include('recurring._form')
        </form>
    </div>

</div>
@endsection
