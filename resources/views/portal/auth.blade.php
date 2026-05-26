@extends('portal.layouts.portal')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-l from-indigo-600 to-indigo-800 px-8 py-8 text-center text-white">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold">بوابة العميل</h1>
                <p class="text-indigo-200 text-sm mt-1">أدخل رمز الوصول المُرسل إليك</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                @if ($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('portal.authenticate') }}">
                    @csrf
                    <div class="mb-5">
                        <label for="token" class="block text-sm font-medium text-gray-700 mb-1.5">
                            رمز الوصول
                        </label>
                        <input
                            type="text"
                            id="token"
                            name="token"
                            value="{{ old('token', $prefillToken ?? '') }}"
                            placeholder="أدخل الرمز المكوّن من 64 حرفاً..."
                            maxlength="64"
                            autocomplete="off"
                            spellcheck="false"
                            class="w-full border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono ltr text-left"
                            dir="ltr"
                            required
                        >
                        <p class="text-xs text-gray-400 mt-1.5">
                            أرسله لك صاحب الحساب عبر البريد الإلكتروني
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-3 rounded-xl transition shadow-sm">
                        دخول
                    </button>
                </form>

                <div class="mt-5 text-center">
                    <a href="{{ route('portal.access') }}"
                       class="text-xs text-gray-400 hover:text-indigo-600 transition">
                        لا يوجد رمز؟ تعرّف على كيفية الحصول عليه
                    </a>
                </div>
            </div>
        </div>

        {{-- Security notice --}}
        <div class="mt-4 flex items-center gap-2 justify-center text-xs text-gray-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            اتصال آمن — رمزك لا يُخزَّن على هذا الجهاز
        </div>
    </div>
</div>
@endsection
