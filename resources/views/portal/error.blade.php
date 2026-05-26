@extends('portal.layouts.portal')
@php $pageTitle = 'خطأ'; @endphp

@section('content')
<div class="min-h-[55vh] flex items-center justify-center">
    <div class="w-full max-w-md text-center">

        {{-- Icon --}}
        <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-9 h-9 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>

        {{-- Title --}}
        <h1 class="text-xl font-bold text-gray-800 mb-3">
            {{ $title ?? 'غير مسموح' }}
        </h1>

        {{-- Message --}}
        <p class="text-gray-500 text-sm mb-8 leading-relaxed">
            {{ $message ?? 'ليس لديك صلاحية للوصول إلى هذه الصفحة.' }}
        </p>

        {{-- Back Button --}}
        <a href="{{ route('portal.dashboard') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-semibold rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 12l6-6M3 12l6 6"/>
            </svg>
            العودة للرئيسية
        </a>

    </div>
</div>
@endsection
