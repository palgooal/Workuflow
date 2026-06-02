@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{ tab: window.location.hash.replace('#','') || 'profile' }">

    {{-- Header + Tabs --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">الإعدادات</h1>
        <p class="mt-0.5 text-sm text-gray-500">إدارة حسابك وتفضيلاتك</p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
        @foreach([
            ['id' => 'profile',     'label' => '👤 الملف الشخصي'],
            ['id' => 'invoice',     'label' => '🧾 قالب الفاتورة'],
            ['id' => 'security',    'label' => '🔒 الأمان'],
            ['id' => 'preferences', 'label' => '⚙️ التفضيلات'],
            ['id' => 'plan',        'label' => '💼 الخطة'],
        ] as $t)
        <button @click="tab = '{{ $t['id'] }}'; window.location.hash = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}'
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 px-3 text-xs sm:text-sm font-medium rounded-lg transition">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ==================== Profile Tab ==================== --}}
    <div x-show="tab === 'profile'" id="profile">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <span class="text-xl">👤</span> بيانات الملف الشخصي
            </h2>

            <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                @csrf @method('PATCH')

                {{-- Avatar --}}
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-700 font-bold text-2xl">
                            {{ mb_substr($user->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                        <p class="text-xs text-indigo-500 mt-0.5">خطة {{ $user->currentPlan()->label() }}</p>
                    </div>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                  @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                  @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                   text-sm font-medium rounded-xl transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== Security Tab ==================== --}}
    <div x-show="tab === 'security'" id="security" style="display:none">
        <div class="space-y-5">

            {{-- Change Password --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="text-xl">🔑</span> تغيير كلمة المرور
                </h2>

                <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                      @error('current_password') border-red-400 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                      @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">8 أحرف على الأقل، تتضمن أرقاماً وحروفاً</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                       text-sm font-medium rounded-xl transition">
                            تغيير كلمة المرور
                        </button>
                    </div>
                </form>
            </div>

            {{-- Delete Account --}}
            <div class="bg-white rounded-2xl border border-red-100 p-6"
                 x-data="{ confirmDelete: false }">
                <h2 class="text-base font-semibold text-red-700 mb-2 flex items-center gap-2">
                    <span class="text-xl">⚠️</span> حذف الحساب
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    سيتم حذف جميع بياناتك بشكل نهائي ولا يمكن التراجع عن هذا الإجراء.
                </p>

                <button @click="confirmDelete = true" x-show="!confirmDelete"
                        class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-700
                               text-sm font-medium rounded-xl border border-red-200 transition">
                    حذف الحساب نهائياً
                </button>

                <div x-show="confirmDelete" x-transition class="space-y-3">
                    <p class="text-sm font-medium text-red-700">
                        أدخل كلمة المرور للتأكيد:
                    </p>
                    <form method="POST" action="{{ route('settings.delete-account') }}" class="flex gap-3">
                        @csrf @method('DELETE')
                        <input type="password" name="password" required
                               placeholder="كلمة المرور"
                               class="flex-1 px-4 py-2 rounded-xl border border-red-200
                                      focus:outline-none focus:ring-2 focus:ring-red-400 text-sm">
                        <button type="submit"
                                class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white
                                       text-sm font-medium rounded-xl transition">
                            تأكيد الحذف
                        </button>
                        <button type="button" @click="confirmDelete = false"
                                class="px-4 py-2 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition">
                            إلغاء
                        </button>
                    </form>
                    @if($errors->accountDeletion->any())
                        <p class="text-xs text-red-600">{{ $errors->accountDeletion->first('password') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ==================== Preferences Tab ==================== --}}
    <div x-show="tab === 'preferences'" id="preferences" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <span class="text-xl">⚙️</span> التفضيلات
            </h2>

            <form method="POST" action="{{ route('settings.preferences') }}" class="space-y-5">
                @csrf @method('PATCH')

                {{-- Currency --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">العملة الافتراضية</label>
                    <select name="currency"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" {{ $user->currency === $code ? 'selected' : '' }}>
                                {{ $code }} — {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">تُستخدم كعملة افتراضية في المعاملات الجديدة</p>
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">المنطقة الزمنية</label>
                    <select name="timezone"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        @foreach($timezones as $tz => $label)
                            <option value="{{ $tz }}" {{ $user->timezone === $tz ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                   text-sm font-medium rounded-xl transition">
                        حفظ التفضيلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== Plan Tab ==================== --}}
    <div x-show="tab === 'plan'" id="plan" style="display:none">
        <div class="space-y-4">

            {{-- Current Plan --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="text-xl">💼</span> خطتك الحالية
                </h2>

                <div class="flex items-center gap-4 p-4 rounded-xl
                    {{ $user->currentPlan()->value === 'free' ? 'bg-gray-50 border border-gray-200' : 'bg-indigo-50 border border-indigo-200' }}">
                    <div class="text-3xl">
                        {{ $user->currentPlan()->value === 'free' ? '🆓' : ($user->currentPlan()->value === 'pro' ? '⭐' : '🏢') }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">خطة {{ $user->currentPlan()->label() }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            @if($user->currentPlan()->value === 'free')
                                الخطة المجانية — محدودة المميزات
                            @elseif($user->currentPlan()->value === 'pro')
                                خطة Pro — للمستقلين والعمل الاحترافي
                            @else
                                خطة Business — للشركات والمؤسسات
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Plan Limits --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">حدود استخدامك الحالية</h3>
                <div class="space-y-3">
                    @php
                        $plan          = $user->currentPlan();
                        $projectsUsed  = $user->projects()->count();
                        $projectsMax   = $plan->maxProjects();
                        $txThisMonth   = $user->transactions()->whereMonth('transaction_date', now()->month)->count();
                        $txMax         = $plan->maxTransactionsPerMonth();
                    @endphp

                    {{-- Projects --}}
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>المشاريع</span>
                            <span>{{ $projectsUsed }} / {{ $projectsMax === PHP_INT_MAX ? '∞' : $projectsMax }}</span>
                        </div>
                        @if($projectsMax !== PHP_INT_MAX)
                            @php $pct = min(round(($projectsUsed / $projectsMax) * 100), 100); @endphp
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-400' : 'bg-indigo-400' }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                        @endif
                    </div>

                    {{-- Transactions this month --}}
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>معاملات هذا الشهر</span>
                            <span>{{ $txThisMonth }} / {{ $txMax === PHP_INT_MAX ? '∞' : $txMax }}</span>
                        </div>
                        @if($txMax !== PHP_INT_MAX)
                            @php $pct = min(round(($txThisMonth / $txMax) * 100), 100); @endphp
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-400' : 'bg-indigo-400' }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                        @endif
                    </div>

                    {{-- Features --}}
                    <div class="pt-2 space-y-2">
                        @foreach([
                            ['label' => 'تصدير البيانات', 'enabled' => $plan->canExport()],
                            ['label' => 'التقارير المتقدمة', 'enabled' => $plan->hasAdvancedReports()],
                            ['label' => 'الوصول للـ API',   'enabled' => $plan->canAccessApi()],
                        ] as $feature)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="{{ $feature['enabled'] ? 'text-green-500' : 'text-gray-300' }}">
                                {{ $feature['enabled'] ? '✓' : '✗' }}
                            </span>
                            <span class="{{ $feature['enabled'] ? 'text-gray-700' : 'text-gray-400' }}">
                                {{ $feature['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Upgrade CTA --}}
            @if($user->currentPlan()->value !== 'business')
            <div class="bg-gradient-to-l from-indigo-600 to-violet-600 rounded-2xl p-6 text-white">
                <p class="font-bold text-lg mb-1">🚀 ارتقِ بتجربتك</p>
                <p class="text-sm text-indigo-100 mb-4">
                    ترقية للـ Pro تمنحك مشاريع غير محدودة، 500 معاملة شهرياً، وتقارير متقدمة.
                </p>
                <a href="{{ route('settings.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-white text-indigo-700
                          font-semibold text-sm rounded-xl hover:bg-indigo-50 transition">
                    عرض الخطط
                    <span>←</span>
                </a>
                <p class="text-xs text-indigo-200 mt-3">سيتوفر نظام الاشتراكات قريباً (Phase 11)</p>
            </div>
            @endif

        </div>
    </div>

    {{-- ==================== Invoice Settings Tab ==================== --}}
    @php
        $userId      = auth()->id();
        $invColor    = \App\Models\Setting::get("invoice_color_{$userId}", '#4f46e5');
        $invName     = \App\Models\Setting::get("invoice_company_name_{$userId}", auth()->user()->name);
        $invInfo     = \App\Models\Setting::get("invoice_company_info_{$userId}", '');
        $invFooter   = \App\Models\Setting::get("invoice_footer_{$userId}", '');
        $invLogoPath = \App\Models\Setting::get("invoice_logo_{$userId}");
    @endphp
    <div x-show="tab === 'invoice'" id="invoice" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-6">
            <h2 class="text-base font-semibold text-gray-800">🧾 تخصيص قالب الفاتورة</h2>

            <form method="POST" action="{{ route('settings.invoice') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- شعار الشركة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">شعار الشركة</label>
                    @if($invLogoPath)
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ Storage::url($invLogoPath) }}" alt="Logo" class="h-16 w-auto rounded-lg border border-gray-200 object-contain p-1">
                        <label class="flex items-center gap-2 text-sm text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300">
                            حذف الشعار الحالي
                        </label>
                    </div>
                    @endif
                    <input type="file" name="invoice_logo" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:ml-0 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition">
                    <p class="text-xs text-gray-400 mt-1">PNG أو JPG — بحد أقصى 2MB</p>
                </div>

                {{-- لون القالب --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">لون القالب الرئيسي</label>
                    <div class="flex items-center gap-3 flex-wrap">
                        <input type="color" name="invoice_color" value="{{ $invColor }}"
                               x-on:input="document.getElementById('colorText').value=$el.value; document.getElementById('previewHeader').style.background=$el.value"
                               class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer p-1">
                        <input type="text" id="colorText" value="{{ $invColor }}"
                               oninput="document.querySelector('[name=invoice_color]').value=this.value; document.getElementById('previewHeader').style.background=this.value"
                               class="w-28 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                        <div class="flex gap-2">
                            @foreach(['#4f46e5','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#1e293b'] as $c)
                            <button type="button" onclick="setColor('{{ $c }}')"
                                    class="w-7 h-7 rounded-full border-2 border-white shadow hover:scale-110 transition"
                                    style="background:{{ $c }}" title="{{ $c }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- اسم الشركة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">اسم الشركة / المستقل</label>
                    <input type="text" name="invoice_company_name" value="{{ old('invoice_company_name', $invName) }}"
                           placeholder="مثال: أحمد للتصميم"
                           class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- معلومات التواصل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">معلومات التواصل</label>
                    <textarea name="invoice_company_info" rows="3"
                              placeholder="العنوان، الهاتف، البريد الإلكتروني، رقم السجل التجاري..."
                              class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none">{{ old('invoice_company_info', $invInfo) }}</textarea>
                </div>

                {{-- نص الفوتر --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">نص أسفل الفاتورة</label>
                    <input type="text" name="invoice_footer" value="{{ old('invoice_footer', $invFooter) }}"
                           placeholder="مثال: شكراً لتعاملك معنا — الدفع خلال 30 يوماً"
                           class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- معاينة الهيدر --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">معاينة</label>
                    <div id="previewHeader" class="rounded-xl p-4 flex items-center gap-3"
                         style="background: {{ $invColor }}">
                        @if($invLogoPath)
                        <img src="{{ Storage::url($invLogoPath) }}" alt="Logo" class="h-10 w-auto object-contain">
                        @else
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                            {{ mb_substr($invName ?: 'A', 0, 1) }}
                        </div>
                        @endif
                        <div>
                            <p class="text-white font-bold text-sm">{{ $invName ?: 'اسم الشركة' }}</p>
                            <p class="text-white/70 text-xs">فاتورة رقم INV-0001</p>
                        </div>
                    </div>
                </div>

                @if(session('success') && str_contains(session('success') ?? '', 'فاتورة'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-2">
                    ✅ {{ session('success') }}
                </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                        حفظ إعدادات الفاتورة
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function setColor(hex) {
    document.querySelector('[name=invoice_color]').value = hex;
    document.getElementById('colorText').value = hex;
    document.getElementById('previewHeader').style.background = hex;
}
</script>

@endsection
