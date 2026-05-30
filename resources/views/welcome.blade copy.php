<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>دراهم | منصة مالية ذكية للمستقلين وأصحاب الأعمال</title>
    <meta name="description" content="دراهم منصة مالية ذكية تساعد المستقلين وأصحاب الأعمال الصغيرة على تتبع الدخل والمصروفات، إدارة العملاء، الفواتير، عروض الأسعار، والمشاريع بوضوح كامل.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F8FAFC] text-[#0F172A] antialiased font-cairo">
    @php
        $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#';
        $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    @endphp

    <div class="min-h-screen overflow-hidden">
        {{-- Header Section --}}
        <header class="sticky top-0 z-50 border-b border-white/60 bg-white/75 shadow-sm shadow-slate-900/5 backdrop-blur-xl">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8" aria-label="التنقل الرئيسي">
                <a href="#" class="flex items-center gap-3" aria-label="دراهم">
                    <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-[#320E8E] to-[#14C698] text-lg font-black text-white shadow-lg shadow-[#320E8E]/20">د</span>
                    <span class="text-2xl font-black tracking-tight text-[#320E8E]">دراهم</span>
                </a>

                <div class="hidden items-center gap-8 text-sm font-semibold text-[#475569] lg:flex">
                    <a href="#home" class="transition hover:text-[#320E8E]">الرئيسية</a>
                    <a href="#features" class="transition hover:text-[#320E8E]">المميزات</a>
                    <a href="#how-it-works" class="transition hover:text-[#320E8E]">كيف يعمل</a>
                    <a href="#pricing" class="transition hover:text-[#320E8E]">الأسعار</a>
                    <a href="#faq" class="transition hover:text-[#320E8E]">الأسئلة الشائعة</a>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    <a href="{{ $loginUrl }}" class="rounded-full px-5 py-2.5 text-sm font-bold text-[#475569] transition hover:bg-slate-100 hover:text-[#320E8E]">تسجيل الدخول</a>
                    <a href="{{ $registerUrl }}" class="rounded-full bg-[#320E8E] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#320E8E]/20 transition hover:-translate-y-0.5 hover:bg-[#270B70]">ابدأ مجاناً</a>
                </div>

                <details class="group relative lg:hidden">
                    <summary class="grid size-11 cursor-pointer list-none place-items-center rounded-2xl border border-slate-200 bg-white text-[#0F172A] shadow-sm transition hover:border-[#320E8E]/30">
                        <span class="sr-only">فتح القائمة</span>
                        <span class="h-0.5 w-5 rounded-full bg-current shadow-[0_7px_0_current,0_-7px_0_current]"></span>
                    </summary>
                    <div class="absolute left-0 mt-3 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl shadow-slate-900/10">
                        <div class="grid gap-1 text-sm font-bold text-[#475569]">
                            <a href="#home" class="rounded-xl px-4 py-3 hover:bg-slate-50 hover:text-[#320E8E]">الرئيسية</a>
                            <a href="#features" class="rounded-xl px-4 py-3 hover:bg-slate-50 hover:text-[#320E8E]">المميزات</a>
                            <a href="#how-it-works" class="rounded-xl px-4 py-3 hover:bg-slate-50 hover:text-[#320E8E]">كيف يعمل</a>
                            <a href="#pricing" class="rounded-xl px-4 py-3 hover:bg-slate-50 hover:text-[#320E8E]">الأسعار</a>
                            <a href="#faq" class="rounded-xl px-4 py-3 hover:bg-slate-50 hover:text-[#320E8E]">الأسئلة الشائعة</a>
                        </div>
                        <div class="mt-3 grid gap-2 border-t border-slate-100 pt-3">
                            <a href="{{ $loginUrl }}" class="rounded-xl px-4 py-3 text-center text-sm font-bold text-[#475569] hover:bg-slate-50">تسجيل الدخول</a>
                            <a href="{{ $registerUrl }}" class="rounded-xl bg-[#320E8E] px-4 py-3 text-center text-sm font-bold text-white">ابدأ مجاناً</a>
                        </div>
                    </div>
                </details>
            </nav>
        </header>

        <main>
            {{-- Hero Section --}}
            <section id="home" class="relative isolate overflow-hidden">
                <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#e2e8f0_1px,transparent_1px),linear-gradient(to_bottom,#e2e8f0_1px,transparent_1px)] bg-[size:40px_40px] opacity-45"></div>
                <div class="absolute right-1/2 top-0 -z-10 h-[42rem] w-[42rem] translate-x-1/2 rounded-full bg-[#320E8E]/10 blur-3xl"></div>
                <div class="absolute -left-24 top-44 -z-10 h-80 w-80 rounded-full bg-[#14C698]/15 blur-3xl"></div>

                <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-20 pt-16 sm:px-6 sm:pt-20 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:pb-28 lg:pt-24">
                    <div class="max-w-3xl">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#14C698]/30 bg-white/80 px-4 py-2 text-sm font-bold text-[#320E8E] shadow-sm backdrop-blur">
                            <span class="size-2 rounded-full bg-[#14C698]"></span>
                            منصة مالية مبنية لنمو الأعمال الصغيرة
                        </div>

                        <h1 class="text-4xl font-black leading-tight tracking-tight text-[#0F172A] sm:text-5xl lg:text-6xl">
                            نظّم أموالك ومشاريعك من مكان واحد
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-[#475569] sm:text-xl">
                            دراهم منصة مالية ذكية تساعد المستقلين وأصحاب الأعمال الصغيرة على تتبع الدخل والمصروفات، إدارة العملاء، الفواتير، عروض الأسعار، والمشاريع بوضوح كامل.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $registerUrl }}" class="inline-flex items-center justify-center rounded-full bg-gradient-to-l from-[#320E8E] to-[#14C698] px-7 py-4 text-base font-black text-white shadow-xl shadow-[#320E8E]/20 transition hover:-translate-y-0.5">
                                ابدأ الآن مجاناً
                            </a>
                            <a href="#features" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-7 py-4 text-base font-black text-[#320E8E] shadow-sm transition hover:-translate-y-0.5 hover:border-[#320E8E]/30">
                                استعرض المميزات
                            </a>
                        </div>

                        <div class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-slate-200 pt-8">
                            <div>
                                <div class="text-2xl font-black text-[#14C698]">3x</div>
                                <div class="mt-1 text-sm font-semibold text-[#475569]">وضوح أسرع</div>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-[#14C698]">24/7</div>
                                <div class="mt-1 text-sm font-semibold text-[#475569]">متابعة مالية</div>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-[#14C698]">0</div>
                                <div class="mt-1 text-sm font-semibold text-[#475569]">تعقيد محاسبي</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-6 -z-10 rounded-[2rem] bg-gradient-to-br from-[#320E8E]/20 via-white to-[#14C698]/20 blur-2xl"></div>
                        <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 shadow-2xl shadow-slate-900/15 backdrop-blur">
                            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-full bg-red-400"></span>
                                    <span class="size-3 rounded-full bg-amber-400"></span>
                                    <span class="size-3 rounded-full bg-[#14C698]"></span>
                                </div>
                                <span class="rounded-full bg-[#320E8E]/10 px-3 py-1 text-xs font-black text-[#320E8E]">لوحة دراهم</span>
                            </div>

                            <div class="grid gap-5 p-5 sm:p-6">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-[#320E8E] p-4 text-white">
                                        <p class="text-xs font-bold text-white/70">صافي الربح</p>
                                        <p class="mt-3 text-2xl font-black">18,450₪</p>
                                        <p class="mt-2 text-xs font-bold text-[#14C698]">+18% هذا الشهر</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                        <p class="text-xs font-bold text-[#475569]">الدخل</p>
                                        <p class="mt-3 text-xl font-black text-[#0F172A]">32,900₪</p>
                                        <div class="mt-4 h-1.5 rounded-full bg-slate-200">
                                            <div class="h-1.5 w-4/5 rounded-full bg-[#14C698]"></div>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                        <p class="text-xs font-bold text-[#475569]">المصروفات</p>
                                        <p class="mt-3 text-xl font-black text-[#0F172A]">14,450₪</p>
                                        <div class="mt-4 h-1.5 rounded-full bg-slate-200">
                                            <div class="h-1.5 w-2/5 rounded-full bg-[#320E8E]"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                                    <div class="mb-5 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black text-[#0F172A]">التدفق النقدي</p>
                                            <p class="text-xs font-semibold text-[#475569]">آخر 6 أشهر</p>
                                        </div>
                                        <span class="rounded-full bg-[#14C698]/10 px-3 py-1 text-xs font-black text-[#0F8F70]">مستقر</span>
                                    </div>
                                    <div class="flex h-40 items-end gap-3">
                                        <div class="h-[38%] flex-1 rounded-t-xl bg-[#320E8E]/15"></div>
                                        <div class="h-[52%] flex-1 rounded-t-xl bg-[#320E8E]/25"></div>
                                        <div class="h-[46%] flex-1 rounded-t-xl bg-[#14C698]/35"></div>
                                        <div class="h-[68%] flex-1 rounded-t-xl bg-[#320E8E]/45"></div>
                                        <div class="h-[78%] flex-1 rounded-t-xl bg-[#14C698]/70"></div>
                                        <div class="h-[92%] flex-1 rounded-t-xl bg-gradient-to-t from-[#320E8E] to-[#14C698]"></div>
                                    </div>
                                </div>

                                <div class="grid gap-3">
                                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid size-10 place-items-center rounded-xl bg-[#14C698]/10 text-sm font-black text-[#0F8F70]">ف</span>
                                            <div>
                                                <p class="text-sm font-black">فاتورة تصميم متجر</p>
                                                <p class="text-xs font-semibold text-[#475569]">شركة وادي التقنية</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-black text-[#14C698]">4,800₪</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid size-10 place-items-center rounded-xl bg-[#320E8E]/10 text-sm font-black text-[#320E8E]">م</span>
                                            <div>
                                                <p class="text-sm font-black">اشتراك أدوات العمل</p>
                                                <p class="text-xs font-semibold text-[#475569]">مصروف تشغيلي</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-black text-[#320E8E]">-320₪</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Pain Points Section --}}
            <section class="py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <span class="text-sm font-black text-[#14C698]">لماذا تحتاج دراهم؟</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">الفوضى المالية تكلفك وقتاً وقرارات خاطئة</h2>
                    </div>
                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                            'لا تعرف صافي ربحك الحقيقي',
                            'الفواتير والديون تتأخر',
                            'العملاء والمشاريع مبعثرة',
                            'لا توجد رؤية واضحة للتدفق النقدي',
                        ] as $pain)
                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#320E8E]/20 hover:shadow-xl hover:shadow-slate-900/5">
                                <div class="mb-5 grid size-11 place-items-center rounded-2xl bg-red-50 text-lg font-black text-red-500">!</div>
                                <h3 class="text-lg font-black text-[#0F172A]">{{ $pain }}</h3>
                                <p class="mt-3 leading-7 text-[#475569]">دراهم يحول البيانات اليومية إلى رؤية واضحة تساعدك على اتخاذ القرار بسرعة وثقة.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Features Section --}}
            <section id="features" class="bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="text-sm font-black text-[#14C698]">مميزات متكاملة</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">كل ما تحتاجه لإدارة المال والعمل في منصة واحدة</h2>
                        <p class="mt-5 text-lg leading-8 text-[#475569]">واجهة بسيطة، تفاصيل مالية دقيقة، وأدوات عملية تجعل إدارة أعمالك اليومية أكثر وضوحاً.</p>
                    </div>

                    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['title' => 'إدارة المشاريع', 'copy' => 'اربط الدخل والمصروفات بكل مشروع واعرف ربحيته بشكل فوري.', 'icon' => 'م'],
                            ['title' => 'تتبع المعاملات', 'copy' => 'سجل الدخل والمصروفات وصنفها بدون جداول مبعثرة.', 'icon' => 'ت'],
                            ['title' => 'إدارة العملاء CRM', 'copy' => 'احتفظ ببيانات العملاء، المشاريع، الفواتير، والعروض في مكان واحد.', 'icon' => 'ع'],
                            ['title' => 'الفواتير', 'copy' => 'أنشئ فواتير واضحة وتابع المدفوع والمتأخر بسهولة.', 'icon' => 'ف'],
                            ['title' => 'عروض الأسعار', 'copy' => 'حوّل عروض الأسعار المقبولة إلى مشاريع وفواتير بخطوات أقل.', 'icon' => 'س'],
                            ['title' => 'التقارير والتحليلات', 'copy' => 'راقب الربح، التدفق النقدي، أداء العملاء، ومصاريف التشغيل.', 'icon' => 'ر'],
                        ] as $feature)
                            <article class="group rounded-2xl border border-slate-200 bg-[#F8FAFC] p-7 shadow-sm transition hover:-translate-y-1 hover:border-[#14C698]/40 hover:bg-white hover:shadow-xl hover:shadow-slate-900/5">
                                <div class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-[#320E8E] to-[#14C698] text-lg font-black text-white shadow-lg shadow-[#320E8E]/15 transition group-hover:scale-105">
                                    {{ $feature['icon'] }}
                                </div>
                                <h3 class="mt-6 text-xl font-black text-[#0F172A]">{{ $feature['title'] }}</h3>
                                <p class="mt-3 leading-7 text-[#475569]">{{ $feature['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- How It Works Section --}}
            <section id="how-it-works" class="py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                        <div>
                            <span class="text-sm font-black text-[#14C698]">كيف يعمل</span>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">من أول مشروع إلى قرار مالي واضح</h2>
                            <p class="mt-5 text-lg leading-8 text-[#475569]">ابدأ بإدخال أساسيات عملك، ثم دع دراهم يرتب لك الصورة المالية والتشغيلية يوماً بعد يوم.</p>
                        </div>
                        <div class="grid gap-5 md:grid-cols-3">
                            @foreach ([
                                ['step' => '01', 'title' => 'أضف مشروعك', 'copy' => 'أنشئ مشروعاً واربطه بعميل وميزانية أولية.'],
                                ['step' => '02', 'title' => 'سجّل دخلك ومصروفاتك', 'copy' => 'أدخل المعاملات وصنفها حسب المشروع أو العميل.'],
                                ['step' => '03', 'title' => 'تابع أرباحك وفواتيرك بوضوح', 'copy' => 'راقب الربح والفواتير المستحقة من لوحة واحدة.'],
                            ] as $item)
                                <div class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <span class="text-sm font-black text-[#14C698]">{{ $item['step'] }}</span>
                                    <h3 class="mt-5 text-xl font-black text-[#0F172A]">{{ $item['title'] }}</h3>
                                    <p class="mt-3 leading-7 text-[#475569]">{{ $item['copy'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- Dashboard Preview Section --}}
            <section class="bg-[#0F172A] py-20 text-white sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="text-sm font-black text-[#14C698]">لوحة تحكم عملية</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">أرقامك المهمة أمامك دائماً</h2>
                        <p class="mt-5 text-lg leading-8 text-slate-300">ملخص مالي مباشر يساعدك على قراءة أداء عملك بدون تعقيد أو تقارير طويلة.</p>
                    </div>

                    <div class="mt-12 overflow-hidden rounded-[2rem] border border-white/10 bg-white/10 p-4 shadow-2xl shadow-black/20 backdrop-blur sm:p-6">
                        <div class="grid gap-4 md:grid-cols-4">
                            @foreach ([
                                ['label' => 'إجمالي الدخل', 'value' => '32,900₪', 'tone' => 'text-[#14C698]'],
                                ['label' => 'المصروفات', 'value' => '14,450₪', 'tone' => 'text-white'],
                                ['label' => 'صافي الربح', 'value' => '18,450₪', 'tone' => 'text-[#14C698]'],
                                ['label' => 'الفواتير المستحقة', 'value' => '6,200₪', 'tone' => 'text-amber-300'],
                            ] as $stat)
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                                    <p class="text-sm font-bold text-slate-300">{{ $stat['label'] }}</p>
                                    <p class="mt-3 text-2xl font-black {{ $stat['tone'] }}">{{ $stat['value'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 grid gap-5 lg:grid-cols-[1.3fr_0.7fr]">
                            <div class="rounded-2xl border border-white/10 bg-[#111C33] p-5">
                                <div class="mb-6 flex items-center justify-between">
                                    <h3 class="font-black">الرسم البياني المالي</h3>
                                    <span class="rounded-full bg-[#14C698]/15 px-3 py-1 text-xs font-black text-[#14C698]">شهري</span>
                                </div>
                                <div class="flex h-64 items-end gap-3">
                                    @foreach ([44, 58, 50, 72, 66, 84, 92, 78, 96] as $height)
                                        <div class="flex flex-1 flex-col items-center gap-3">
                                            <div class="w-full rounded-t-2xl bg-gradient-to-t from-[#320E8E] to-[#14C698]" style="height: {{ $height }}%"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-[#111C33] p-5">
                                <h3 class="font-black">آخر المعاملات</h3>
                                <div class="mt-5 grid gap-3">
                                    @foreach ([
                                        ['name' => 'دفعة عميل', 'meta' => 'مشروع تطبيق حجوزات', 'value' => '+3,200₪', 'color' => 'text-[#14C698]'],
                                        ['name' => 'استضافة وخوادم', 'meta' => 'مصروف تشغيلي', 'value' => '-460₪', 'color' => 'text-rose-300'],
                                        ['name' => 'فاتورة مدفوعة', 'meta' => 'هوية بصرية', 'value' => '+1,750₪', 'color' => 'text-[#14C698]'],
                                        ['name' => 'أدوات تصميم', 'meta' => 'اشتراكات', 'value' => '-95₪', 'color' => 'text-rose-300'],
                                    ] as $transaction)
                                        <div class="flex items-center justify-between rounded-2xl bg-white/5 p-4">
                                            <div>
                                                <p class="text-sm font-black">{{ $transaction['name'] }}</p>
                                                <p class="mt-1 text-xs font-semibold text-slate-400">{{ $transaction['meta'] }}</p>
                                            </div>
                                            <span class="text-sm font-black {{ $transaction['color'] }}">{{ $transaction['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CRM And Invoices Highlight Section --}}
            <section class="bg-white py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
                    <div>
                        <span class="text-sm font-black text-[#14C698]">أكثر من تتبع مصاريف</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">دراهم منصة لإدارة العمل وليس دفتر مصروفات فقط</h2>
                        <p class="mt-5 text-lg leading-8 text-[#475569]">اربط العميل بالمشروع، وحوّل عرض السعر إلى فاتورة، وتابع المدفوعات والالتزامات من نفس المكان.</p>
                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            @foreach (['العملاء', 'الفواتير', 'عروض الأسعار', 'بوابة العميل'] as $item)
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-[#F8FAFC] p-4">
                                    <span class="grid size-9 place-items-center rounded-xl bg-[#14C698]/10 text-sm font-black text-[#0F8F70]">✓</span>
                                    <span class="font-black text-[#0F172A]">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-[2rem] border border-slate-200 bg-[#F8FAFC] p-5 shadow-xl shadow-slate-900/5">
                        <div class="rounded-2xl bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                                <div>
                                    <p class="text-sm font-black text-[#320E8E]">شركة النخبة الرقمية</p>
                                    <p class="mt-1 text-xs font-semibold text-[#475569]">عميل نشط منذ 8 أشهر</p>
                                </div>
                                <span class="rounded-full bg-[#14C698]/10 px-3 py-1 text-xs font-black text-[#0F8F70]">مدفوع</span>
                            </div>
                            <div class="mt-5 grid gap-4">
                                <div class="flex items-center justify-between rounded-2xl bg-[#F8FAFC] p-4">
                                    <span class="font-bold text-[#475569]">عرض سعر موقع SaaS</span>
                                    <span class="font-black text-[#0F172A]">9,500₪</span>
                                </div>
                                <div class="flex items-center justify-between rounded-2xl bg-[#F8FAFC] p-4">
                                    <span class="font-bold text-[#475569]">فاتورة مرحلة أولى</span>
                                    <span class="font-black text-[#14C698]">4,750₪</span>
                                </div>
                                <div class="flex items-center justify-between rounded-2xl bg-[#F8FAFC] p-4">
                                    <span class="font-bold text-[#475569]">بوابة العميل</span>
                                    <span class="font-black text-[#320E8E]">3 ملفات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Pricing Section --}}
            <section id="pricing" class="py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="text-sm font-black text-[#14C698]">الأسعار</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">خطط مرنة تناسب بداية ونمو عملك</h2>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-3">
                        @foreach ([
                            ['name' => 'مجاني', 'price' => '0₪', 'desc' => 'لبداية منظمة وتتبع أساسيات العمل.', 'featured' => false, 'features' => ['مشروعان', '30 معاملة شهرياً', 'عملاء محدودون', 'تقارير أساسية']],
                            ['name' => 'Pro', 'price' => '49₪', 'desc' => 'للمستقلين الذين يحتاجون إدارة مالية وتشغيلية كاملة.', 'featured' => true, 'features' => ['مشاريع غير محدودة', 'فواتير وعروض أسعار', 'CRM للعملاء', 'تقارير ربحية متقدمة']],
                            ['name' => 'Business', 'price' => '129₪', 'desc' => 'لفرق العمل والشركات الصغيرة ذات العمليات المتعددة.', 'featured' => false, 'features' => ['عدة مستخدمين', 'بوابة عميل', 'صلاحيات وأدوار', 'دعم أولوية']],
                        ] as $plan)
                            <article class="{{ $plan['featured'] ? 'relative border-[#320E8E] bg-[#320E8E] text-white shadow-2xl shadow-[#320E8E]/25' : 'border-slate-200 bg-white text-[#0F172A] shadow-sm' }} rounded-[2rem] border p-7">
                                @if ($plan['featured'])
                                    <div class="absolute -top-4 right-7 rounded-full bg-[#14C698] px-4 py-1.5 text-xs font-black text-[#0F172A]">الأكثر اختياراً</div>
                                @endif
                                <h3 class="text-2xl font-black">{{ $plan['name'] }}</h3>
                                <p class="{{ $plan['featured'] ? 'text-white/75' : 'text-[#475569]' }} mt-3 leading-7">{{ $plan['desc'] }}</p>
                                <div class="mt-7 flex items-end gap-2">
                                    <span class="text-4xl font-black">{{ $plan['price'] }}</span>
                                    <span class="{{ $plan['featured'] ? 'text-white/70' : 'text-[#475569]' }} mb-1 font-bold">/ شهرياً</span>
                                </div>
                                <a href="{{ $registerUrl }}" class="{{ $plan['featured'] ? 'bg-white text-[#320E8E] hover:bg-slate-100' : 'bg-[#320E8E] text-white hover:bg-[#270B70]' }} mt-8 inline-flex w-full justify-center rounded-full px-6 py-3.5 text-sm font-black transition">
                                    ابدأ الآن
                                </a>
                                <ul class="mt-8 grid gap-4">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex items-center gap-3">
                                            <span class="{{ $plan['featured'] ? 'bg-white/15 text-[#14C698]' : 'bg-[#14C698]/10 text-[#0F8F70]' }} grid size-7 place-items-center rounded-full text-xs font-black">✓</span>
                                            <span class="{{ $plan['featured'] ? 'text-white/85' : 'text-[#475569]' }} font-bold">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- FAQ Section --}}
            <section id="faq" class="bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <span class="text-sm font-black text-[#14C698]">الأسئلة الشائعة</span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#0F172A] sm:text-4xl">إجابات سريعة قبل أن تبدأ</h2>
                    </div>

                    <div class="mt-12 grid gap-4">
                        @foreach ([
                            ['q' => 'هل دراهم برنامج محاسبة؟', 'a' => 'دراهم ليس بديلاً عن المحاسب القانوني، لكنه منصة مالية تساعدك على تنظيم الدخل والمصروفات والفواتير وفهم أداء عملك اليومي.'],
                            ['q' => 'هل يناسب المستقلين؟', 'a' => 'نعم، صمم دراهم للمستقلين وأصحاب الأعمال الصغيرة الذين يحتاجون رؤية واضحة للمشاريع والعملاء والأرباح.'],
                            ['q' => 'هل يدعم الفواتير؟', 'a' => 'نعم، يمكنك إنشاء الفواتير، متابعة حالتها، وربطها بالعملاء والمشاريع.'],
                            ['q' => 'هل يدعم أكثر من عملة؟', 'a' => 'يمكن تهيئة المنصة لدعم العملات المتعددة بحسب احتياج عملك وأسواقك.'],
                            ['q' => 'هل يمكن استخدامه من الجوال؟', 'a' => 'نعم، الواجهة متجاوبة وتعمل بسلاسة على الجوال والتابلت وسطح المكتب.'],
                        ] as $faq)
                            <details class="group rounded-2xl border border-slate-200 bg-[#F8FAFC] p-6">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-black text-[#0F172A]">
                                    {{ $faq['q'] }}
                                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white text-[#320E8E] shadow-sm transition group-open:rotate-45">+</span>
                                </summary>
                                <p class="mt-4 leading-8 text-[#475569]">{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Final CTA Section --}}
            <section class="relative isolate overflow-hidden py-20 sm:py-24">
                <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#320E8E] via-[#24105F] to-[#14C698]"></div>
                <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,rgba(255,255,255,.12)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,.12)_1px,transparent_1px)] bg-[size:42px_42px]"></div>
                <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-black tracking-tight text-white sm:text-5xl">ابدأ تنظيم أموالك ومشاريعك اليوم</h2>
                    <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-white/80">جرّب دراهم مجاناً، واجعل قراراتك المالية مبنية على أرقام واضحة لا على التخمين.</p>
                    <a href="{{ $registerUrl }}" class="mt-9 inline-flex rounded-full bg-white px-8 py-4 text-base font-black text-[#320E8E] shadow-xl shadow-black/15 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        سجّل مجاناً الآن
                    </a>
                </div>
            </section>
        </main>

        {{-- Footer Section --}}
        <footer class="bg-[#0F172A] py-10 text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-[#320E8E] to-[#14C698] font-black">د</span>
                        <span class="text-xl font-black">دراهم</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-400">© {{ date('Y') }} دراهم. جميع الحقوق محفوظة.</p>
                </div>
                <div class="flex flex-wrap gap-5 text-sm font-bold text-slate-300">
                    <a href="#features" class="hover:text-white">المميزات</a>
                    <a href="#how-it-works" class="hover:text-white">كيف يعمل</a>
                    <a href="#pricing" class="hover:text-white">الأسعار</a>
                    <a href="#faq" class="hover:text-white">الأسئلة الشائعة</a>
                    <a href="{{ $loginUrl }}" class="hover:text-white">تسجيل الدخول</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
