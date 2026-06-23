@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{ tab: window.location.hash.replace('#','') || 'profile' }">

    {{-- Header + Tabs --}}
    <div>
        <h1 class="text-xl font-bold text-ink tracking-tight">الإعدادات</h1>
        <p class="mt-1 text-sm text-muted">إدارة حسابك وتفضيلاتك</p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
        @foreach([
            ['id' => 'profile',     'label' => '👤 الملف الشخصي'],
            ['id' => 'invoice',     'label' => '🧾 قالب الفاتورة'],
            ['id' => 'security',    'label' => '🔒 الأمان'],
            ['id' => 'preferences', 'label' => '⚙️ التفضيلات'],
            ['id' => 'plan',        'label' => '💼 الخطة'],
        ] as $t)
        <button @click="tab = '{{ $t['id'] }}'; window.location.hash = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}'
                    ? 'bg-white text-ink shadow-sm'
                    : 'text-muted hover:text-ink'"
                class="flex-1 py-2 px-3 text-xs sm:text-sm font-medium rounded-lg transition">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ==================== Profile Tab ==================== --}}
    <div x-show="tab === 'profile'" id="profile">
        <div class="dash-card p-6">
            <h2 class="text-base font-bold text-ink mb-5 flex items-center gap-2">
                <span class="text-xl">👤</span> بيانات الملف الشخصي
            </h2>

            <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                @csrf @method('PATCH')

                {{-- Avatar --}}
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-brand-100 flex items-center justify-center">
                        <span class="text-brand-600 font-bold text-2xl">
                            {{ mb_substr($user->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $user->email }}</p>
                        <p class="text-xs text-brand mt-0.5">خطة {{ $user->currentPlan()->label() }}</p>
                    </div>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="dash-field px-4 py-2.5
                                  @error('name') dash-field-error @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="dash-field px-4 py-2.5
                                  @error('email') dash-field-error @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn transition-colors">
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
            <div class="dash-card p-6">
                <h2 class="text-base font-bold text-ink mb-5 flex items-center gap-2">
                    <span class="text-xl">🔑</span> تغيير كلمة المرور
                </h2>

                <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="dash-field px-4 py-2.5
                                      @error('current_password') dash-field-error @enderror">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="dash-field px-4 py-2.5
                                      @error('password') dash-field-error @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted">8 أحرف على الأقل، تتضمن أرقاماً وحروفاً</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                               class="dash-field px-4 py-2.5">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn transition-colors">
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
                <p class="text-sm text-slate-500 mb-4">
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
                                class="px-4 py-2 bg-slate-100 text-slate-600 text-sm rounded-xl hover:bg-slate-200 transition">
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
        <div class="dash-card p-6">
            <h2 class="text-base font-bold text-ink mb-5 flex items-center gap-2">
                <span class="text-xl">⚙️</span> التفضيلات
            </h2>

            <form method="POST" action="{{ route('settings.preferences') }}" class="space-y-5">
                @csrf @method('PATCH')

                {{-- Currency --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">العملة الافتراضية</label>
                    <select name="currency"
                            class="dash-field px-4 py-2.5">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" {{ $user->currency === $code ? 'selected' : '' }}>
                                {{ $code }} — {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-muted">تُستخدم كعملة افتراضية في المعاملات الجديدة</p>
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">المنطقة الزمنية</label>
                    <select name="timezone"
                            class="dash-field px-4 py-2.5">
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

                {{-- الهامش المستهدف --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        الهامش المستهدف للخدمات
                        <span class="text-slate-400 font-normal text-xs">(%)</span>
                    </label>
                    <p class="text-xs text-slate-500 mb-2">
                        يُستخدم لاقتراح الأسعار وتنبيه الهامش المنخفض عند إنشاء المشاريع.
                    </p>
                    <div class="flex items-center gap-4">
                        <input type="range" name="target_margin_pct"
                               min="1" max="99" step="1"
                               value="{{ old('target_margin_pct', $user->target_margin_pct ?? 40) }}"
                               class="flex-1 accent-brand"
                               x-data
                               x-model.number="$el.value"
                               @input="$el.nextElementSibling.textContent = $el.value + '%'"
                               oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <span class="w-12 text-center text-sm font-bold text-brand-600 bg-brand-50 rounded-lg py-1">
                            {{ old('target_margin_pct', $user->target_margin_pct ?? 40) }}%
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400 mt-1 px-0.5">
                        <span>1%</span>
                        <span>حرص متوسط (40%)</span>
                        <span>99%</span>
                    </div>
                    @error('target_margin_pct')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn transition-colors">
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
            @php
                $plan         = $user->currentPlan();
                $projectsUsed = $user->projects()->count();
                $projectsMax  = $plan->maxProjects();
                $txThisMonth  = $user->transactions()->whereMonth('transaction_date', now()->month)->count();
                $txMax        = $plan->maxTransactionsPerMonth();
                $invoicesCount = \App\Models\Invoice::where('user_id', $user->id)->count();
                $clientsCount  = \App\Models\Client::where('user_id', $user->id)->count();

                $projPct  = $projectsMax !== PHP_INT_MAX ? min(round(($projectsUsed / $projectsMax) * 100), 100) : 0;
                $txPct    = $txMax       !== PHP_INT_MAX ? min(round(($txThisMonth  / $txMax)       * 100), 100) : 0;
                $nearLimit = ($projPct >= 80 || $txPct >= 80);
                $atLimit   = ($projPct >= 100 || $txPct >= 100);
            @endphp

            <div class="dash-card p-6">
                <h2 class="text-base font-bold text-ink mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    خطتك الحالية
                </h2>

                <div class="flex items-center gap-4 p-4 rounded-xl
                    {{ $plan->value === 'free' ? 'bg-slate-50 border border-slate-200' : 'bg-brand-50 border border-brand/30' }}">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $plan->value === 'free' ? 'bg-slate-200' : 'bg-brand' }}">
                        <svg class="w-6 h-6 {{ $plan->value === 'free' ? 'text-slate-500' : 'text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($plan->value === 'free')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">{{ $plan->label() }}</p>
                        <p class="text-sm text-slate-500 mt-0.5">
                            @if($plan->value === 'free')
                                ابدأ مجاناً — يمكنك الترقية في أي وقت
                            @elseif($plan->value === 'pro')
                                خطة Pro — للمستقلين المحترفين
                            @else
                                Business — للشركات والفرق
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- تحذير الاقتراب من الحد --}}
            @if($atLimit)
            <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-sm">
                    <span class="font-semibold text-red-700">وصلت للحد الأقصى</span>
                    <span class="text-red-600"> — لن تتمكن من إضافة المزيد حتى الترقية.</span>
                </div>
            </div>
            @elseif($nearLimit)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-sm">
                    <span class="font-semibold text-amber-700">اقتربت من الحد</span>
                    <span class="text-amber-600"> — فكّر في الترقية قبل أن تتوقف.</span>
                </div>
            </div>
            @endif

            {{-- Plan Limits --}}
            <div class="dash-card p-6">
                <h3 class="text-sm font-bold text-ink mb-4">استخدامك الحالي</h3>
                <div class="space-y-4">

                    {{-- Projects --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-slate-600">المشاريع</span>
                            <span class="{{ $projPct >= 90 ? 'text-red-600 font-semibold' : ($projPct >= 80 ? 'text-amber-600 font-medium' : 'text-slate-500') }}">
                                {{ $projectsUsed }} / {{ $projectsMax === PHP_INT_MAX ? '∞' : $projectsMax }}
                                @if($projectsMax !== PHP_INT_MAX && $projPct < 100)
                                    <span class="text-xs font-normal text-slate-400">(تبقّى {{ $projectsMax - $projectsUsed }})</span>
                                @endif
                            </span>
                        </div>
                        @if($projectsMax !== PHP_INT_MAX)
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                {{ $projPct >= 100 ? 'bg-red-500' : ($projPct >= 80 ? 'bg-amber-400' : 'bg-brand/80') }}"
                                 style="width:{{ $projPct }}%"></div>
                        </div>
                        @else
                        <div class="w-full bg-brand-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-brand/80 w-full opacity-30"></div>
                        </div>
                        @endif
                    </div>

                    {{-- Transactions --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-slate-600">معاملات هذا الشهر</span>
                            <span class="{{ $txPct >= 90 ? 'text-red-600 font-semibold' : ($txPct >= 80 ? 'text-amber-600 font-medium' : 'text-slate-500') }}">
                                {{ $txThisMonth }} / {{ $txMax === PHP_INT_MAX ? '∞' : $txMax }}
                                @if($txMax !== PHP_INT_MAX && $txPct < 100)
                                    <span class="text-xs font-normal text-slate-400">(تبقّى {{ $txMax - $txThisMonth }})</span>
                                @endif
                            </span>
                        </div>
                        @if($txMax !== PHP_INT_MAX)
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                {{ $txPct >= 100 ? 'bg-red-500' : ($txPct >= 80 ? 'bg-amber-400' : 'bg-brand/80') }}"
                                 style="width:{{ $txPct }}%"></div>
                        </div>
                        @else
                        <div class="w-full bg-brand-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-brand/80 w-full opacity-30"></div>
                        </div>
                        @endif
                    </div>

                    {{-- إحصائيات إضافية (بدون حدود) --}}
                    <div class="pt-2 grid grid-cols-2 gap-3 border-t border-subtle">
                        <div class="bg-slate-50 rounded-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-slate-800">{{ $invoicesCount }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">فاتورة</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-slate-800">{{ $clientsCount }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">عميل</p>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="pt-1 space-y-2 border-t border-subtle">
                        <p class="text-xs font-medium text-slate-400 pt-1">الميزات المتاحة</p>
                        @foreach([
                            ['label' => 'تصدير البيانات',   'enabled' => $plan->canExport()],
                            ['label' => 'التقارير المتقدمة', 'enabled' => $plan->hasAdvancedReports()],
                            ['label' => 'الوصول للـ API',    'enabled' => $plan->canAccessApi()],
                        ] as $feature)
                        <div class="flex items-center gap-2.5 text-sm">
                            @if($feature['enabled'])
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-slate-700">{{ $feature['label'] }}</span>
                            @else
                                <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span class="text-slate-400 line-through decoration-slate-200">{{ $feature['label'] }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Upgrade CTA --}}
            @if($plan->value !== 'business')
            <div class="bg-gradient-to-l from-brand to-violet-600 rounded-2xl p-6 text-white">
                @if($atLimit)
                    <p class="font-bold text-lg mb-1">وصلت للحد — حان وقت الترقية</p>
                    <p class="text-sm text-brand-100 mb-4">
                        @if($projPct >= 100) لا يمكنك إضافة مشاريع جديدة. @endif
                        @if($txPct  >= 100) لا يمكنك تسجيل معاملات هذا الشهر. @endif
                        الترقية إلى Pro تحل المشكلة فوراً.
                    </p>
                @elseif($nearLimit)
                    <p class="font-bold text-lg mb-1">اقتربت من الحد 🔔</p>
                    <p class="text-sm text-brand-100 mb-4">
                        @if($projPct >= 80) تبقّى لك {{ $projectsMax - $projectsUsed }} مشروع فقط. @endif
                        @if($txPct  >= 80) تبقّى لك {{ $txMax - $txThisMonth }} معاملة هذا الشهر. @endif
                        الترقية إلى Pro تمنحك مساحة أكبر بكثير.
                    </p>
                @else
                    <p class="font-bold text-lg mb-1">🚀 ارتقِ بتجربتك</p>
                    <p class="text-sm text-brand-100 mb-4">
                        ترقية للـ Pro: 10 مشاريع، 500 معاملة شهرياً، تقارير متقدمة، وتصدير البيانات.
                    </p>
                @endif
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('billing.upgrade') }}"
                       class="inline-flex items-center gap-2 px-5 py-2 bg-white text-brand-600
                              font-semibold text-sm rounded-xl hover:bg-brand-50 transition">
                        ترقية الخطة
                        <span>←</span>
                    </a>
                    <a href="{{ route('billing.index') }}"
                       class="text-sm text-brand/40 hover:text-white transition">
                        عرض جميع الخطط
                    </a>
                </div>
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
        <div class="dash-card p-6 space-y-6">
            <h2 class="text-base font-bold text-ink">🧾 تخصيص قالب الفاتورة</h2>

            <form method="POST" action="{{ route('settings.invoice') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- شعار الشركة --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">شعار الشركة</label>
                    @if($invLogoPath)
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ Storage::url($invLogoPath) }}" alt="Logo" class="h-16 w-auto rounded-lg border border-slate-200 object-contain p-1">
                        <label class="flex items-center gap-2 text-sm text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300">
                            حذف الشعار الحالي
                        </label>
                    </div>
                    @endif
                    <input type="file" name="invoice_logo" accept="image/*"
                           class="block w-full text-sm text-slate-500 file:ml-0 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand hover:file:bg-brand-100 transition">
                    <p class="text-xs text-muted mt-1">PNG أو JPG — بحد أقصى 2MB</p>
                </div>

                {{-- لون القالب --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">لون القالب الرئيسي</label>
                    <div class="flex items-center gap-3 flex-wrap">
                        <input type="color" name="invoice_color" value="{{ $invColor }}"
                               x-on:input="document.getElementById('colorText').value=$el.value; document.getElementById('previewHeader').style.background=$el.value"
                               class="w-12 h-10 rounded-lg border border-slate-200 cursor-pointer p-1">
                        <input type="text" id="colorText" value="{{ $invColor }}"
                               oninput="document.querySelector('[name=invoice_color]').value=this.value; document.getElementById('previewHeader').style.background=this.value"
                               class="w-28 px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent/40 focus:outline-none font-mono">
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
                    <label class="block text-sm font-semibold text-ink mb-1.5">اسم الشركة / المستقل</label>
                    <input type="text" name="invoice_company_name" value="{{ old('invoice_company_name', $invName) }}"
                           placeholder="مثال: أحمد للتصميم"
                           class="dash-field px-3.5 py-2.5">
                </div>

                {{-- معلومات التواصل --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">معلومات التواصل</label>
                    <textarea name="invoice_company_info" rows="3"
                              placeholder="العنوان، الهاتف، البريد الإلكتروني، رقم السجل التجاري..."
                              class="dash-field px-3.5 py-2.5 resize-none">{{ old('invoice_company_info', $invInfo) }}</textarea>
                </div>

                {{-- نص الفوتر --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">نص أسفل الفاتورة</label>
                    <input type="text" name="invoice_footer" value="{{ old('invoice_footer', $invFooter) }}"
                           placeholder="مثال: شكراً لتعاملك معنا — الدفع خلال 30 يوماً"
                           class="dash-field px-3.5 py-2.5">
                </div>

                {{-- معاينة الهيدر --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">معاينة</label>
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
                            class="px-5 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-medium rounded-xl transition">
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
