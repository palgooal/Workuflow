@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Header --}}
    <x-page-header title="الإشعارات" subtitle="تنبيهات الديون والاستحقاقات">
        @if(auth()->user()->unreadNotifications->count() > 0)
            <x-slot name="actions">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-brand hover:text-brand-700
                                   border border-brand/30 hover:bg-brand-50 rounded-btn transition-colors">
                        تحديد الكل كمقروء
                    </button>
                </form>
            </x-slot>
        @endif
    </x-page-header>

    {{-- Notifications List --}}
    <div class="dash-card overflow-hidden">

        @if($notifications->isEmpty())
            <div class="py-16">
                <x-empty-state
                    title="لا توجد إشعارات"
                    description="ستظهر هنا التنبيهات المتعلقة بالديون والاستحقاقات"
                />
            </div>
        @else
            <div class="divide-y divide-subtle/70">
                @foreach($notifications as $notification)
                    @php
                        $data    = $notification->data;
                        $isRead  = !is_null($notification->read_at);
                        $icon    = $data['icon'] ?? '🔔';
                        $type    = $data['type'] ?? 'general';
                    @endphp
                    <div class="flex items-start gap-4 px-5 py-4 transition
                                {{ $isRead ? 'bg-white' : 'bg-brand-50/30' }}
                                hover:bg-slate-50 group">

                        {{-- Icon --}}
                        <div class="shrink-0 mt-0.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl
                                {{ in_array($type, ['debt_overdue', 'invoice_overdue']) ? 'bg-red-100' : (in_array($type, ['debt_due_soon', 'invoice_due_soon']) ? 'bg-yellow-100' : 'bg-slate-100') }}">
                                {{ $icon }}
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-ink flex items-center gap-2">
                                        {{ $data['title'] ?? 'إشعار' }}
                                        @if(!$isRead)
                                            <span class="w-2 h-2 rounded-full bg-brand inline-block shrink-0"></span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-slate-600 mt-0.5">
                                        {{ $data['message'] ?? '' }}
                                    </p>
                                    @if(isset($data['amount']) && isset($data['currency']))
                                        <p class="text-xs text-muted mt-1">
                                            المبلغ المتبقي:
                                            <strong class="text-slate-700 nums">
                                                {{ number_format($data['amount'], 2) }} {{ $data['currency'] }}
                                            </strong>
                                        </p>
                                    @endif
                                </div>
                                <p class="text-xs text-muted shrink-0 mt-0.5">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-2.5 flex items-center gap-3">
                                @if(isset($data['link']))
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}"
                                          class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs text-brand hover:text-brand-700 font-medium">
                                            عرض التفاصيل ←
                                        </button>
                                    </form>
                                @endif
                                @if(!$isRead)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}"
                                          class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs text-muted hover:text-ink">
                                            تحديد كمقروء
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}"
                                      class="inline opacity-0 group-hover:opacity-100 transition">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-400 hover:text-red-600">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="px-5 py-4 border-t border-subtle">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
