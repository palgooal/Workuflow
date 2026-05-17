@extends('layouts.app')

@section('title', 'مركز المساعدة')

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">مركز المساعدة</span>
@endsection

@section('content')
<div
    x-data="{ tab: 'start' }"
    class="flex gap-6"
>

    {{-- ===== Sidebar التبويبات ===== --}}
    <aside class="w-56 shrink-0 hidden md:block">
        <div class="bg-white rounded-2xl border border-gray-100 p-3 sticky top-6 space-y-1">

            <p class="px-3 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">الأقسام</p>

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
                ['id' => 'tips',         'label' => 'نصائح وحيل',         'emoji' => '💡'],
            ];
            @endphp

            @foreach($tabs as $t)
            <button
                @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
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
        <div class="md:hidden bg-white rounded-2xl border border-gray-100 p-3">
            <select x-model="tab" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
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

                <div class="mt-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <p class="text-sm font-semibold text-indigo-800 mb-1">💡 الدورة الطبيعية للاستخدام:</p>
                    <p class="text-sm text-indigo-700 leading-relaxed">
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
                                <p class="font-medium text-gray-900 text-sm">تجاري</p>
                                <p class="text-gray-500 text-xs">مشاريع العملاء والأعمال — يظهر في تقارير الأعمال</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">🏠</span>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">شخصي</p>
                                <p class="text-gray-500 text-xs">المصاريف الشخصية والمنزلية — مفصول عن مالية العمل</p>
                            </div>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="الحقول المالية">
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">📄 قيمة العقد</p>
                            <p class="text-gray-500 text-xs mt-0.5">المبلغ المتفق عليه مع العميل. يظهر شريط تقدم يوضح كم استلمت منه حتى الآن (أزرق → أخضر عند الاكتمال).</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">💰 ميزانية التكاليف</p>
                            <p class="text-gray-500 text-xs mt-0.5">الحد الأقصى للمصروفات الذي خططت له. يتحول الشريط أحمر تلقائياً عند التجاوز.</p>
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
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $desc }}</p>
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
                    <p class="text-sm text-gray-600">كل معاملة يمكن ربطها بـ:</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">•</span> <strong>مشروع</strong> — لتظهر في تقرير المشروع وتؤثر على أرباحه</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">•</span> <strong>فئة</strong> — لتصنيفها في التقارير (إيجار، أدوات، تسويق...)</li>
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
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تتبع المشاريع لكل عميل بشكل منفصل</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> التواصل المباشر عبر واتساب من بطاقة العميل</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> سجل مرجعي بجميع بيانات عملائك في مكان واحد</li>
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
                            <p class="text-sm text-gray-600">مرتبط بالشركة بشكل دائم، راتب ثابت</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">فريلانسر</span>
                            <p class="text-sm text-gray-600">مستقل يُدفع له بالمشروع أو المهمة</p>
                        </div>
                    </div>
                </x-help-card>

                <x-help-card title="ربط عضو الفريق بخدمة في المشروع">
                    عند إنشاء أو تعديل مشروع، داخل كل خدمة يمكنك:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تعيين عضو الفريق المسؤول عن هذه الخدمة</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تحديد تكلفته (team cost) على هذه الخدمة</li>
                    </ul>
                </x-help-card>

                <x-help-card title='زر "تسجيل دفعة"'>
                    في صفحة تفاصيل المشروع، قسم <strong>"الفريق المعين على المشروع"</strong>:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
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
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> <strong>تسجيل دفعة جزئية</strong> — تُحدَّث نسبة السداد تلقائياً</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> <strong>تمييز كمدفوع كلياً</strong> — يُغلق الدين ويُزال من القائمة النشطة</li>
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
                        <div class="p-2.5 bg-indigo-50 rounded-xl">
                            <p class="text-sm font-semibold text-indigo-800">📊 الميزانية (Budget Module)</p>
                            <p class="text-xs text-indigo-700 mt-0.5">ميزانية شهرية أو سنوية عامة لفئة معينة (مثال: 2000 ريال للتسويق شهرياً)</p>
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
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-red-400">•</span> إيجار المكتب، اشتراك Adobe، اشتراك الاستضافة...</li>
                        <li class="flex items-start gap-2"><span class="text-green-400">•</span> راتب ثابت، دخل شهري متكرر من عميل...</li>
                    </ul>
                </x-help-card>

                <x-help-card title="دورات التكرار المتاحة">
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['يومي','أسبوعي','شهري','كل شهرين','ربع سنوي','نصف سنوي','سنوي'] as $period)
                        <span class="text-xs text-center px-2 py-1.5 bg-gray-100 rounded-lg text-gray-700">{{ $period }}</span>
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
                    <ul class="space-y-1.5 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> الدخل والمصروف الشهري مع مقارنة الأشهر</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> أكثر الفئات إنفاقاً</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> ربحية المشاريع مقارنةً ببعضها</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> توزيع الدخل حسب المشاريع أو الفئات</li>
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
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 hover:border-indigo-200 transition">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl shrink-0">{{ $emoji }}</span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $title }}</p>
                                <p class="text-gray-500 text-xs mt-1 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                {{-- سؤال؟ --}}
                <div class="mt-4 p-5 bg-gradient-to-l from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
                    <p class="font-semibold text-gray-900 mb-1">لديك سؤال لم تجد إجابته هنا؟</p>
                    <p class="text-sm text-gray-600">تواصل معنا عبر البريد الإلكتروني أو واتساب وسنساعدك فوراً.</p>
                </div>

            </x-help-section>
        </div>

    </div>

</div>
@endsection
