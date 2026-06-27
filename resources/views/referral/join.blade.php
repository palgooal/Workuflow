@extends('layouts.app')

@section('title', 'الانضمام لبرنامج الشركاء')

@section('content')
<div class="max-w-xl mx-auto space-y-8">

    {{-- Header --}}
    <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-100 rounded-2xl mb-2">
            <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-ink tracking-tight">برنامج شركاء دراهم</h1>
        <p class="text-slate-500 text-sm max-w-sm mx-auto">
            انضم إلينا واكسب عمولة على كل اشتراك تجلبه — حتى <strong>45%</strong> عمولة متكررة
        </p>
    </div>

    {{-- Benefits --}}
    <div class="grid grid-cols-3 gap-4 text-center">
        @foreach([
            ['٣٠٪', 'عمولة ابتداءً', 'text-brand'],
            ['٦٠ يوم', 'صلاحية الإحالة', 'text-emerald-600'],
            ['٢٠$', 'حد أدنى للصرف', 'text-violet-600'],
        ] as [$val, $label, $color])
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <div class="text-xl font-bold {{ $color }}">{{ $val }}</div>
            <div class="text-xs text-muted mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('affiliates.store') }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
        @csrf

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="space-y-1">
            <label class="block text-sm font-medium text-ink">الاسم الكامل</label>
            <input type="text" name="name" value="{{ old('name', $name) }}" required
                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-ink
                          focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition
                          @error('name') border-red-400 @enderror"/>
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-medium text-ink">البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required
                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-ink
                          focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition
                          @error('email') border-red-400 @enderror"/>
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-medium text-ink">
                واتساب <span class="text-muted font-normal">(اختياري)</span>
            </label>
            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                   placeholder="+970599123456"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-ink
                          focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition"/>
            <p class="text-xs text-muted">سنستخدمه للتواصل معك بشأن طلبات الصرف</p>
        </div>

        <button type="submit"
                class="w-full py-3 bg-brand hover:bg-brand-600 text-white font-semibold rounded-xl
                       transition text-sm">
            تقديم الطلب
        </button>

        <p class="text-xs text-center text-muted">
            بتقديم الطلب توافق على
            <a href="{{ route('legal.terms') }}" class="text-brand hover:underline">شروط الاستخدام</a>
        </p>
    </form>

</div>
@endsection
