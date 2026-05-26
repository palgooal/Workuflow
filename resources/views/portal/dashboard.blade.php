@extends('portal.layouts.portal')
@php $pageTitle = 'الرئيسية'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Greeting --}}
    <div>
        <h1 class="text-xl font-bold text-gray-800">
            مرحباً، {{ $client->name }} 👋
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            ملخص حسابك الحالي
            @if($portalToken->expires_at)
                · ينتهي رمزك بتاريخ {{ $portalToken->expires_at->format('Y/m/d') }}
            @endif
        </p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-500 mb-1">إجمالي المستحق</div>
            <div class="text-2xl font-bold {{ $summary['outstanding'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                {{ number_format($summary['outstanding'], 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">
                {{ $summary['outstanding'] > 0 ? 'يرجى التسوية' : 'حسابك صافٍ ✓' }}
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-500 mb-1">إجمالي الفواتير</div>
            <div class="text-2xl font-bold text-gray-800">
                {{ number_format($summary['total_revenue'], 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $summary['invoice_count'] }} فاتورة</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-500 mb-1">المدفوع</div>
            <div class="text-2xl font-bold text-emerald-600">
                {{ number_format($summary['total_paid'], 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">
                @if($summary['last_payment_at'])
                    آخر دفعة: {{ $summary['last_payment_at']->format('Y/m/d') }}
                @else
                    لا توجد مدفوعات مسجلة
                @endif
            </div>
        </div>
    </div>

    {{-- Payment progress --}}
    @if($summary['total_revenue'] > 0)
        @php $paidPercent = min(100, round(($summary['total_paid'] / $summary['total_revenue']) * 100)); @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-2 text-sm">
                <span class="text-gray-600 font-medium">نسبة السداد</span>
                <span class="font-bold {{ $paidPercent >= 100 ? 'text-emerald-600' : 'text-gray-800' }}">
                    {{ $paidPercent }}%
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="{{ $paidPercent >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }} h-2.5 rounded-full transition-all"
                     style="width: {{ $paidPercent }}%"></div>
            </div>
        </div>
    @endif

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        @if($portalToken->hasPermission(\App\Modules\CRM\Enums\PortalPermission::ViewInvoices))
            <a href="{{ route('portal.invoices') }}"
               class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow-md transition group flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center shrink-0 transition">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-800">الفواتير</div>
                    <div class="text-xs text-gray-400">{{ $summary['invoice_count'] }} فاتورة</div>
                </div>
                <svg class="w-4 h-4 text-gray-300 mr-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif

        <a href="{{ route('portal.profile') }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow-md transition group flex items-center gap-4">
            <div class="w-10 h-10 bg-gray-100 group-hover:bg-gray-200 rounded-xl flex items-center justify-center shrink-0 transition">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-gray-800">ملفي الشخصي</div>
                <div class="text-xs text-gray-400">معلوماتي وبيانات التواصل</div>
            </div>
            <svg class="w-4 h-4 text-gray-300 mr-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Recent Projects --}}
    @if($projects->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">المشاريع الأخيرة</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($projects as $project)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full shrink-0"
                             style="background-color: {{ $project->color ?? '#6366F1' }}"></div>
                        <span class="text-sm text-gray-800 flex-1">{{ $project->name }}</span>
                        <span class="text-xs text-gray-400">{{ $project->created_at->format('Y/m/d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
