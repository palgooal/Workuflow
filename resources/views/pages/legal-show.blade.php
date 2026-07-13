{{--
    قالب عرض موحّد للصفحات القانونية الأربع المُرحَّلة إلى نظام إدارة
    المحتوى (Page Model) — يُستخدَم فقط عبر PageController@renderLegal.

    يُعيد إنتاج نفس البنية البصرية الحالية (Hero بنفس التدرّج، شريط TOC
    جانبي، article.legal-page-content) بنفس Tailwind classes الحالية في
    darahem-front/assets/css/app.css — دون أي CSS جديد، ودون أي تغيير في
    الهوية البصرية. جدول المحتويات يُبنى تلقائياً من عناوين H2 داخل
    المحتوى (App\Support\Content\TableOfContentsBuilder) بدل مصفوفة ثابتة،
    ليبقى متزامناً تلقائياً مع أي تعديل يجريه الأدمن.
--}}
@extends('layouts.marketing')

@section('title', ($page->meta_title ?: $page->title) . ' — دراهم | مال وأعمال')

@section('meta')
<meta name="description" content="{{ $page->meta_description }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ route($routeName) }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $page->meta_title ?: $page->title }} — دراهم | مال وأعمال">
<meta property="og:description" content="{{ $page->og_description ?: $page->meta_description }}">
<meta property="og:url" content="{{ route($routeName) }}">
<meta property="og:locale" content="ar_AR">
@endsection

@section('body-class', 'bg-white text-g-body antialiased overflow-x-hidden font-sans')

@section('content')
<main>
    <!-- Hero — نفس تصميم بنر بقية الصفحات القانونية -->
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
        <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
        <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]"></div>

        <div class="relative mx-auto flex min-h-[520px] max-w-[1142px] flex-col items-center justify-center px-6 py-24 text-center sm:min-h-[595px]">
            <h1 class="max-w-[1104px] bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-[40px] font-bold leading-[1.15] text-transparent sm:text-[56px] lg:text-[63px] lg:leading-[72px]">
                {{ $page->title }}
            </h1>
            @if ($page->excerpt)
                <p class="mt-6 text-base leading-8 text-white sm:text-lg">{{ $page->excerpt }}</p>
            @endif
            @if ($page->last_reviewed_at)
                <p class="mt-4 text-sm text-white/70">آخر تحديث: {{ $page->last_reviewed_at->translatedFormat('d F Y') }}</p>
            @endif
        </div>
    </section>

    <!-- محتوى الصفحة + جدول المحتويات -->
    <section class="px-5 py-16">
        <div class="max-w-6xl mx-auto legal-page-layout lg:grid-cols-[256px_minmax(0,1fr)] lg:gap-12">

            @if (count($toc))
                <!-- جدول المحتويات — Mobile (قابل للفتح) -->
                <details class="lg:hidden rounded-2xl border border-g-border bg-g-light px-4 py-3">
                    <summary class="text-sm font-bold text-g-dark cursor-pointer select-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-g-green rounded">محتويات الصفحة</summary>
                    <nav aria-label="محتويات صفحة {{ $page->title }}" class="mt-2">
                        <ul class="flex flex-col gap-1">
                            @foreach ($toc as $item)
                                <li><a href="#{{ $item['id'] }}" class="legal-page-toc-link">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                </details>

                <!-- جدول المحتويات — Desktop (شريط جانبي Sticky) -->
                <aside class="hidden lg:block lg:sticky lg:top-28 lg:self-start">
                    <nav aria-label="محتويات صفحة {{ $page->title }}" class="rounded-xl bg-g-light p-2">
                        <ul class="flex flex-col gap-0.5">
                            @foreach ($toc as $item)
                                <li><a href="#{{ $item['id'] }}" class="legal-page-toc-link">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                </aside>
            @endif

            <!-- محتوى الوثيقة (مُعقَّم مسبقاً عبر PageContentSanitizer عند الحفظ) -->
            <article class="legal-page-content @if (! count($toc)) lg:col-span-2 @endif">
                {!! $html !!}
            </article>
        </div>
    </section>
</main>
@endsection
