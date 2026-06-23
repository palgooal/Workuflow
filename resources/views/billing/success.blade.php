@extends('layouts.app')

@section('title', 'تم الاشتراك بنجاح')

@section('content')
<div class="max-w-lg mx-auto text-center py-16">

    {{-- Success Icon --}}
    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        تم الاشتراك بنجاح! 🎉
    </h1>

    <p class="text-slate-500 dark:text-slate-400 mb-2">
        تم تفعيل خطة <span class="font-semibold text-brand dark:text-brand/70">{{ $plan->label() }}</span> على حسابك.
    </p>

    <p class="text-sm text-slate-400 dark:text-slate-500 mb-8">
        قد يستغرق تحديث بيانات الاشتراك بضع ثوانٍ. أعد تحميل الصفحة إذا لم يظهر التغيير فوراً.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('dashboard') }}"
           class="px-6 py-2.5 bg-brand text-white rounded-xl font-medium hover:bg-brand-600 transition text-sm">
            العودة للوحة التحكم
        </a>
        <a href="{{ route('billing.index') }}"
           class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition text-sm">
            إدارة الاشتراك
        </a>
    </div>

</div>
@endsection
