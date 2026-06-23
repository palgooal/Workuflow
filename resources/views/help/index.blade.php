@extends('layouts.app')

@section('title', 'مركز المساعدة')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <span class="text-ink">مركز المساعدة</span>
@endsection

@section('content')
<div
    x-data="{ tab: 'start' }"
    class="flex gap-6"
>

    {{-- ===== Sidebar التبويبات ===== --}}
    <aside class="w-56 shrink-0 hidden md:block">
        <div class="dash-card p-3 sticky top-6 space-y-1">

            <p class="px-3 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">الأقسام</p>

            @php
            $tabs = [
                ['id' => 'start',        'label' => 'البداية السريعة',   'emoji' => '🚀'],
                ['id' => 'projects',     'label' => 'المشاريع',           'emoji' => '📁'],
                ['id' => 'transactions', 'label' => 'المعاملات',          'emoji' => '💸'],
                ['id' => 'clients',      'label' => 'العملاء',            'emoji' => '👥'],
                ['id' => 'team',         'label' => 'الفريق',             'emoji' => '🧑‍💼'],
                ['id' => 'debts',        'label' => 'الديون',             'emoji' => '💳'],
                ['id' => 'budget',       'label' => 'الميزانية',          'emoji' => '📊'],
                ['id' => 'recurring',    'label' => 'الالتزامات الثابتة', 'emoji' => '🔁'],
                ['id' => 'reports',      'label' => 'التقارير',           'emoji' => '📈'],
                ['id' => 'crm',          'label' => 'الشرائح وصحة العملاء','emoji' => '🎯'],
                ['id' => 'tips',         'label' => 'نصائح وحيل',         'emoji' => '💡'],
            ];
            @endphp

            @foreach($tabs as $t)
            <button
                @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-slate-600 hover:bg-slate-50'"
                class="w-full text-right flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition"
            >
                <span class="text-base">{{ $t['emoji'] }}</span>
                {{ $t['label'] }}
            </button>
            @endforeach

        </div>
    </aside>

    {{-- ===== المحتوى ===== --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- تبويبات الموبايل --}}
        <div class="md:hidden dash-card p-3">
            <select x-model="tab" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                @foreach($tabs as $t)
                    <option value="{{ $t['id'] }}">{{ $t['emoji'] }} {{ $t['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- ==================== البداية السريعة ==================== --}}
        <div x-show="tab === 'start'" x-cloak>
            <x-help-section emoji="🚀" title="البداية السريعة — أول 5 دقائق">

                <x-help-step number="1" title="أنشئ مشروعك الأول">
                    اذهب إلى <strong>المشاريع</strong> من القائمة الجانبية ← اضغط <strong>"مشروع جديد"</strong>.
                    أدخل اسم المشروع، نوعه (تجاري أو شخصي)، العملة، والعميل إن وُجد.
                    إذا كان لديك عقد مع العميل، أدخل <strong>قيمة العقد</strong> لتتبع نسبة ما استلمته.
                </x-help-step>

                <x-help-step number="2" title="أضف الخدمات التي تقدمها">
                    داخل نموذج المشروع، في قسم الخدمات اختر ما تقدمه (تصميم، سيو، موشن...).
                    حدد مبلغ كل خدمة ونوعه (دخل أو مصروف).
                    يمكنك تعيين عضو من فريقك على كل خدمة وتحديد تكلفته.
                </x-help-step>

                <x-help-step number="3" title="سجّل أول معاملة">
                    افتح المشروع ← اضغط <strong>"إضافة معاملة"</strong>.
                    اختر النوع (دخل أو مصروف)، أدخل المبلغ والوصف والتاريخ.
                    لحظة التسجيل، ستُحدَّث جميع الإحصاءات تلقائياً.
                </x-help-step>

                <x-help-step number="4" title="راقب لوحة التحكم">
                    لوحة التحكم تُلخّص كل شيء: دخلك الشهري، مصروفاتك، أرباحك، ومشاريعك النشطة —
                    كل شيء في مكان واحد.
                </x-help-step>

                <div class="mt-6 p-4 bg-brand-50 border border-brand-100 rounded-xl">
                    <p class="text-sm font-semibold text-brand-700 mb-1">💡 الدورة الطبيعية للاستخدام:</p>
                    <p class="text-sm text-brand-600 leading-relaxed">
                        مشروع جديد ← إضافة خدمات وعميل ← تسجيل دفعات (دخل) ← تسجيل مصروفات ← مراقبة الأرباح من صفحة المشروع.
                    </p>
                </div>

            </x-help-section>
        </div>

        {{-- ==================== المشاريع ==================== --}}
        <div x-show="tab === 'projects'" x-cloak>
            <x-help-section emoji="📁" title="المشاريع">

                <x-help-card title="ما هو المشروع؟">
                    المشروع هو <strong>وحدة التتبع المالي الأساسية</strong>. كل دخل أو مصروف تسجله يُربط بمشروع،
                    مما يعطيك صورة كاملة عن ربحية كل مشروع على حدة.
                </x-help-card>

                <x-help-card title="أنواع المشاريع">
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="text-xl">💼</span>
                            <div>
                                <p class="font-medium text-slate-900 text-sm">تجاري</p>
                                <p class="text-slate-500 text-xs">مشاريع العملاء والأعمال — يظهر في تقارير الأعمال</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">🏠</span>
                            <div>
                                <p class="font-medium text-slate-900 text-sm">شخصي</p>
                                <p class="text-slate-500 text-xs">المصاريف الشخصية والمنزلية — مفصول عن مالية العمل</p>
                            </div>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="الحقول المالية">
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">📄 قيمة العقد</p>
                            <p class="text-slate-500 text-xs mt-0.5">المبلغ المتفق عليه مع العميل. يظهر شريط تقدم يوضح كم استلمت منه حتى الآن (أزرق → أخضر عند الاكتمال).</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">💰 ميزانية التكاليف</p>
                            <p class="text-slate-500 text-xs mt-0.5">الحد الأقصى للمصروفات الذي خططت له. يتحول الشريط أحمر تلقائياً عند التجاوز.</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="المقاييس المالية في صفحة المشروع">
                    <div class="grid grid-cols-2 gap-3">
                        @php $metrics = [
                            ['إجمالي الدخل', 'مجموع كل معاملات الدخل', 'text-green-700', 'bg-green-50'],
                            ['إجمالي المصروفات', 'مجموع كل معاملات المصروف', 'text-red-700', 'bg-red-50'],
                            ['صافي الربح', 'الدخل − المصروفات. أخضر = ربح، أحمر = خسارة', 'text-blue-700', 'bg-blue-50'],
                            ['هامش الربح %', '30%+ ممتاز · 10-30% جيد · أقل من 0% = خسارة', 'text-purple-700', 'bg-purple-50'],
                        ] @endphp
                        @foreach($metrics as [$name, $desc, $text, $bg])
                        <div class="p-3 {{ $bg }} rounded-xl">
                            <p class="font-semibold text-sm {{ $text }}">{{ $name }}</p>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </x-help-card>

                <x-help-tip>
                    عند تجاوز مصروفات المشروع لميزانيته، ستظهر تنبيه أحمر يوضح مقدار التجاوز مباشرةً في صفحة المشروع.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== المعاملات ==================== --}}
        <div x-show="tab === 'transactions'" x-cloak>
            <x-help-section emoji="💸" title="المعاملات">

                <x-help-card title="نوعا المعاملة">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-2.5 bg-green-50 rounded-xl">
                            <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center text-green-700 font-bold">↑</div>
                            <div>
                                <p class="font-semibold text-green-900 text-sm">دخل</p>
                                <p class="text-green-700 text-xs">مدفوعات من العملاء، دفعات مقدمة، إيرادات...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2.5 bg-red-50 rounded-xl">
                            <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center text-red-700 font-bold">↓</div>
                            <div>
                                <p class="font-semibold text-red-900 text-sm">مصروف</p>
                                <p class="text-red-700 text-xs">أدوات، خدمات، رواتب، مستلزمات...</p>
                            </div>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="حقل جهة الدفع (Payee)">
                    يظهر فقط عند اختيار <strong>مصروف</strong>. استخدمه لتسجيل من دفعت له:
                    اسم المورد، الشركة، الفريلانسر... يساعدك لاحقاً في تتبع أين تذهب أموالك.
                </x-help-card>

                <x-help-card title="ربط المعاملة بمشروع وفئة">
                    <p class="text-sm text-slate-600">كل معاملة يمكن ربطها بـ:</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-brand mt-0.5">•</span> <strong>مشروع</strong> — لتظهر في تقرير المشروع وتؤثر على أرباحه</li>
                        <li class="flex items-start gap-2"><span class="text-brand mt-0.5">•</span> <strong>فئة</strong> — لتصنيفها في التقارير (إيجار، أدوات، تسويق...)</li>
                    </ul>
                </x-help-card>

                <x-help-tip>
                    يمكنك تصفية المعاملات بالمشروع أو النوع أو التاريخ من صفحة المعاملات الرئيسية.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== العملاء ==================== --}}
        <div x-show="tab === 'clients'" x-cloak>
            <x-help-section emoji="👥" title="العملاء">

                <x-help-card title="ما فائدة إضافة العملاء؟">
                    ربط العميل بالمشروع يمنحك:
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> تتبع المشاريع لكل عميل بشكل منفصل</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> التواصل المباشر عبر واتساب من بطاقة العميل</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> سجل مرجعي بجميع بيانات عملائك في مكان واحد</li>
                    </ul>
                </x-help-card>

                <x-help-card title="بطاقة العميل تحتوي على">
                    الاسم، الشركة، رقم الهاتف، البريد الإلكتروني، الموقع، والملاحظات.
                    من البطاقة يمكنك مباشرةً فتح محادثة واتساب مع العميل بنقرة واحدة.
                </x-help-card>

                <x-help-tip>
                    تأكد من إدخال رقم الهاتف بالصيغة الدولية (مثال: 966501234567) لكي يعمل زر الواتساب بشكل صحيح.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== الفريق ==================== --}}
        <div x-show="tab === 'team'" x-cloak>
            <x-help-section emoji="🧑‍💼" title="الفريق">

                <x-help-card title="نوعا أعضاء الفريق">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">موظف</span>
                            <p class="text-sm text-slate-600">مرتبط بالشركة بشكل دائم، راتب ثابت</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">فريلانسر</span>
                            <p class="text-sm text-slate-600">مستقل يُدفع له بالمشروع أو المهمة</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="ربط عضو الفريق بخدمة في المشروع">
                    عند إنشاء أو تعديل مشروع، داخل كل خدمة يمكنك:
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> تعيين عضو الفريق المسؤول عن هذه الخدمة</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> تحديد تكلفته (team cost) على هذه الخدمة</li>
                    </ul>
                </x-help-card>

                <x-help-card title='زر "تسجيل دفعة"'>
                    في صفحة تفاصيل المشروع، قسم <strong>"الفريق المعين على المشروع"</strong>:
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> اضغط "تسجيل دفعة" ← تُنشأ معاملة مصروف تلقائياً باسم العضو</li>
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> تتحول حالة الدفع من "⏳ لم يُدفع" إلى "✅ تم الدفع"</li>
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> يُحسب المبلغ تلقائياً في مصروفات المشروع</li>
                    </ul>
                </x-help-card>

                <x-help-tip>
                    أدخل "المعدل الافتراضي" (default rate) لكل عضو، سيُقترح تلقائياً عند تعيينه على خدمة.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== الديون ==================== --}}
        <div x-show="tab === 'debts'" x-cloak>
            <x-help-section emoji="💳" title="الديون والالتزامات">

                <x-help-card title="متى تستخدم الديون؟">
                    <div class="space-y-2">
                        <div class="p-2.5 bg-orange-50 rounded-xl">
                            <p class="text-sm font-semibold text-orange-800">دين عليك (لازم تدفعه)</p>
                            <p class="text-xs text-orange-700 mt-0.5">مثال: اشتريت معدات بالتقسيط، أو اقترضت من شخص</p>
                        </div>
                        <div class="p-2.5 bg-blue-50 rounded-xl">
                            <p class="text-sm font-semibold text-blue-800">دين لك (لازم تستلمه)</p>
                            <p class="text-xs text-blue-700 mt-0.5">مثال: العميل لم يدفع بعد، أقرضت شخصاً</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="كيف تتعامل مع الديون">
                    عند تسجيل دين، يمكنك:
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> <strong>تسجيل دفعة جزئية</strong> — تُحدَّث نسبة السداد تلقائياً</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> <strong>تمييز كمدفوع كلياً</strong> — يُغلق الدين ويُزال من القائمة النشطة</li>
                    </ul>
                </x-help-card>

                <x-help-tip>
                    ديونك تؤثر على لوحة التحكم تحت مؤشر "إجمالي الالتزامات" — راقبه لتعرف وضعك المالي الحقيقي.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== الميزانية ==================== --}}
        <div x-show="tab === 'budget'" x-cloak>
            <x-help-section emoji="📊" title="الميزانية">

                <x-help-card title="الفرق بين الميزانية وميزانية التكاليف في المشروع">
                    <div class="space-y-2">
                        <div class="p-2.5 bg-brand-50 rounded-xl">
                            <p class="text-sm font-semibold text-brand-700">📊 الميزانية (Budget Module)</p>
                            <p class="text-xs text-brand-600 mt-0.5">ميزانية شهرية أو سنوية عامة لفئة معينة (مثال: 2000 ريال للتسويق شهرياً)</p>
                        </div>
                        <div class="p-2.5 bg-orange-50 rounded-xl">
                            <p class="text-sm font-semibold text-orange-800">💰 ميزانية التكاليف في المشروع</p>
                            <p class="text-xs text-orange-700 mt-0.5">سقف مصروفات خاص بمشروع بعينه (مثال: مصروفات مشروع X لا تتجاوز 1500)</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="كيف تعمل الميزانية؟">
                    تُنشئ ميزانية لفئة معينة (مثل: إيجار، أدوات، تسويق) بمبلغ محدد وفترة زمنية.
                    التطبيق يتبع مصروفاتك في تلك الفئة ويُنبهك عند الاقتراب من الحد.
                </x-help-card>

                <x-help-tip>
                    استخدم الفئات باتساق عند تسجيل المعاملات حتى تعمل الميزانيات بدقة.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== الالتزامات الثابتة ==================== --}}
        <div x-show="tab === 'recurring'" x-cloak>
            <x-help-section emoji="🔁" title="الالتزامات الثابتة (Recurring)">

                <x-help-card title="ما هي الالتزامات الثابتة؟">
                    مصروفات أو دخل يتكرر بانتظام:
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-red-400">•</span> إيجار المكتب، اشتراك Adobe، اشتراك الاستضافة...</li>
                        <li class="flex items-start gap-2"><span class="text-green-400">•</span> راتب ثابت، دخل شهري متكرر من عميل...</li>
                    </ul>
                </x-help-card>

                <x-help-card title="دورات التكرار المتاحة">
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['يومي','أسبوعي','شهري','كل شهرين','ربع سنوي','نصف سنوي','سنوي'] as $period)
                        <span class="text-xs text-center px-2 py-1.5 bg-slate-100 rounded-lg text-slate-700">{{ $period }}</span>
                        @endforeach
                    </div>
                </x-help-card>

                <x-help-card title='زر "نفّذ الآن"'>
                    إذا جاء موعد دفع أحد الالتزامات، اضغط <strong>"نفّذ الآن"</strong> ← ستُنشأ معاملة فعلية تلقائياً وتُضاف لقائمة معاملاتك.
                </x-help-card>

                <x-help-tip>
                    يمكنك إيقاف أي التزام مؤقتاً بدون حذفه، ثم تفعيله لاحقاً عند الحاجة.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== التقارير ==================== --}}
        <div x-show="tab === 'reports'" x-cloak>
            <x-help-section emoji="📈" title="التقارير">

                <x-help-card title="ما الذي تعرضه التقارير؟">
                    <ul class="space-y-1.5 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> الدخل والمصروف الشهري مع مقارنة الأشهر</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> أكثر الفئات إنفاقاً</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> ربحية المشاريع مقارنةً ببعضها</li>
                        <li class="flex items-start gap-2"><span class="text-brand">•</span> توزيع الدخل حسب المشاريع أو الفئات</li>
                    </ul>
                </x-help-card>

                <x-help-card title="تصدير التقارير">
                    <div class="flex gap-3">
                        <div class="flex-1 p-3 bg-red-50 rounded-xl text-center">
                            <p class="text-2xl">📄</p>
                            <p class="text-sm font-semibold text-red-800 mt-1">PDF</p>
                            <p class="text-xs text-red-600">مناسب للطباعة أو الإرسال للعميل</p>
                        </div>
                        <div class="flex-1 p-3 bg-green-50 rounded-xl text-center">
                            <p class="text-2xl">📊</p>
                            <p class="text-sm font-semibold text-green-800 mt-1">Excel</p>
                            <p class="text-xs text-green-600">مناسب للتحليل أو المحاسب</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-tip>
                    يمكنك فلترة التقارير حسب الفترة الزمنية: هذا الشهر، الشهر الماضي، 3 أشهر، أو نطاق مخصص.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== الشرائح وصحة العملاء ==================== --}}
        <div x-show="tab === 'crm'" x-cloak>
            <x-help-section emoji="🎯" title="الشرائح وصحة العملاء — اعرف كل عميل بعمق">

                {{-- ما هو مؤشر الصحة --}}
                <x-help-card title="ما هو مؤشر صحة العميل؟">
                    <p class="text-slate-600 leading-relaxed">
                        مؤشر الصحة هو <strong>درجة من 0 إلى 100</strong> تُخبرك تلقائياً بمدى جودة علاقتك مع كل عميل —
                        بناءً على سلوكه الفعلي معك: هل يدفع في الوقت؟ هل يتعاون معك باستمرار؟ هل قيمته لعملك تنمو أم تتراجع؟
                    </p>
                    <p class="mt-3 text-slate-500 text-xs">
                        بدلاً من الاعتماد على الانطباع الشخصي، تحصل على رقم موضوعي مبني على بياناتك الحقيقية.
                    </p>
                </x-help-card>

                {{-- درجات التقييم --}}
                <x-help-card title="درجات التقييم — ماذا تعني الأرقام؟">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl font-bold text-emerald-700">80–100</span>
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">ممتاز</span>
                            </div>
                            <p class="text-xs text-emerald-700 leading-relaxed">عميل وفيّ، يدفع بانتظام، تواصله مستمر. احرص على الاهتمام به وتعزيز العلاقة.</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl font-bold text-blue-700">60–79</span>
                                <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">جيد</span>
                            </div>
                            <p class="text-xs text-blue-700 leading-relaxed">علاقة صحية بشكل عام، لكن ثمة جوانب تستحق التحسين. تابع من حين لآخر.</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl font-bold text-amber-700">40–59</span>
                                <span class="text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">متوسط</span>
                            </div>
                            <p class="text-xs text-amber-700 leading-relaxed">العلاقة متذبذبة — تأخر في الدفع أو تواصل متقطع. يحتاج اهتماماً استباقياً.</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl font-bold text-red-700">0–39</span>
                                <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">ضعيف</span>
                            </div>
                            <p class="text-xs text-red-700 leading-relaxed">علاقة متدهورة. انقطع التواصل أو تأخرت الدفعات بشكل ملحوظ — تحرك الآن.</p>
                        </div>
                    </div>
                </x-help-card>

                {{-- كيف تُحسَب الدرجة --}}
                <x-help-card title="كيف تُحسَب الدرجة؟ — 5 عوامل ذكية">
                    <p class="text-slate-500 text-xs mb-3">الخوارزمية تحلل سلوك العميل عبر 5 عوامل، لكل منها وزن مختلف:</p>
                    <div class="space-y-2.5">
                        @php $factors = [
                            ['35%', 'bg-brand', 'معدل الدفع', 'نسبة ما دفعه الفعلي من إجمالي الفواتير. هذا هو العامل الأهم — العميل الذي يدفع دائماً يستحق درجة عالية.'],
                            ['25%', 'bg-violet-500', 'تكرار التعامل', 'عدد المعاملات والمشاريع خلال آخر 12 شهراً. العميل المتكرر أكثر قيمة من العميل المتقطع.'],
                            ['20%', 'bg-blue-500', 'قيمة الإيراد', 'حجم ما دفعه نسبةً لباقي عملائك. العميل الأعلى قيمةً يأخذ درجة أعلى.'],
                            ['10%', 'bg-sky-500', 'انتظام التواصل', 'متى كان آخر تواصل معك؟ العميل الذي تواصل منذ أسبوع أفضل من الذي لم تسمع عنه 6 أشهر.'],
                            ['10%', 'bg-cyan-500', 'معدل الاستجابة', 'نسبة المتابعات التي اكتملت معه. متابعاتك معه تعكس مدى استجابته واهتمامه.'],
                        ] @endphp
                        @foreach($factors as [$weight, $color, $name, $desc])
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-10 h-6 {{ $color }} text-white text-xs font-bold rounded-lg shrink-0 mt-0.5">{{ $weight }}</div>
                            <div>
                                <p class="font-semibold text-slate-800 text-xs">{{ $name }}</p>
                                <p class="text-slate-500 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 p-3 bg-brand-50 rounded-xl border border-brand-100">
                        <p class="text-xs text-brand-600 leading-relaxed">
                            <strong>تحيّز الحداثة:</strong> الخوارزمية تُعطي الـ 3 أشهر الأخيرة وزناً أكبر (70%) مقارنةً بالتاريخ الكامل (30%).
                            يعني عميل تحسّن مؤخراً → ترتفع درجته سريعاً. وعميل تراجع مؤخراً → تنخفض درجته حتى لو كان ممتازاً قديماً.
                        </p>
                    </div>
                </x-help-card>

                {{-- ما هي الشرائح --}}
                <x-help-card title="ما هي الشرائح (Segments)؟">
                    <p class="text-slate-600 leading-relaxed">
                        الشريحة هي <strong>فلتر ذكي محفوظ</strong> يُجمّع عملاءك في مجموعات بشروط تحددها أنت.
                        بدلاً من البحث يدوياً في كل مرة، تضغط على الشريحة وتحصل على القائمة فوراً.
                    </p>
                    <p class="mt-3 text-slate-500 text-xs leading-relaxed">
                        مثال: "عملاء نشطون، صحتهم أقل من 40، ولديهم متابعة متأخرة" — هذا الفلتر يمكن حفظه كشريحة
                        وفتحه بنقرة واحدة في أي وقت.
                    </p>
                </x-help-card>

                {{-- أمثلة شرائح --}}
                <x-help-card title="أمثلة على شرائح مفيدة">
                    <div class="space-y-2">
                        @php $examples = [
                            ['🔴', 'العملاء في خطر', 'صحة أقل من 40 + آخر تواصل قبل أكثر من 60 يوماً', 'bg-red-50 border-red-100'],
                            ['⏰', 'متابعات متأخرة', 'لديهم متابعة معلقة تجاوزت موعدها', 'bg-amber-50 border-amber-100'],
                            ['💎', 'العملاء المميزون', 'إجمالي إيراد أعلى من 10,000 + صحة فوق 80', 'bg-emerald-50 border-emerald-100'],
                            ['🔄', 'العملاء الخاملون', 'لا تواصل منذ 90 يوماً + لا مشاريع نشطة', 'bg-blue-50 border-blue-100'],
                            ['💳', 'متأخرو الدفع', 'معدل الدفع أقل من 50% + لديهم فواتير', 'bg-orange-50 border-orange-100'],
                        ] @endphp
                        @foreach($examples as [$emoji, $name, $condition, $style])
                        <div class="flex items-start gap-3 p-3 rounded-xl border {{ $style }}">
                            <span class="text-xl shrink-0">{{ $emoji }}</span>
                            <div>
                                <p class="font-semibold text-slate-800 text-xs">{{ $name }}</p>
                                <p class="text-slate-500 text-xs mt-0.5">{{ $condition }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-help-card>

                {{-- كيفية الاستخدام --}}
                <x-help-card title="كيف تستخدم الشرائح وصحة العملاء؟">
                    <div class="space-y-4">

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand text-white flex items-center justify-center text-xs font-bold shrink-0">1</div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">اذهب إلى صفحة الشرائح</p>
                                <p class="text-slate-500 text-xs mt-0.5">من القائمة الجانبية: <strong>العملاء ← الشرائح</strong>، ثم اختر تبويب <strong>"صحة العملاء"</strong> لترى نظرة عامة فورية.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand text-white flex items-center justify-center text-xs font-bold shrink-0">2</div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">احسب مؤشرات الصحة</p>
                                <p class="text-slate-500 text-xs mt-0.5">إذا رأيت تنبيهاً بوجود عملاء بدون درجة، اضغط <strong>"احسب المؤشرات الآن"</strong> — سيتم الحساب تلقائياً دون الحاجة لأي إجراء تقني.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand text-white flex items-center justify-center text-xs font-bold shrink-0">3</div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">أنشئ شريحة مخصصة</p>
                                <p class="text-slate-500 text-xs mt-0.5">في تبويب <strong>"الشرائح"</strong>، اضغط <strong>"شريحة جديدة"</strong>، اختر الحقل والشرط والقيمة، ثم احفظها باسم. يمكنك تثبيتها لتظهر في الأعلى دائماً.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand text-white flex items-center justify-center text-xs font-bold shrink-0">4</div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">تصرّف بناءً على النتائج</p>
                                <p class="text-slate-500 text-xs mt-0.5">من نتائج الشريحة، انقر على أي عميل لتفتح بطاقته وتتواصل معه مباشرةً عبر واتساب أو تضيف له متابعة.</p>
                            </div>
                        </div>

                    </div>
                </x-help-card>

                {{-- الفلاتر المتاحة --}}
                <x-help-card title="الفلاتر المتاحة عند بناء الشريحة">
                    <div class="grid grid-cols-2 gap-2">
                        @php $fields = [
                            ['الحالة', 'نشط، موقوف، مُغلق...'],
                            ['مصدر العميل', 'إحالة، موقع، تواصل اجتماعي...'],
                            ['مؤشر الصحة', 'أعلى من / أقل من / بين'],
                            ['إجمالي الإيراد', 'مقارنة بمبلغ محدد'],
                            ['تاريخ التواصل', 'آخر 30 يوم، 90 يوم، سنة...'],
                            ['الوسوم', 'يحمل / لا يحمل / يحمل الكل'],
                            ['متابعة متأخرة', 'نعم أو لا'],
                            ['عدد الفواتير', 'أكثر من / أقل من'],
                        ] @endphp
                        @foreach($fields as [$name, $desc])
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="font-semibold text-slate-800 text-xs">{{ $name }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </x-help-card>

                {{-- tip --}}
                <x-help-tip>
                    تُحسَب درجات الصحة تلقائياً كل يوم في الساعة 2:00 صباحاً. لكن إذا أضفت بيانات جديدة وأردت رؤية النتائج فوراً، اضغط زر "احسب المؤشرات الآن" في صفحة الشرائح.
                </x-help-tip>

            </x-help-section>
        </div>

        {{-- ==================== النصائح والحيل ==================== --}}
        <div x-show="tab === 'tips'" x-cloak>
            <x-help-section emoji="💡" title="نصائح وحيل">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    @php $tips = [
                        ['🎨', 'استخدم الألوان', 'أعطِ لكل مشروع لون مميز حتى تتعرف عليه بسرعة في القوائم.'],
                        ['🏷️', 'صنّف معاملاتك', 'حدد فئة لكل معاملة لكي تكون التقارير دقيقة ومفيدة.'],
                        ['📅', 'سجّل يومياً', 'تسجيل المعاملات فور حدوثها أفضل من تأجيلها — لن تنساها.'],
                        ['💬', 'استخدم الوصف', 'اكتب وصفاً واضحاً لكل معاملة حتى تعرف سببها لاحقاً.'],
                        ['🔁', 'أتمتة المتكرر', 'أي مصروف يتكرر شهرياً أضفه كالتزام ثابت لتوفير الوقت.'],
                        ['📊', 'راقع الميزانية أسبوعياً', 'نظرة سريعة أسبوعياً على مؤشرات الميزانية تمنع المفاجآت.'],
                        ['👥', 'أضف كل عملائك', 'حتى لو لم يكن لديهم مشاريع الآن — يسهّل التواصل مستقبلاً.'],
                        ['📱', 'رقم الواتساب الدولي', 'أدخل أرقام الهاتف بالصيغة الدولية (966xxxxxxxx) لكي يعمل الواتساب.'],
                    ] @endphp

                    @foreach($tips as [$emoji, $title, $desc])
                    <div class="dash-card p-4 hover:border-brand/30 transition">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl shrink-0">{{ $emoji }}</span>
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">{{ $title }}</p>
                                <p class="text-slate-500 text-xs mt-1 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                {{-- سؤال؟ --}}
                <div class="mt-4 p-5 bg-gradient-to-l from-brand-50 to-purple-50 rounded-2xl border border-brand-100">
                    <p class="font-semibold text-slate-900 mb-1">لديك سؤال لم تجد إجابته هنا؟</p>
                    <p class="text-sm text-slate-600">تواصل معنا عبر البريد الإلكتروني أو واتساب وسنساعدك فوراً.</p>
                </div>

            </x-help-section>
        </div>

    </div>

</div>
@endsection
