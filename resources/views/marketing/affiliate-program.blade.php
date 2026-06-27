@extends('layouts.marketing')

@section('title', 'برنامج الإحالات — اربح مع دراهم')

@section('meta')
<meta name="description" content="انضم لبرنامج إحالات دراهم واربح عمولات تصل إلى 45% على كل اشتراك مدفوع تُحيله.">
@endsection

@section('body-class', 'font-sans text-g-body bg-g-light overflow-x-hidden')

@section('content')
@php
    try {
        $pageAffiliate = auth()->check() ? auth()->user()->affiliate : null;
    } catch (\Throwable $e) {
        $pageAffiliate = null;
    }

    if (!auth()->check()) {
        $ctaUrl   = route('register');
        $ctaLabel = 'ابدأ الآن مجاناً';
    } elseif (!$pageAffiliate) {
        $ctaUrl   = route('affiliates.join');
        $ctaLabel = 'انضم للبرنامج';
    } else {
        $ctaUrl   = route('affiliates.dashboard');
        $ctaLabel = 'اذهب للوحة الإحالات';
    }
@endphp
<main>

    {{-- ══════════════ Hero ══════════════ --}}
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
        <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
        <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]"></div>

        <div class="relative mx-auto flex min-h-[560px] max-w-[1142px] flex-col items-center justify-center px-6 py-24 text-center">

            {{-- Badge --}}
            <span class="mb-6 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-[#63f8c7]">
                💰 برنامج الإحالات
            </span>

            <h1 class="max-w-[900px] text-[40px] font-bold leading-[1.25] text-white sm:text-[56px] lg:text-[63px]">
                اربح من ترشيح دراهم<br>
                <span class="bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-transparent">لأصدقائك</span>
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-8 text-white/80 sm:text-lg">
                شارك رابطك الخاص، وكلما اشترك عميل جديد في إحدى خطط دراهم المدفوعة،
                تحصل على عمولة تصل إلى <strong class="text-[#63f8c7]">45%</strong>.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ $ctaUrl }}"
                   class="rounded bg-g-orange px-8 py-4 text-base font-bold text-white hover:opacity-90 transition-opacity">
                    {{ $ctaLabel }}
                </a>
                <a href="#how-it-works"
                   class="rounded border-2 border-white/20 px-7 py-4 text-base font-medium text-white hover:bg-white/10 transition-colors">
                    كيف يعمل البرنامج؟
                </a>
            </div>

            {{-- Quick stats --}}
            <div class="mt-16 mx-auto w-full max-w-lg flex gap-4">
                <div class="flex-1 rounded-2xl border border-white/10 bg-white/10 p-4 text-center">
                    <p class="text-2xl font-bold text-white">45%</p>
                    <p class="mt-1 text-xs text-white/70">أعلى عمولة</p>
                </div>
                <div class="flex-1 rounded-2xl border border-white/10 bg-white/10 p-4 text-center">
                    <p class="text-2xl font-bold text-white">$20</p>
                    <p class="mt-1 text-xs text-white/70">حد الصرف</p>
                </div>
                <div class="flex-1 rounded-2xl border border-white/10 bg-white/10 p-4 text-center">
                    <p class="text-2xl font-bold text-white">∞</p>
                    <p class="mt-1 text-xs text-white/70">بلا سقف للأرباح</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ كيف يعمل البرنامج ══════════════ --}}
    <section id="how-it-works" class="bg-white px-5 py-20">
        <div class="mx-auto max-w-[1142px]">
            <div class="mb-14 text-center">
                <h2 class="text-[32px] font-bold text-g-dark sm:text-[40px]">كيف يعمل البرنامج؟</h2>
                <p class="mt-3 text-base text-g-muted">أربع خطوات بسيطة تبدأ بها الكسب</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['num' => '١', 'icon' => '📝', 'title' => 'انضم للبرنامج',       'desc' => 'سجّل طلبك وانتظر موافقة الفريق خلال 1–3 أيام عمل.'],
                    ['num' => '٢', 'icon' => '🔗', 'title' => 'احصل على رابط خاص',   'desc' => 'رابط فريد يُنسَب إليك كل مستخدم يسجّل عبره.'],
                    ['num' => '٣', 'icon' => '📣', 'title' => 'شارك الرابط',          'desc' => 'واتساب، سوشيال، يوتيوب، مدوّنة — أينما جمهورك.'],
                    ['num' => '٤', 'icon' => '💵', 'title' => 'اربح عند أول اشتراك', 'desc' => 'عمولتك تُحسب تلقائياً فور أول دفعة مدفوعة.'],
                ] as $item)
                <div class="relative flex flex-col gap-4 rounded-2xl border border-g-border bg-g-light p-6">
                    <span class="text-[11px] font-bold tracking-widest text-g-muted">{{ $item['num'] }}</span>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-g-green/10 text-2xl">
                        {{ $item['icon'] }}
                    </div>
                    <div>
                        <h3 class="font-bold text-g-dark">{{ $item['title'] }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-g-muted">{{ $item['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ نسب العمولات ══════════════ --}}
    <section id="commissions" class="bg-g-light px-5 py-20">
        <div class="mx-auto max-w-[860px]">
            <div class="mb-12 text-center">
                <h2 class="text-[32px] font-bold text-g-dark sm:text-[40px]">نسب العمولات</h2>
                <p class="mt-3 text-base text-g-muted">كلما زاد عدد من تُحيلهم، ارتفعت نسبة عمولتك</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-g-border bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-g-border bg-g-light">
                            <th class="px-6 py-4 text-right font-bold text-g-dark">المستوى</th>
                            <th class="px-6 py-4 text-center font-bold text-g-dark">عدد الاشتراكات</th>
                            <th class="px-6 py-4 text-center font-bold text-g-dark">العمولة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-g-border">
                        <tr class="transition-colors hover:bg-g-light">
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-g-light2 px-3 py-1 text-xs font-bold text-g-body">Standard</span>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-g-dark">0 – 9</td>
                            <td class="px-6 py-4 text-center text-lg font-bold text-g-purple">30%</td>
                        </tr>
                        <tr class="transition-colors hover:bg-g-light">
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Silver</span>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-g-dark">10 – 29</td>
                            <td class="px-6 py-4 text-center text-lg font-bold text-g-purple">35%</td>
                        </tr>
                        <tr class="transition-colors hover:bg-g-light">
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">Gold</span>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-g-dark">30 – 99</td>
                            <td class="px-6 py-4 text-center text-lg font-bold text-g-purple">40%</td>
                        </tr>
                        <tr class="bg-g-purple/5 transition-colors hover:bg-g-purple/10">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full bg-g-purple/10 px-3 py-1 text-xs font-bold text-g-purple">🏆 Platinum</span>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-g-dark">100+</td>
                            <td class="px-6 py-4 text-center text-xl font-bold text-g-green">45%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-center text-xs text-g-muted">العمولة تُحسب على أول اشتراك مدفوع فقط — لا عمولة على التجديد.</p>
        </div>
    </section>

    {{-- ══════════════ أمثلة الأرباح ══════════════ --}}
    <section class="bg-white px-5 py-20">
        <div class="mx-auto max-w-[1142px]">
            <div class="mb-12 text-center">
                <h2 class="text-[32px] font-bold text-g-dark sm:text-[40px]">أمثلة على أرباحك</h2>
                <p class="mt-3 text-base text-g-muted">بناءً على خطط دراهم السنوية — والنسبة ترتفع كلما نمت</p>
            </div>

            <div class="mx-auto grid max-w-2xl grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Pro --}}
                <div class="rounded-2xl border border-g-border bg-g-light p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-g-purple/10 text-lg">⚡</div>
                        <div>
                            <p class="font-bold text-g-dark">Pro سنوي</p>
                            <p class="text-xs text-g-muted">$127 / سنة</p>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-g-muted">عمولة Standard (30%)</span>
                            <span class="font-bold text-g-dark">$38.10</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-g-muted">عمولة Platinum (45%)</span>
                            <span class="font-bold text-g-green">$57.15</span>
                        </div>
                    </div>
                </div>

                {{-- Business --}}
                <div class="rounded-2xl border-2 border-g-purple/20 bg-g-purple/5 p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-g-purple/15 text-lg">🚀</div>
                        <div>
                            <p class="font-bold text-g-dark">Business سنوي</p>
                            <p class="text-xs text-g-muted">$337 / سنة</p>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-g-muted">عمولة Standard (30%)</span>
                            <span class="font-bold text-g-dark">$101.10</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-g-muted">عمولة Platinum (45%)</span>
                            <span class="font-bold text-g-green">$151.65</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ لماذا تنضم ══════════════ --}}
    <section class="bg-g-light px-5 py-20">
        <div class="mx-auto max-w-[1142px]">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div>
                    <h2 class="text-[32px] font-bold text-g-dark sm:text-[40px]">لماذا تنضم لبرنامجنا؟</h2>
                    <p class="mt-4 leading-relaxed text-g-muted">
                        مصمّم للمستقلين وصنّاع المحتوى والمجتمعات المهنية — بدون تعقيد، وبدون حد للأرباح.
                    </p>
                    <ul class="mt-8 space-y-5">
                        @foreach([
                            ['icon' => '🔗', 'text' => 'رابط إحالة خاص بك يعمل فوراً بعد الاعتماد'],
                            ['icon' => '📊', 'text' => 'لوحة متابعة كاملة للأرباح والعملاء والعمولات'],
                            ['icon' => '💸', 'text' => 'طلب صرف عند وصول الرصيد إلى $20'],
                            ['icon' => '📈', 'text' => 'عمولات تصاعدية تصل إلى 45% كلما نمت'],
                            ['icon' => '📱', 'text' => 'مناسب للمستقلين وصنّاع المحتوى والمجتمعات المهنية'],
                        ] as $b)
                        <li class="flex items-start gap-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-g-green/10 text-base">{{ $b['icon'] }}</span>
                            <span class="pt-1.5 text-sm leading-relaxed text-g-dark">{{ $b['text'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- CTA Box --}}
                <div class="overflow-hidden rounded-2xl bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)] p-8 text-center">
                    <div class="absolute -top-8 -start-8 h-32 w-32 rounded-full bg-g-green opacity-15 blur-[30px] pointer-events-none"></div>
                    <p class="mb-4 text-4xl">💰</p>
                    <h3 class="text-xl font-bold text-white">ابدأ في الكسب اليوم</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/80">انضم للبرنامج مجاناً، احصل على رابطك، وابدأ في مشاركته.</p>
                    <a href="{{ $ctaUrl }}"
                       class="mt-6 inline-flex items-center gap-2 rounded bg-g-orange px-7 py-3 text-sm font-bold text-white hover:opacity-90 transition-opacity">
                        {{ $ctaLabel }}
                    </a>
                    <p class="mt-4 text-xs text-white/50">الانضمام مجاني تماماً</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ الأسئلة الشائعة ══════════════ --}}
    <section class="bg-white px-5 py-20">
        <div class="mx-auto max-w-[800px]">
            <div class="mb-12 text-center">
                <h2 class="text-[32px] font-bold text-g-dark sm:text-[40px]">الأسئلة الشائعة</h2>
            </div>

            <div class="space-y-3">
                @foreach([
                    ['q' => 'هل الانضمام لبرنامج الإحالات مجاني؟',
                     'a' => 'نعم، الانضمام مجاني تماماً. كل ما تحتاجه هو حساب في دراهم وتقديم طلب الانضمام. سيراجع فريقنا طلبك خلال 1–3 أيام عمل.'],
                    ['q' => 'متى تُحسب العمولة؟',
                     'a' => 'تُحسب العمولة تلقائياً عند أول دفعة مدفوعة ناجحة من العميل الذي أحلته. تظهر في لوحتك فور معالجة الدفعة.'],
                    ['q' => 'متى أستطيع طلب صرف أرباحي؟',
                     'a' => 'يمكنك تقديم طلب صرف عند وصول رصيدك المتاح إلى $20 أو ما يعادله. يُعالَج الطلب خلال 3–5 أيام عمل.'],
                    ['q' => 'هل توجد عمولة على تجديد الاشتراك؟',
                     'a' => 'لا. العمولة تُحسب على أول اشتراك مدفوع فقط لكل عميل. التجديدات اللاحقة لا تُنشئ عمولة إضافية.'],
                    ['q' => 'هل يمكنني مشاركة الرابط في واتساب والسوشيال ميديا؟',
                     'a' => 'بالتأكيد! رابطك يعمل في أي مكان — واتساب، تويتر/X، إنستجرام، يوتيوب، تيك توك، مدوّنتك، أو حتى في محادثات مباشرة.'],
                ] as $faq)
                <details class="group rounded-2xl border border-g-border bg-g-light overflow-hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-white">
                        <span class="font-bold text-g-dark text-sm">{{ $faq['q'] }}</span>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm transition-transform group-open:rotate-45">
                            <svg class="h-3.5 w-3.5 text-g-muted" viewBox="0 0 14 14" fill="none">
                                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-g-border bg-white px-6 py-5 text-sm leading-relaxed text-g-muted">
                        {{ $faq['a'] }}
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ Final CTA ══════════════ --}}
    <section class="relative overflow-hidden bg-[linear-gradient(to_right,#310e8e_0%,#13c597_100%)] px-5 py-24 text-center">
        <div class="absolute start-[-200px] top-[-200px] size-[500px] rounded-full border-[30px] border-white/10"></div>
        <div class="absolute end-[-100px] bottom-[-100px] size-[240px] rounded-full border-[20px] border-white/10"></div>

        <div class="relative mx-auto flex max-w-[700px] flex-col items-center gap-4">
            <span class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-[#63f8c7]">
                الفرصة بين يديك
            </span>
            <h2 class="text-[36px] font-bold leading-tight text-white sm:text-[52px]">
                جاهز للبدء؟
            </h2>
            <p class="max-w-[620px] pt-2 text-base leading-8 text-white/70 sm:text-lg">
                انضم لبرنامج الإحالات اليوم وحوّل شبكتك إلى مصدر دخل حقيقي.
            </p>
            <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                <a href="{{ $ctaUrl }}"
                   class="rounded bg-g-orange px-10 py-5 text-lg font-bold text-white hover:opacity-90 transition-opacity">
                    {{ $ctaLabel }}
                </a>
                <a href="{{ route('marketing.pricing') }}"
                   class="rounded border-2 border-white/20 px-10 py-5 text-lg font-bold text-white hover:bg-white/10 transition-colors">
                    عرض الأسعار
                </a>
            </div>
        </div>
    </section>

</main>
@endsection
