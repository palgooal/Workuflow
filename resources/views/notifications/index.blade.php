@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">الإشعارات</h1>
            <p class="mt-0.5 text-sm text-gray-500">
                تنبيهات الديون والاستحقاقات
            </p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800
                               border border-indigo-200 hover:bg-indigo-50 rounded-xl transition">
                    تحديد الكل كمقروء
                </button>
            </form>
        @endif
    </div>

    {{-- Notifications List --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">

        @if($notifications->isEmpty())
            <div class="py-16">
                <x-empty-state
                    title="لا توجد إشعارات"
                    description="ستظهر هنا التنبيهات المتعلقة بالديون والاستحقاقات"
                />
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($notifications as $notification)
                    @php
                        $data    = $notification->data;
                        $isRead  = !is_null($notification->read_at);
                        $icon    = $data['icon'] ?? '🔔';
                        $type    = $data['type'] ?? 'general';
                    @endphp
                    <div class="flex items-start gap-4 px-5 py-4 transition
                                {{ $isRead ? 'bg-white' : 'bg-indigo-50/30' }}
                                hover:bg-gray-50 group">

                        {{-- Icon --}}
                        <div class="shrink-0 mt-0.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl
                                {{ $type === 'debt_overdue' ? 'bg-red-100' : ($type === 'debt_due_soon' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                                {{ $icon }}
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $data['title'] ?? 'إشعار' }}
                                        @if(!$isRead)
                                            <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block shrink-0"></span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-0.5">
                                        {{ $data['message'] ?? '' }}
                                    </p>
                                    @if(isset($data['amount']) && isset($data['currency']))
                                        <p class="text-xs text-gray-400 mt-1">
                                            المبلغ المتبقي:
                                            <strong class="text-gray-700">
                                                {{ number_format($data['amount'], 2) }} {{ $data['currency'] }}
                                            </strong>
                                        </p>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 shrink-0 mt-0.5">
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
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            عرض التفاصيل ←
                                        </button>
                                    </form>
                                @endif
                                @if(!$isRead)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}"
                                          class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs text-gray-400 hover:text-gray-600">
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
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
