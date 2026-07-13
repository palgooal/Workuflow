{{--
    قالب عرض عام لأي صفحة منشورة عبر نظام إدارة المحتوى (غير الصفحات
    القانونية الأربع، التي لها قالبها المخصص pages/legal-show.blade.php
    وروابطها الرسمية الخاصة). يُستخدَم عبر GET /pages/{slug}.

    عمداً بسيط (Rich Editor + عرض نثري واحد، بلا شريط TOC جانبي) تماشياً
    مع قاعدة "لا تنشئ Page Builder" — يعيد استخدام نفس مكوّن
    legal-page-content من darahem-front/assets/css/app.css دون أي CSS جديد.
--}}
@extends('layouts.marketing')

@section('title', ($page->meta_title ?: $page->title) . ' — دراهم | مال وأعمال')

@section('meta')
<meta name="description" content="{{ $page->meta_description }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/pages/' . $page->slug) }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $page->meta_title ?: $page->title }} — دراهم | مال وأعمال">
<meta property="og:description" content="{{ $page->og_description ?: $page->meta_description }}">
<meta property="og:url" content="{{ url('/pages/' . $page->slug) }}">
<meta property="og:locale" content="ar_AR">
@endsection

@section('body-class', 'bg-white text-g-body antialiased overflow-x-hidden font-sans')

@section('content')
<main>
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
        <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
        <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]"></div>

        <div class="relative mx-auto flex min-h-[420px] max-w-[1142px] flex-col items-center justify-center px-6 py-20 text-center">
            <h1 class="max-w-[1104px] bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-[40px] font-bold leading-[1.15] text-transparent sm:text-[56px] lg:text-[63px] lg:leading-[72px]">
                {{ $page->title }}
            </h1>
            @if ($page->excerpt)
                <p class="mt-6 text-base leading-8 text-white sm:text-lg">{{ $page->excerpt }}</p>
            @endif
        </div>
    </section>

    <section class="px-5 py-16">
        <article class="legal-page-content max-w-3xl mx-auto">
            {!! $html !!}
        </article>
    </section>
</main>
@endsection
