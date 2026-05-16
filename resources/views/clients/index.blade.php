@extends('layouts.app')

@section('title', 'العملاء')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">العملاء</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">العملاء</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $clients->count() }} عميل مسجّل</p>
        </div>
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة عميل
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($clients->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">لا يوجد عملاء بعد</h3>
            <p class="mt-1 text-sm text-gray-400">أضف أول عميل لتبدأ بتتبع مشاريعه</p>
            <a href="{{ route('clients.create') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm
                      font-medium rounded-xl hover:bg-indigo-700 transition">
                + إضافة عميل
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($clients as $client)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-indigo-200 transition group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center
                                    text-indigo-600 font-bold text-sm">
                            {{ mb_substr($client->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $client->name }}</p>
                            @if($client->company)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $client->company }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('clients.edit', $client) }}"
                           class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 space-y-1.5">
                    @if($client->phone)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $client->phone }}
                    </div>
                    @endif
                    @if($client->email)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $client->email }}
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ $client->projects_count }} مشروع</span>
                    <div class="flex items-center gap-2">
                        @if(! $client->is_active)
                            <span class="text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded-full">موقوف</span>
                        @endif
                        @if($client->phone)
                            @php
                                $waPhone = preg_replace('/[^0-9]/', '', $client->phone);
                            @endphp
                            <a href="https://wa.me/{{ $waPhone }}"
                               target="_blank"
                               title="مراسلة عبر واتساب"
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 hover:bg-green-100
                                      text-green-700 text-xs font-medium rounded-lg transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                واتساب
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
