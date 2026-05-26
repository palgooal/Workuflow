@extends('portal.layouts.portal')
@php $pageTitle = 'ملفي الشخصي'; @endphp

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-800">ملفي الشخصي</h1>
        <a href="{{ route('portal.dashboard') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الرئيسية
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Profile Header --}}
        <div class="bg-gradient-to-l from-gray-100 to-gray-50 px-6 py-6 flex items-center gap-4 border-b border-gray-100">
            <div class="w-14 h-14 rounded-full bg-indigo-600 text-white text-xl font-bold flex items-center justify-center shrink-0">
                {{ mb_substr($client->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ $client->name }}</h2>
                @if($client->company)
                    <p class="text-sm text-gray-500">{{ $client->company }}</p>
                @endif
                <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full mt-1 {{ $client->status->badgeClass() }}">
                    {{ $client->status->label() }}
                </span>
            </div>
        </div>

        {{-- Fields --}}
        <div class="divide-y divide-gray-50">
            @php
                $fields = array_filter([
                    'البريد الإلكتروني' => $client->email,
                    'رقم الهاتف'        => $client->phone,
                    'الشركة'            => $client->company,
                    'المسمى الوظيفي'    => $client->position ?? null,
                    'الموقع الإلكتروني' => $client->website ?? null,
                    'العنوان'           => $client->address ?? null,
                    'المدينة'           => $client->city ?? null,
                    'الدولة'            => $client->country ?? null,
                ]);
            @endphp

            @foreach($fields as $label => $value)
                <div class="px-6 py-3.5 flex items-center gap-4">
                    <div class="text-xs text-gray-400 w-36 shrink-0">{{ $label }}</div>
                    <div class="text-sm text-gray-800 flex-1">
                        @if(str_starts_with((string)$value, 'http'))
                            <a href="{{ $value }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">
                                {{ $value }}
                            </a>
                        @else
                            {{ $value }}
                        @endif
                    </div>
                </div>
            @endforeach

            @if(empty($fields))
                <div class="px-6 py-8 text-center text-sm text-gray-400">
                    لا توجد بيانات إضافية مسجلة
                </div>
            @endif
        </div>
    </div>

    {{-- Token Info --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex items-start gap-3 text-sm text-gray-600">
        <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <div>
            <p>جلستك الحالية صالحة حتى <strong>{{ $portalToken->expires_at->format('Y/m/d الساعة H:i') }}</strong></p>
            @if($portalToken->last_used_at)
                <p class="text-xs text-gray-400 mt-0.5">آخر دخول: {{ $portalToken->last_used_at->diffForHumans() }}</p>
            @endif
        </div>
    </div>

</div>
@endsection
