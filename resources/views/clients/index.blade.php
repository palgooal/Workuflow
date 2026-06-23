@extends('layouts.app')

@section('title', 'العملاء')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <span class="text-ink">العملاء</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="العملاء" :subtitle="$clients->count().' عميل مسجّل'">
        <x-slot name="actions">
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                      text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة عميل
            </a>
        </x-slot>
    </x-page-header>

    @if(session('success'))
        <div class="bg-success-soft border border-success/30 text-success-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($clients->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا يوجد عملاء بعد"
                description="أضف أول عميل لتبدأ بتتبع مشاريعه"
                :action="route('clients.create')"
                actionLabel="إضافة عميل" />
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($clients as $client)
            <div class="dash-card dash-card-hover p-5 group">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center text-brand font-bold shrink-0">
                            {{ mb_substr($client->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-ink truncate">{{ $client->name }}</p>
                            @if($client->company)
                                <p class="text-xs text-muted mt-0.5 truncate">{{ $client->company }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity shrink-0">
                        <a href="{{ route('clients.edit', $client) }}"
                           class="row-action hover:text-brand hover:bg-brand-50" aria-label="تعديل">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                            @csrf @method('DELETE')
                            <button class="row-action hover:text-red-600 hover:bg-red-50" aria-label="حذف">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 space-y-1.5">
                    @if($client->phone)
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <svg class="w-3.5 h-3.5 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="nums">{{ $client->phone }}</span>
                    </div>
                    @endif
                    @if($client->email)
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <svg class="w-3.5 h-3.5 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">{{ $client->email }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-subtle flex items-center justify-between">
                    <span class="text-xs text-muted nums">{{ $client->projects_count }} مشروع</span>
                    <div class="flex items-center gap-2">
                        @if(! $client->is_active)
                            <span class="text-xs text-red-600 bg-error-soft px-2 py-0.5 rounded-full">موقوف</span>
                        @endif
                        @if($client->phone)
                            @php $waPhone = preg_replace('/[^0-9]/', '', $client->phone); @endphp
                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" title="مراسلة عبر واتساب"
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-success-soft hover:bg-success-soft/70
                                      text-success-700 text-xs font-medium rounded-lg transition-colors">
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
