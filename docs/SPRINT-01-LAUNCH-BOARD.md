# SPRINT-01-LAUNCH-BOARD.md
## لوحة تنفيذ Sprint-01 — إطلاق التسعير دراهم

> **المشروع:** Darahum — Pricing MVP Launch
> **Sprint رقم:** 01
> **المدة:** 7 أيام (يوم 1 → يوم 7)
> **هدف Sprint:** إطلاق نظام التسعير وتحصيل أول عميل مدفوع
> **المرجع الرسمي:** `docs/PRICING-SOURCE-OF-TRUTH.md`
> **Stack:** Laravel 12 · PHP 8.2 · Filament 3.3 · Livewire 3 · Blade
> **الحالة الأولية:** تم تدقيق الكود بتاريخ 24 يونيو 2026

---

## 📋 جدول المحتويات

1. [ملخص Sprint](#ملخص-sprint)
2. [Epic 1 — محرك حدود الاشتراك](#epic-1--محرك-حدود-الاشتراك)
3. [Epic 2 — بوابات الميزات في الواجهة](#epic-2--بوابات-الميزات-في-الواجهة)
4. [Epic 3 — تدفق تفعيل Admin](#epic-3--تدفق-تفعيل-admin)
5. [Epic 4 — صفحات التسعير والفوترة](#epic-4--صفحات-التسعير-والفوترة)
6. [Epic 5 — ضمان الجودة والإطلاق](#epic-5--ضمان-الجودة-والإطلاق)
7. [Board Columns — موقع كل مهمة](#board-columns)
8. [خريطة الأيام](#خريطة-الأيام)
9. [Launch Readiness Checklists](#launch-readiness-checklists)
10. [Risk Register](#risk-register)

---

## ملخص Sprint

### الوضع الحالي (As-Is) — تدقيق 24 يونيو 2026

| العنصر | المشكلة الدقيقة | الملف |
|--------|----------------|-------|
| `maxProjects()` Free | يُرجع 2 ← يجب 3 | `SubscriptionPlan.php:22` |
| `maxProjects()` Pro | يُرجع 10 ← يجب ∞ | `SubscriptionPlan.php:23` |
| `maxTransactionsPerMonth()` Pro | يُرجع 500 ← يجب 1,000 | `SubscriptionPlan.php:30` |
| Middleware hint text للـ Projects | "حتى 10 مشاريع" ← يجب "غير محدودة" | `CheckSubscriptionLimits.php:43` |
| Middleware hint text للـ Transactions | "500 معاملة" ← يجب "1,000 معاملة" | `CheckSubscriptionLimits.php:54` |
| `maxClients()` | غير موجودة | — |
| `maxInvoicesPerMonth()` | غير موجودة | — |
| `maxQuotesPerMonth()` | غير موجودة | — |
| `can(string $gate)` | غير موجودة | — |
| Middleware للـ Clients | غير مسجّل على `clients.store` | `crm.php:37` |
| Middleware للـ Invoices | غير مسجّل على `invoices.store` | `web.php:65` |
| Middleware للـ Quotes | غير مسجّل على `quotes.store` | `web.php:52` |
| Upgrade Prompt component | غير موجود | — |
| Plan badge في sidebar | غير موجود | — |
| Feature Gates في Views | غير موجودة | — |
| `activatePlan` Admin Action | غير موجودة في UserResource | `UserResource.php` |
| `config/billing.php` prices | SAR 99/299 ← يجب USD $17/$45 | `config/billing.php` |
| `getPlanPrices()` في Service | تُرجع SAR ← يجب USD | `SubscriptionService.php` |
| `billing/upgrade.blade.php` | "10 مشاريع", "500 معاملة", 99 SAR | View |
| `marketing/pricing.blade.php` CTAs | `href="#"` (3 أزرار معطّلة) | View:84,136,179 |
| `marketing/pricing.blade.php` text | "مشروعَين", "500 معاملة" للـ Pro | View:61,113 |

### النتيجة المستهدفة (To-Be)

```
مستخدم Starter
  → يصل لـ 5 عملاء / 3 مشاريع / 5 فواتير / 3 عروض / 50 معاملة
  → يرى Upgrade Prompt واضح مع رابط /billing/upgrade
  → يرى ميزات مقفولة مع Lock icon + "متاح في Pro"

مستخدم يريد الترقية
  → /pricing: CTAs تعمل → /register أو /billing/upgrade
  → /billing/upgrade: يرى $17/شهر Pro · $45/شهر Business (USD)
  → يضغط "تواصل على واتساب" → رسالة جاهزة

Admin
  → يجد المستخدم → يضغط "تفعيل خطة"
  → يختار Pro/$17 · End Date · ملاحظة الدفع
  → subscription_plan يتحدث → المستخدم يرى badge Pro فوراً
```

### الساعات الإجمالية المقدّرة

| Epic | الساعات |
|------|---------|
| E1: محرك الحدود | 7h |
| E2: Feature Gates | 7h |
| E3: Admin Flow | 4.5h |
| E4: صفحات التسعير | 5h |
| E5: QA والإطلاق | 5.5h |
| **المجموع** | **29h ≈ 7 أيام × 4-5h/يوم** |

---

---

## EPIC 1 — محرك حدود الاشتراك

> **الهدف:** تطبيق حدود الاستخدام الصحيحة على كل موارد Starter (عملاء / مشاريع / فواتير / عروض / معاملات)
> **القيمة التجارية:** بدون هذا Epic لا توجد محفّزات ترقية حقيقية — المنتج "مجاني فعلياً" بلا حدود
> **معيار النجاح:** مستخدم Free لا يستطيع تجاوز الحد في أي مورد — يرى Upgrade Prompt واضح في كل حالة

---

### T01 — إصلاح SubscriptionPlan Enum

| الحقل | القيمة |
|-------|--------|
| **ID** | T01 |
| **العنوان** | Fix SubscriptionPlan Enum — تصحيح القيم + إضافة Methods المفقودة |
| **الأولوية** | 🔴 Critical |
| **الملف** | `app/Support/Enums/SubscriptionPlan.php` |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | لا يوجد |

**الوصف:**
الـ Enum الحالي يحتوي على 3 أخطاء في القيم و3 methods مفقودة. هذه المهمة تُصحح الأخطاء وتُضيف جميع methods الحدود + method `can()` للـ Feature Gates.

**Subtasks:**

```
[ ] ST01.1 — تصحيح maxProjects()
    الحالي: Free=2, Pro=10, Business=∞
    المطلوب: Free=3, Pro=PHP_INT_MAX, Business=PHP_INT_MAX

[ ] ST01.2 — تصحيح maxTransactionsPerMonth()
    الحالي: Free=50, Pro=500, Business=∞
    المطلوب: Free=50, Pro=1000, Business=PHP_INT_MAX

[ ] ST01.3 — إضافة maxClients()
    Free=5, Pro=PHP_INT_MAX, Business=PHP_INT_MAX

[ ] ST01.4 — إضافة maxInvoicesPerMonth()
    Free=5, Pro=PHP_INT_MAX, Business=PHP_INT_MAX

[ ] ST01.5 — إضافة maxQuotesPerMonth()
    Free=3, Pro=PHP_INT_MAX, Business=PHP_INT_MAX

[ ] ST01.6 — إضافة maxTeamMembers() (عدد الإضافيين)
    Free=0, Pro=1, Business=9

[ ] ST01.7 — إضافة maxStorageMB()
    Free=500, Pro=10240, Business=102400

[ ] ST01.8 — إضافة can(string $gate): bool
    (الكود المفصّل في Acceptance Criteria)
```

**الكود المطلوب:**

```php
public function maxProjects(): int
{
    return match($this) {
        self::Free     => 3,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxTransactionsPerMonth(): int
{
    return match($this) {
        self::Free     => 50,
        self::Pro      => 1000,
        self::Business => PHP_INT_MAX,
    };
}

public function maxClients(): int
{
    return match($this) {
        self::Free     => 5,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxInvoicesPerMonth(): int
{
    return match($this) {
        self::Free     => 5,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxQuotesPerMonth(): int
{
    return match($this) {
        self::Free     => 3,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxTeamMembers(): int
{
    return match($this) {
        self::Free     => 0,
        self::Pro      => 1,
        self::Business => 9,
    };
}

public function maxStorageMB(): int
{
    return match($this) {
        self::Free     => 500,
        self::Pro      => 10240,
        self::Business => 102400,
    };
}

public function can(string $gate): bool
{
    return match($gate) {
        'export_data'              => $this !== self::Free,
        'advanced_reports'         => $this !== self::Free,
        'send_invoice_email'       => $this !== self::Free,
        'wallets'                  => $this !== self::Free,
        'multi_currency'           => $this !== self::Free,
        'client_portal'            => $this !== self::Free,
        'advanced_crm'             => $this !== self::Free,
        'import_excel'             => $this !== self::Free,
        'recurring_transactions'   => $this !== self::Free,
        'custom_invoice_templates' => $this !== self::Free,
        'recurring_invoices'       => $this !== self::Free,
        'zatca_compliance'         => $this !== self::Free,
        'payment_gateways'         => $this !== self::Free,
        'time_tracking'            => $this !== self::Free,
        'project_profitability'    => $this !== self::Free,
        'two_factor_auth'          => $this !== self::Free,
        'cash_flow_forecast'       => $this !== self::Free,
        'white_label'              => $this === self::Business,
        'team_projects'            => $this === self::Business,
        'milestones'               => $this === self::Business,
        'bulk_operations'          => $this === self::Business,
        'custom_permissions'       => $this === self::Business,
        'activity_log'             => $this === self::Business,
        'api_access'               => $this === self::Business,
        'webhooks'                 => $this === self::Business,
        'automation_rules'         => $this === self::Business,
        'whatsapp_automation'      => $this === self::Business,
        'team_reports'             => $this === self::Business,
        'custom_client_fields'     => $this === self::Business,
        default                    => false,
    };
}
```

**Acceptance Criteria:**

```
✅ AC01.1 — SubscriptionPlan::Free->maxProjects() === 3
✅ AC01.2 — SubscriptionPlan::Pro->maxProjects() === PHP_INT_MAX
✅ AC01.3 — SubscriptionPlan::Business->maxProjects() === PHP_INT_MAX
✅ AC01.4 — SubscriptionPlan::Free->maxTransactionsPerMonth() === 50
✅ AC01.5 — SubscriptionPlan::Pro->maxTransactionsPerMonth() === 1000
✅ AC01.6 — SubscriptionPlan::Business->maxTransactionsPerMonth() === PHP_INT_MAX
✅ AC01.7 — SubscriptionPlan::Free->maxClients() === 5
✅ AC01.8 — SubscriptionPlan::Pro->maxClients() === PHP_INT_MAX
✅ AC01.9 — SubscriptionPlan::Free->maxInvoicesPerMonth() === 5
✅ AC01.10 — SubscriptionPlan::Free->maxQuotesPerMonth() === 3
✅ AC01.11 — SubscriptionPlan::Free->can('send_invoice_email') === false
✅ AC01.12 — SubscriptionPlan::Pro->can('send_invoice_email') === true
✅ AC01.13 — SubscriptionPlan::Pro->can('api_access') === false
✅ AC01.14 — SubscriptionPlan::Business->can('api_access') === true
✅ AC01.15 — لا يوجد syntax error (php artisan route:list يعمل)
```

---

### T02 — توسيع CheckSubscriptionLimits Middleware

| الحقل | القيمة |
|-------|--------|
| **ID** | T02 |
| **العنوان** | Extend CheckSubscriptionLimits — إضافة clients / invoices / quotes + تصحيح hint texts |
| **الأولوية** | 🔴 Critical |
| **الملف** | `app/Http/Middleware/CheckSubscriptionLimits.php` |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | T01 (يجب أن تكون methods موجودة أولاً) |

**Subtasks:**

```
[ ] ST02.1 — إضافة case 'clients' لـ match + checkClients() method
[ ] ST02.2 — إضافة case 'invoices' + checkInvoices() method
[ ] ST02.3 — إضافة case 'quotes' + checkQuotes() method
[ ] ST02.4 — تصحيح hint text في checkProjects():
    الحالي: "الترقية إلى Pro تتيح لك حتى 10 مشاريع"
    المطلوب: "الترقية إلى Pro تتيح لك مشاريع غير محدودة"
[ ] ST02.5 — تصحيح hint text في checkTransactions():
    الحالي: "Pro تتيح لك 500 معاملة شهرياً"
    المطلوب: "Pro تتيح لك 1,000 معاملة شهرياً"
[ ] ST02.6 — تصحيح عدد المشاريع في message checkProjects():
    الحالي: "{$max} مشاريع" ← حين max=3 سيعرض "3 مشاريع" صحيح تلقائياً ✓
```

**الكود المطلوب (methods الجديدة):**

```php
// في match():
'clients'      => $this->checkClients($user, $plan),
'invoices'     => $this->checkInvoices($user, $plan),
'quotes'       => $this->checkQuotes($user, $plan),

// Methods:
private function checkClients($user, $plan): void
{
    $max   = $plan->maxClients();
    $count = $user->clients()->whereNull('deleted_at')->count();

    if ($count >= $max) {
        session()->flash('upgrade_prompt', [
            'resource' => 'clients',
            'message'  => "وصلت للحد الأقصى ({$max} عملاء) في خطتك الحالية.",
            'hint'     => 'الترقية إلى Pro تتيح عملاء غير محدودين.',
        ]);
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            redirect()->back()->withInput()
        );
    }
}

private function checkInvoices($user, $plan): void
{
    $max   = $plan->maxInvoicesPerMonth();
    $count = $user->invoices()
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->whereNull('deleted_at')
        ->count();

    if ($count >= $max) {
        session()->flash('upgrade_prompt', [
            'resource' => 'invoices',
            'message'  => "وصلت للحد الأقصى ({$max} فواتير) هذا الشهر.",
            'hint'     => 'الترقية إلى Pro تتيح فواتير غير محدودة.',
        ]);
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            redirect()->back()->withInput()
        );
    }
}

private function checkQuotes($user, $plan): void
{
    $max   = $plan->maxQuotesPerMonth();
    $count = $user->quotes()
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->whereNull('deleted_at')
        ->count();

    if ($count >= $max) {
        session()->flash('upgrade_prompt', [
            'resource' => 'quotes',
            'message'  => "وصلت للحد الأقصى ({$max} عروض أسعار) هذا الشهر.",
            'hint'     => 'الترقية إلى Pro تتيح عروض أسعار غير محدودة.',
        ]);
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            redirect()->back()->withInput()
        );
    }
}
```

**Acceptance Criteria:**

```
✅ AC02.1 — مستخدم Free أضاف 5 عملاء → محاولة إضافة سادس → redirect مع flash upgrade_prompt
✅ AC02.2 — upgrade_prompt.resource === 'clients'
✅ AC02.3 — مستخدم Free أنشأ 5 فواتير هذا الشهر → محاولة إنشاء سادسة → redirect
✅ AC02.4 — مستخدم Free أنشأ 3 عروض هذا الشهر → محاولة إنشاء رابع → redirect
✅ AC02.5 — مستخدم Pro → إضافة عميل سادس → يمر بدون مشكلة
✅ AC02.6 — رسالة checkProjects hint تقول "مشاريع غير محدودة" (لا 10)
✅ AC02.7 — رسالة checkTransactions hint تقول "1,000 معاملة" (لا 500)
✅ AC02.8 — الفواتير المحذوفة (soft-delete) لا تُحسب في الحد
✅ AC02.9 — الحدود Monthly تُحسب لهذا الشهر فقط (يناير لا يؤثر على فبراير)
```

---

### T03 — تسجيل Middleware على Routes المفقودة

| الحقل | القيمة |
|-------|--------|
| **ID** | T03 |
| **العنوان** | Wire subscription middleware to clients.store / invoices.store / quotes.store |
| **الأولوية** | 🔴 Critical |
| **الملفات** | `routes/crm.php:37` · `routes/web.php:52,65` |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | T01, T02 |

**الوضع الحالي:**
```php
// routes/crm.php:37 — MISSING middleware
Route::post('/', [ClientController::class, 'store'])->name('store');

// routes/web.php:52 — MISSING middleware
Route::post('/', [QuoteController::class, 'store'])->name('store');

// routes/web.php:65 — MISSING middleware
Route::post('/', [InvoiceController::class, 'store'])->name('store');
```

**المطلوب:**
```php
// routes/crm.php:37
Route::post('/', [ClientController::class, 'store'])
    ->middleware('subscription:clients')
    ->name('store');

// routes/web.php:52
Route::post('/', [QuoteController::class, 'store'])
    ->middleware('subscription:quotes')
    ->name('store');

// routes/web.php:65
Route::post('/', [InvoiceController::class, 'store'])
    ->middleware('subscription:invoices')
    ->name('store');
```

**ملاحظة:** الـ alias `subscription` مسجّل بالفعل في `bootstrap/app.php:23`

**Acceptance Criteria:**

```
✅ AC03.1 — php artisan route:list | grep store يُظهر subscription middleware على clients/invoices/quotes
✅ AC03.2 — POST /clients بعد بلوغ الحد 5 → 302 redirect مع session flash
✅ AC03.3 — POST /invoices بعد بلوغ الحد 5 → 302 redirect
✅ AC03.4 — POST /quotes بعد بلوغ الحد 3 → 302 redirect
✅ AC03.5 — middleware لا يُفعَّل للـ Pro/Business (يمر بدون قيود)
```

---

### T04 — Upgrade Prompt Component + تضمينه في Layout

| الحقل | القيمة |
|-------|--------|
| **ID** | T04 |
| **العنوان** | Create upgrade-prompt Blade component + include في app.blade.php |
| **الأولوية** | 🟡 High |
| **الملفات** | `resources/views/components/upgrade-prompt.blade.php` (جديد) · `resources/views/layouts/app.blade.php:106` |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | T01, T02, T03 |

**Subtasks:**

```
[ ] ST04.1 — إنشاء resources/views/components/upgrade-prompt.blade.php
[ ] ST04.2 — إضافة @include أو <x-upgrade-prompt /> في app.blade.php
            قبل <main class="flex-1 p-4 sm:p-6"> مباشرة (السطر 106)
```

**الكود المطلوب (`upgrade-prompt.blade.php`):**

```blade
@if(session('upgrade_prompt'))
@php $prompt = session('upgrade_prompt'); @endphp
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="mx-4 sm:mx-6 mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20
           border border-amber-200 dark:border-amber-700
           px-4 py-3 flex items-start justify-between gap-4 print:hidden"
>
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                {{ $prompt['message'] }}
            </p>
            @if(!empty($prompt['hint']))
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                {{ $prompt['hint'] }}
            </p>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('billing.upgrade') }}"
           class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white
                  text-xs font-bold rounded-lg transition whitespace-nowrap">
            ترقية الآن ←
        </a>
        <button @click="show = false"
                class="text-amber-400 hover:text-amber-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif
```

**التضمين في `app.blade.php` قبل `<main>`:**

```blade
{{-- Upgrade Prompt --}}
<x-upgrade-prompt />

<main class="flex-1 p-4 sm:p-6">
    @yield('content')
</main>
```

**Acceptance Criteria:**

```
✅ AC04.1 — Prompt يظهر بعد redirect من محاولة تجاوز الحد
✅ AC04.2 — الرسالة تتطابق مع session('upgrade_prompt.message')
✅ AC04.3 — زر "ترقية الآن" يحوّل لـ /billing/upgrade
✅ AC04.4 — زر X يُخفي الـ prompt (Alpine.js)
✅ AC04.5 — لا يظهر على صفحات بدون session flash
✅ AC04.6 — لا يظهر في طباعة الصفحة (print:hidden)
✅ AC04.7 — Dark mode يعمل
```

---

---

## EPIC 2 — بوابات الميزات في الواجهة

> **الهدف:** تطبيق 6 Feature Gates الأساسية لإظهار الميزات المحجوبة مع CTAs الترقية
> **القيمة التجارية:** مستخدم Free يرى ما لا يملكه → دافع قوي للترقية
> **معيار النجاح:** 6 ميزات مقفولة بشكل صحيح مع Lock UI واضح ورابط ترقية

---

### T05 — إضافة Plan Badge في Sidebar

| الحقل | القيمة |
|-------|--------|
| **ID** | T05 |
| **العنوان** | Add subscription plan badge to sidebar layout |
| **الأولوية** | 🟡 High |
| **الملف** | `resources/views/layouts/partials/sidebar.blade.php` |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | T01 |

**Subtasks:**

```
[ ] ST05.1 — تحديد المكان المناسب في sidebar (قرب اسم المستخدم أو أسفل القائمة)
[ ] ST05.2 — إضافة conditional badge يظهر للمدفوعين فقط
[ ] ST05.3 — إضافة "ترقّ للـ Pro" link للـ Free users في أسفل sidebar
```

**الكود المطلوب:**

```blade
{{-- Plan Badge (أعلى sidebar أو بجانب اسم المستخدم) --}}
@php $plan = auth()->user()->currentPlan(); @endphp

@if($plan !== \App\Support\Enums\SubscriptionPlan::Free)
<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full
    {{ $plan->value === 'pro'
        ? 'bg-brand-100 text-brand dark:bg-brand-900/40 dark:text-brand/70'
        : 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' }}">
    ⚡ {{ $plan->label() }}
</span>
@else
{{-- Upgrade CTA للـ Free users --}}
<a href="{{ route('billing.upgrade') }}"
   class="flex items-center gap-2 px-3 py-2 rounded-xl
          bg-gradient-to-l from-brand/10 to-purple-100/50
          dark:from-brand/20 dark:to-purple-900/30
          text-xs font-medium text-brand dark:text-brand/80
          hover:opacity-90 transition">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
    ترقّ للـ Pro
</a>
@endif
```

**Acceptance Criteria:**

```
✅ AC05.1 — مستخدم Free يرى "ترقّ للـ Pro" في sidebar
✅ AC05.2 — مستخدم Pro يرى badge "⚡ Pro" ملوّن
✅ AC05.3 — مستخدم Business يرى badge "⚡ Business" بلون مختلف
✅ AC05.4 — الرابط يحوّل لـ /billing/upgrade
✅ AC05.5 — Dark mode يعمل
✅ AC05.6 — لا يظهر Badge للـ Free users (ليس "Starter" عليهم)
```

---

### T06 — Gate: send_invoice_email في InvoiceController + View

| الحقل | القيمة |
|-------|--------|
| **ID** | T06 |
| **العنوان** | Gate send_invoice_email — حجب إرسال الفاتورة بالبريد للـ Starter |
| **الأولوية** | 🔴 Critical |
| **الملفات** | InvoiceController (send action) + invoice show/list views |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | T01 (can() method) |

**Subtasks:**

```
[ ] ST06.1 — تحديد action/method إرسال الفاتورة بالبريد في InvoiceController
[ ] ST06.2 — إضافة check في Controller action:
    if (!auth()->user()->currentPlan()->can('send_invoice_email')) {
        return redirect()->route('billing.upgrade')
            ->with('upgrade_reason', 'إرسال الفاتورة بالبريد متاح في خطة Pro فأعلى');
    }
[ ] ST06.3 — في View لزر الإرسال بالبريد: إظهار locked state للـ Free
[ ] ST06.4 — إضافة tooltip: "إرسال الفاتورة بالبريد — متاح في Pro ⚡"
```

**Pattern الـ UI للـ Locked Feature:**

```blade
@if(auth()->user()->currentPlan()->can('send_invoice_email'))
    <button type="button" {{-- زر الإرسال الطبيعي --}}>
        إرسال بالبريد
    </button>
@else
    <div class="relative group inline-block">
        <button type="button" disabled
                class="opacity-40 cursor-not-allowed
                       {{-- نفس classes الزر الطبيعي --}}">
            إرسال بالبريد
            <svg class="w-3.5 h-3.5 inline-block mr-1" {{-- lock icon --}}>...</svg>
        </button>
        <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block z-50
                    bg-slate-900 text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap">
            متاح في خطة Pro ⚡
            <a href="{{ route('billing.upgrade') }}" class="underline mr-1">ترقية</a>
        </div>
    </div>
@endif
```

**Acceptance Criteria:**

```
✅ AC06.1 — مستخدم Free: زر إرسال البريد معطّل (disabled) مع Lock icon
✅ AC06.2 — Hover على الزر يُظهر tooltip "متاح في Pro ⚡"
✅ AC06.3 — مستخدم Free يحاول POST مباشر لـ send action → redirect لـ /billing/upgrade
✅ AC06.4 — مستخدم Pro: الزر يعمل بشكل طبيعي
✅ AC06.5 — مستخدم Business: الزر يعمل بشكل طبيعي
```

---

### T07 — Gate: export_data في Reports

| الحقل | القيمة |
|-------|--------|
| **ID** | T07 |
| **العنوان** | Gate export_data — حجب تصدير Excel/CSV/PDF للـ Starter في Reports |
| **الأولوية** | 🟡 High |
| **الملفات** | ReportController أو ExportController + report views |
| **التقدير** | 1.5 ساعة |
| **الاعتماديات** | T01 |

**ملاحظة:** `canExport()` موجودة بالفعل في الـ Enum — هذه المهمة تُطبّقها في Views والـ Controller.

**Subtasks:**

```
[ ] ST07.1 — إضافة check في Export Controller/Action:
    if (!auth()->user()->currentPlan()->canExport()) {
        return redirect()->back()
            ->with('upgrade_reason', 'التصدير متاح في خطة Pro فأعلى');
    }
[ ] ST07.2 — إظهار زر التصدير بـ locked state للـ Free في جميع صفحات Reports
[ ] ST07.3 — التأكد من تطبيق canExport() على Excel + CSV + PDF للتقارير
```

**Acceptance Criteria:**

```
✅ AC07.1 — مستخدم Free: أزرار التصدير (Excel/CSV/PDF) معطّلة مع Lock
✅ AC07.2 — مستخدم Free يحاول GET مباشر لـ /reports/export → redirect
✅ AC07.3 — مستخدم Pro: التصدير يعمل
✅ AC07.4 — Tooltip يوضح "التصدير متاح في Pro ⚡"
```

---

### T08 — Gate: wallets في Sidebar + WalletController

| الحقل | القيمة |
|-------|--------|
| **ID** | T08 |
| **العنوان** | Gate wallets — إخفاء/حجب Wallets للـ Starter مع CTA |
| **الأولوية** | 🟡 High |
| **الملفات** | `WalletController.php` + sidebar + wallets index view |
| **التقدير** | 1.5 ساعة |
| **الاعتماديات** | T01 |

**Subtasks:**

```
[ ] ST08.1 — في WalletController::index():
    if (!auth()->user()->currentPlan()->can('wallets')) {
        return redirect()->route('billing.upgrade')
            ->with('upgrade_reason', 'الصناديق المالية متاحة في Pro فأعلى');
    }
[ ] ST08.2 — في Sidebar: رابط Wallets يُظهر Lock icon + CTA للـ Free
[ ] ST08.3 — في Wallets index view: إضافة upgrade banner عند الدخول بـ Starter
             (Fallback في حال لم يتم redirect من Controller)
```

**Acceptance Criteria:**

```
✅ AC08.1 — مستخدم Free يزور /wallets → redirect لـ /billing/upgrade
✅ AC08.2 — رابط Wallets في Sidebar يُظهر 🔒 للـ Free
✅ AC08.3 — مستخدم Pro: /wallets يعمل بشكل طبيعي
```

---

### T09 — Gate: advanced_reports في Reports View

| الحقل | القيمة |
|-------|--------|
| **ID** | T09 |
| **العنوان** | Gate advanced_reports — Blur overlay على التقارير المتقدمة للـ Starter |
| **الأولوية** | 🟡 High |
| **الملف** | Reports Views |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | T01 |

**Pattern:**

```blade
@if(auth()->user()->currentPlan()->can('advanced_reports'))
    {{-- التقارير المتقدمة --}}
@else
    <div class="relative">
        <div class="blur-sm pointer-events-none select-none opacity-60">
            {{-- معاينة مبهمة للتقرير --}}
        </div>
        <div class="absolute inset-0 flex flex-col items-center justify-center
                    bg-white/80 dark:bg-slate-900/80 rounded-xl">
            <svg class="w-8 h-8 text-slate-400 mb-2">{{-- lock icon --}}</svg>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                التقارير المتقدمة متاحة في Pro
            </p>
            <a href="{{ route('billing.upgrade') }}"
               class="mt-3 px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg">
                ترقية للاحترافي ⚡
            </a>
        </div>
    </div>
@endif
```

**Acceptance Criteria:**

```
✅ AC09.1 — مستخدم Free يرى Blur overlay على التقارير المتقدمة
✅ AC09.2 — زر "ترقية للاحترافي" واضح وقابل للضغط
✅ AC09.3 — مستخدم Pro يرى التقارير كاملة
✅ AC09.4 — لوحة التحكم الأساسية تظهر لجميع المستخدمين
```

---

---

## EPIC 3 — تدفق تفعيل Admin

> **الهدف:** Admin يستطيع تفعيل خطة مدفوعة لمستخدم خلال 30 ثانية
> **القيمة التجارية:** هذا هو قلب المنظومة — بدونه لا يوجد مسار لتحصيل أي درهم
> **معيار النجاح:** Admin يُفعّل Pro لمستخدم → subscription_plan يتحدث → المستخدم يرى badge Pro فوراً

---

### T10 — إضافة activatePlan Action في UserResource

| الحقل | القيمة |
|-------|--------|
| **ID** | T10 |
| **العنوان** | Add activatePlan Filament Action to UserResource table |
| **الأولوية** | 🔴 Critical |
| **الملف** | `app/Filament/Resources/UserResource.php` |
| **التقدير** | 3 ساعات |
| **الاعتماديات** | T01, `SubscriptionService::activatePlan()` موجودة |

**Subtasks:**

```
[ ] ST10.1 — إضافة Action في Table Actions (بعد resetPlan وقبل sendEmail)
[ ] ST10.2 — Form يتضمن: Select(plan) + DatePicker(ends_at) + TextInput(notes)
[ ] ST10.3 — Action يستدعي SubscriptionService::activatePlan()
[ ] ST10.4 — تحديث ends_at من form (بدلاً من الشهر الافتراضي)
[ ] ST10.5 — حفظ ملاحظة الدفع في provider_subscription_id مؤقتاً
[ ] ST10.6 — Notification نجاح بعد التفعيل
[ ] ST10.7 — الـ Action يظهر فقط للـ Free users (visible condition)
```

**الكود المطلوب:**

```php
use App\Modules\Billing\Services\SubscriptionService;

// في Table::actions():
Tables\Actions\Action::make('activatePlan')
    ->label('تفعيل خطة')
    ->icon('heroicon-o-rocket-launch')
    ->color('success')
    ->tooltip('تفعيل اشتراك مدفوع لهذا المستخدم')
    ->visible(fn (User $record) => $record->subscription_plan === SubscriptionPlan::Free)
    ->form([
        Forms\Components\Select::make('plan')
            ->label('الخطة')
            ->options([
                'pro'      => 'Pro — $17/شهر (شهري) | $13/شهر (سنوي)',
                'business' => 'Business — $45/شهر (شهري) | $34/شهر (سنوي)',
            ])
            ->required(),

        Forms\Components\DatePicker::make('ends_at')
            ->label('تاريخ انتهاء الاشتراك')
            ->default(now()->addMonth())
            ->minDate(now()->addDay())
            ->required(),

        Forms\Components\TextInput::make('notes')
            ->label('ملاحظة الدفع')
            ->placeholder('مثال: تحويل بنكي - 64 SAR - 24 يونيو 2026')
            ->helperText('تُحفظ داخلياً للمرجعية — لا تظهر للمستخدم'),
    ])
    ->modalHeading(fn (User $record) => "تفعيل خطة مدفوعة لـ {$record->name}")
    ->modalSubmitActionLabel('تفعيل الاشتراك')
    ->action(function (User $record, array $data): void {
        $service = app(SubscriptionService::class);
        $subscription = $service->activatePlan($record, $data['plan']);

        $subscription->update([
            'ends_at'                  => $data['ends_at'],
            'provider_subscription_id' => $data['notes'] ?? 'manual',
        ]);

        Notification::make()
            ->title("✅ تم تفعيل خطة {$data['plan']} للمستخدم {$record->name}")
            ->body("تنتهي في: " . \Carbon\Carbon::parse($data['ends_at'])->format('d/m/Y'))
            ->success()
            ->send();
    }),
```

**Acceptance Criteria:**

```
✅ AC10.1 — زر "تفعيل خطة" يظهر فقط للمستخدمين على Starter
✅ AC10.2 — Modal يعرض: Select (Pro/Business) + Date (default=+30 days) + Notes
✅ AC10.3 — بعد التفعيل: users.subscription_plan === 'pro' أو 'business'
✅ AC10.4 — بعد التفعيل: سجل في جدول subscriptions بـ status=active
✅ AC10.5 — بعد التفعيل: subscriptions.ends_at === التاريخ المختار
✅ AC10.6 — بعد التفعيل: subscriptions.provider_subscription_id === ملاحظة الدفع
✅ AC10.7 — Notification نجاح تظهر في Filament
✅ AC10.8 — UserResource table يُحدَّث بدون reload (Livewire)
✅ AC10.9 — زر resetPlan يُعيد المستخدم لـ Free ويُلغي الاشتراك
```

---

### T11 — تحديث getPlanPrices() و config/billing.php إلى USD-first

| الحقل | القيمة |
|-------|--------|
| **ID** | T11 |
| **العنوان** | Update billing config + getPlanPrices() to USD-first |
| **الأولوية** | 🔴 Critical |
| **الملفات** | `config/billing.php` · `app/Modules/Billing/Services/SubscriptionService.php` |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | لا يوجد |

**في `config/billing.php`:**

```php
'plans' => [
    'pro' => [
        'label'    => 'الاحترافي',
        'monthly'  => ['price' => '17',  'currency' => 'USD', 'sar_equiv' => '64',  'jod_equiv' => '12',  'ils_equiv' => '63'],
        'annual'   => ['price' => '13',  'currency' => 'USD', 'sar_equiv' => '49',  'jod_equiv' => '9',   'ils_equiv' => '48'],
        'founder_monthly' => ['price' => '10', 'currency' => 'USD'],
        'founder_annual'  => ['price' => '8',  'currency' => 'USD'],
    ],
    'business' => [
        'label'    => 'الأعمال',
        'monthly'  => ['price' => '45',  'currency' => 'USD', 'sar_equiv' => '169', 'jod_equiv' => '32',  'ils_equiv' => '167'],
        'annual'   => ['price' => '34',  'currency' => 'USD', 'sar_equiv' => '127', 'jod_equiv' => '24',  'ils_equiv' => '126'],
        'founder_monthly' => ['price' => '26', 'currency' => 'USD'],
        'founder_annual'  => ['price' => '21', 'currency' => 'USD'],
    ],
],
```

**في `SubscriptionService::getPlanPrices()`:**

```php
public function getPlanPrices(): array
{
    return config('billing.plans', []);
}
```

**Acceptance Criteria:**

```
✅ AC11.1 — config('billing.plans.pro.monthly.price') === '17'
✅ AC11.2 — config('billing.plans.pro.monthly.currency') === 'USD'
✅ AC11.3 — config('billing.plans.business.monthly.price') === '45'
✅ AC11.4 — $service->getPlanPrices() يُرجع بنية USD-first
✅ AC11.5 — القيمة القديمة '99 SAR' لا تظهر في أي مكان
```

---

---

## EPIC 4 — صفحات التسعير والفوترة

> **الهدف:** جميع صفحات التسعير تعرض أرقاماً صحيحة وأسعاراً بالدولار وأزرار تعمل
> **القيمة التجارية:** العميل المحتمل يصل للصفحة → يرى سعراً واضحاً → يضغط → يتواصل → يدفع
> **معيار النجاح:** صفر تناقض بين /pricing و /billing/upgrade وفق PRICING-SOURCE-OF-TRUTH.md

---

### T12 — إصلاح billing/upgrade.blade.php

| الحقل | القيمة |
|-------|--------|
| **ID** | T12 |
| **العنوان** | Fix billing/upgrade.blade.php — USD prices + correct limits text |
| **الأولوية** | 🔴 Critical |
| **الملف** | `resources/views/billing/upgrade.blade.php` |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | T11 |

**المشاكل الحالية في الملف:**

| السطر | الحالي | المطلوب |
|-------|--------|---------|
| 41 | `$planPrices['pro']['price'] ?? '99'` | `$planPrices['pro']['monthly']['price'] ?? '17'` |
| 43 | `$planPrices['pro']['currency'] ?? 'SAR'` | `'USD'` |
| 49 | "حتى 10 مشاريع" | "مشاريع غير محدودة" |
| 53 | "500 معاملة / شهر" | "1,000 معاملة / شهر" |
| 89 | `$planPrices['business']['price'] ?? '299'` | `$planPrices['business']['monthly']['price'] ?? '45'` |
| 91 | `$planPrices['business']['currency'] ?? 'SAR'` | `'USD'` |

**Subtasks:**

```
[ ] ST12.1 — إصلاح عرض سعر Pro: "$17 / شهر" بدلاً من "99 SAR"
[ ] ST12.2 — إضافة سعر سنوي Pro: "$13 / شهر إذا دُفع سنوياً"
[ ] ST12.3 — إضافة معادلات: "≈ 64 SAR ≈ 12 JOD ≈ 63 ILS"
[ ] ST12.4 — إصلاح نص "10 مشاريع" → "مشاريع غير محدودة"
[ ] ST12.5 — إصلاح نص "500 معاملة" → "1,000 معاملة / شهر"
[ ] ST12.6 — إصلاح نفس الأخطاء لبطاقة Business ($45, "مشاريع غير محدودة")
[ ] ST12.7 — إضافة disclaimer: "الفوترة بالدولار · المعادلات تقديرية"
[ ] ST12.8 — التأكد من WhatsApp CTA يتضمن الخطة والسعر في الرسالة:
    "مرحباً، أريد الاشتراك في خطة Pro ($17/شهر) - حسابي: {email}"
```

**Acceptance Criteria:**

```
✅ AC12.1 — سعر Pro يظهر "$17 / شهر"
✅ AC12.2 — سعر Pro السنوي يظهر "$13 / شهر" (إذا دُفع سنوياً)
✅ AC12.3 — معادل SAR يظهر "≈ 64 ريال"
✅ AC12.4 — نص الميزات: "مشاريع غير محدودة" ✅ (لا "10 مشاريع")
✅ AC12.5 — نص الميزات: "1,000 معاملة / شهر" ✅ (لا "500 معاملة")
✅ AC12.6 — سعر Business يظهر "$45 / شهر"
✅ AC12.7 — WhatsApp CTA يعمل (href موجود)
✅ AC12.8 — disclaimer العملة موجود
✅ AC12.9 — الصفحة لا تُظهر "99 SAR" أو "299 SAR" في أي مكان
```

---

### T13 — إصلاح marketing/pricing.blade.php

| الحقل | القيمة |
|-------|--------|
| **ID** | T13 |
| **العنوان** | Fix /pricing page — CTAs + text corrections + "14-day trial" removal |
| **الأولوية** | 🔴 Critical |
| **الملف** | `resources/views/marketing/pricing.blade.php` |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | لا يوجد |

**المشاكل الحالية:**

| السطر | المشكلة | الإصلاح |
|-------|---------|---------|
| 61 | "حتى مشروعَين نشطَين" | "حتى 3 مشاريع نشطة" |
| 113 | "500 معاملة شهرياً" (Pro) | "1,000 معاملة شهرياً" |
| 84 | `href="#"` (CTA Starter) | `href="{{ auth()->check() ? route('dashboard') : route('register') }}"` |
| 136 | `href="#"` (CTA Pro) | `href="{{ auth()->check() ? route('billing.upgrade') : route('register') }}"` |
| 179 | `href="#"` (CTA Business) | `href="{{ auth()->check() ? route('billing.upgrade') : route('register') }}"` |
| 464 | "ابدأ تجربتك المجانية لمدة 14 يوماً" | "ابدأ مجاناً — لا بطاقة ائتمان، لا التزامات" |

**Subtasks:**

```
[ ] ST13.1 — إصلاح نص "مشروعَين" → "3 مشاريع نشطة" (Starter features)
[ ] ST13.2 — إصلاح نص "500 معاملة" → "1,000 معاملة" (Pro features)
[ ] ST13.3 — إصلاح CTA Starter (href="#" → route register/dashboard)
[ ] ST13.4 — إصلاح CTA Pro (href="#" → route billing.upgrade/register)
[ ] ST13.5 — إصلاح CTA Business (href="#" → route billing.upgrade/register)
[ ] ST13.6 — إزالة "14 يوماً trial" من الـ Hero section و footer CTA
[ ] ST13.7 — إصلاح نص آخر الصفحة: "انضم إلى آلاف المستقلين..."
            (لا نُعلن آلاف ونحن في البداية — نُزيل هذا أو نُعدّله)
```

**Acceptance Criteria:**

```
✅ AC13.1 — Starter CTA يحوّل لـ /register للزوار، لـ /dashboard للمسجّلين
✅ AC13.2 — Pro CTA يحوّل لـ /register للزوار، لـ /billing/upgrade للمسجّلين
✅ AC13.3 — Business CTA نفس Pro
✅ AC13.4 — نص "مشروعَين" غير موجود في الصفحة
✅ AC13.5 — نص "500 معاملة" غير موجود في بطاقة Pro
✅ AC13.6 — لا يوجد "14 يوماً" أو "free trial" في أي مكان
✅ AC13.7 — "ابدأ مجاناً" يعمل كـ CTA واضح
✅ AC13.8 — Inspect HTML: href="#" لا توجد على أزرار الخطط
```

---

---

## EPIC 5 — ضمان الجودة والإطلاق

> **الهدف:** التأكد من عمل جميع التدفقات بشكل صحيح قبل الإطلاق
> **القيمة التجارية:** خطأ في الإطلاق أمام أول عميل = فقدان الثقة للأبد
> **معيار النجاح:** 100% من Acceptance Criteria تمرّ في اليوم 7

---

### T14 — اختبار E2E: تدفق Starter Limits

| الحقل | القيمة |
|-------|--------|
| **ID** | T14 |
| **العنوان** | E2E Test — Starter limits + upgrade prompts |
| **الأولوية** | 🔴 Critical |
| **التقدير** | 2 ساعات |
| **الاعتماديات** | T01, T02, T03, T04 |

**Scenarios:**

```
SC14.1 — Clients Limit
  Setup: مستخدم Free، 4 عملاء موجودين
  Action: إضافة عميل 5 → يُضاف بنجاح
  Action: إضافة عميل 6 → redirect + upgrade_prompt يظهر
  Expected: رسالة "وصلت للحد الأقصى (5 عملاء)"

SC14.2 — Projects Limit (was broken at 2)
  Setup: مستخدم Free، 2 مشاريع موجودة
  Action: إضافة مشروع 3 → يُضاف ✅ (لم يكن يعمل قبل الإصلاح)
  Action: إضافة مشروع 4 → redirect + upgrade_prompt
  Expected: رسالة "وصلت للحد الأقصى (3 مشاريع)"

SC14.3 — Invoices Limit (Monthly)
  Setup: مستخدم Free، 4 فواتير هذا الشهر
  Action: إنشاء فاتورة 5 → يُنشأ ✅
  Action: إنشاء فاتورة 6 → redirect + upgrade_prompt

SC14.4 — Quotes Limit (Monthly)
  Setup: مستخدم Free، 2 عروض أسعار هذا الشهر
  Action: إنشاء عرض 3 → يُنشأ ✅
  Action: إنشاء عرض 4 → redirect + upgrade_prompt

SC14.5 — Transactions Limit (Monthly)
  Setup: مستخدم Free، 49 معاملة هذا الشهر
  Action: إضافة معاملة 50 → تُضاف ✅
  Action: إضافة معاملة 51 → redirect + upgrade_prompt

SC14.6 — Upgrade Prompt Display
  Expected: الـ Prompt يظهر أعلى الصفحة التالية بعد الـ redirect
  Expected: رابط "ترقية الآن" يحوّل لـ /billing/upgrade
  Expected: زر X يُخفي الـ Prompt
```

**Acceptance Criteria:**

```
✅ AC14.1 → AC14.6: جميع السيناريوهات تمرّ بالنتيجة المتوقعة
✅ AC14.7 — المستخدم Pro لا يصطدم بأي حد في أي من الموارد أعلاه
```

---

### T15 — اختبار E2E: Feature Gates

| الحقل | القيمة |
|-------|--------|
| **ID** | T15 |
| **العنوان** | E2E Test — Feature gates smoke test |
| **الأولوية** | 🟡 High |
| **التقدير** | 1.5 ساعة |
| **الاعتماديات** | T05, T06, T07, T08, T09 |

**Scenarios:**

```
SC15.1 — Send Invoice Email Gate
  Free user: زر إرسال البريد معطّل ✅ · Tooltip يظهر ✅
  Pro user: الزر يعمل ✅

SC15.2 — Export Gate
  Free user: أزرار التصدير معطّلة ✅
  Pro user: التصدير يعمل ✅

SC15.3 — Wallets Gate
  Free user: /wallets يُعيد redirect لـ /billing/upgrade ✅
  Pro user: /wallets يعمل ✅

SC15.4 — Advanced Reports Gate
  Free user: Blur + CTA يظهر ✅
  Pro user: التقارير تظهر ✅

SC15.5 — Plan Badge
  Free user: "ترقّ للـ Pro" يظهر في Sidebar ✅
  Pro user: Badge "⚡ Pro" يظهر ✅
  Business user: Badge "⚡ Business" يظهر ✅
```

---

### T16 — اختبار E2E: Admin Activation Flow

| الحقل | القيمة |
|-------|--------|
| **ID** | T16 |
| **العنوان** | E2E Test — Admin activates plan → user sees change immediately |
| **الأولوية** | 🔴 Critical |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | T10 |

**Scenario:**

```
1. Admin يفتح Filament → Users → يجد user@test.com
2. يضغط "تفعيل خطة"
3. يختار Pro
4. يختار ends_at = اليوم + 30 يوم
5. يكتب notes: "اختبار - 64 SAR"
6. يضغط "تفعيل الاشتراك"

Expected:
✅ users.subscription_plan === 'pro'
✅ subscriptions record created: status=active, plan=pro
✅ subscriptions.ends_at = اليوم + 30 يوم
✅ subscriptions.provider_subscription_id === "اختبار - 64 SAR"
✅ Notification success ظهرت في Filament
✅ المستخدم يعيد تحميل التطبيق → Plan badge "⚡ Pro" يظهر
✅ حدود الـ Free لا تُطبَّق عليه
```

---

### T17 — اختبار E2E: صفحات التسعير التسويقية

| الحقل | القيمة |
|-------|--------|
| **ID** | T17 |
| **العنوان** | E2E Test — /pricing + /billing/upgrade accuracy |
| **الأولوية** | 🔴 Critical |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | T12, T13 |

**Checklist:**

```
/pricing (بدون تسجيل دخول):
✅ Starter CTA → /register
✅ Pro CTA → /register
✅ Business CTA → /register
✅ نص Starter: "3 مشاريع" (لا "مشروعَين")
✅ نص Pro: "1,000 معاملة" (لا "500 معاملة")
✅ لا يوجد "14 يوماً" أو "trial"

/pricing (بعد تسجيل دخول - Free user):
✅ Pro CTA → /billing/upgrade
✅ Business CTA → /billing/upgrade

/billing/upgrade:
✅ سعر Pro: "$17 / شهر"
✅ سعر Pro السنوي: "$13 / شهر"
✅ معادل: "≈ 64 SAR"
✅ نص: "مشاريع غير محدودة" (لا "10 مشاريع")
✅ نص: "1,000 معاملة / شهر" (لا "500 معاملة")
✅ سعر Business: "$45 / شهر"
✅ WhatsApp CTA يعمل
✅ لا يوجد "99 SAR" أو "299 SAR" في أي مكان
```

---

### T18 — تدقيق نهائي: PRICING-SOURCE-OF-TRUTH Compliance

| الحقل | القيمة |
|-------|--------|
| **ID** | T18 |
| **العنوان** | Final pricing audit vs PRICING-SOURCE-OF-TRUTH.md |
| **الأولوية** | 🔴 Critical |
| **التقدير** | 1 ساعة |
| **الاعتماديات** | جميع المهام السابقة |

```
[ ] Pro monthly price في الكود === $17 ✅
[ ] Pro annual price في الكود === $13 ✅
[ ] Business monthly price في الكود === $45 ✅
[ ] Business annual price في الكود === $34 ✅
[ ] Founder Pro monthly === $10 ✅
[ ] Founder Pro annual === $8 ✅
[ ] Founder Business monthly === $26 ✅
[ ] Founder Business annual === $21 ✅
[ ] Free maxProjects === 3 ✅
[ ] Pro maxProjects === ∞ ✅
[ ] Free maxTransactions === 50 ✅
[ ] Pro maxTransactions === 1,000 ✅
[ ] Free maxClients === 5 ✅
[ ] Free maxInvoices === 5 ✅
[ ] Free maxQuotes === 3 ✅
[ ] Free maxTeamMembers === 0 (additional) ✅
[ ] Pro maxTeamMembers === 1 (additional) ✅
[ ] Business maxTeamMembers === 9 (additional) ✅
[ ] لا يوجد "$27" أو "$67" في أي ملف ✅
[ ] لا يوجد "99 SAR" أو "299 SAR" في أي View ✅
```

---

---

## Board Columns

### 📥 BACKLOG
> المهام المعروفة ولم تبدأ بعد

```
- T09  Gate: advanced_reports (الأدنى أولوية في E2)
- T17  E2E: صفحات التسعير (يتبع T12 و T13)
- T18  تدقيق نهائي PRICING-SOURCE-OF-TRUTH
```

---

### 📋 TODO (جاهزة للبدء)
> مهام بدون اعتماديات أو اعتمادياتها اكتملت

```
- T01  Fix SubscriptionPlan Enum          ← يبدأ اليوم 1
- T05  Add Plan Badge in Sidebar          ← يبدأ اليوم 1 (مستقل عن T01-T04)
- T11  Update billing config + getPlanPrices ← يبدأ اليوم 4 (مستقل)
- T13  Fix marketing/pricing.blade.php    ← يبدأ اليوم 5 (مستقل)
```

---

### 🔄 IN PROGRESS
> لا شيء حالياً — Sprint لم يبدأ

---

### 👀 REVIEW
> مكتملة وتنتظر مراجعة الكود

---

### 🧪 TESTING
> كود مراجَع، تحت الاختبار

---

### ✅ DONE

```
- PRICING-SOURCE-OF-TRUTH.md ✅
- PRICING-MVP-LAUNCH-SCOPE.md ✅
- PRICING-IMPLEMENTATION-PLAN.md ✅
- COMMERCIAL-PRICING-GUIDE.md ✅
```

---

---

## خريطة الأيام

```
╔═══════════════════════════════════════════════════════════════╗
║                 Sprint-01 — خريطة الأيام                      ║
╠══════════╦═══════════════════════════════════════════════════╣
║ اليوم    ║ المهام                                              ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 1    ║ T01 — Fix SubscriptionPlan Enum (2h)              ║
║          ║ T02 — Extend CheckSubscriptionLimits (2h)          ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 2    ║ T03 — Wire middleware to routes (1h)               ║
║          ║ T04 — Upgrade Prompt component + layout (2h)       ║
║          ║ T05 — Plan Badge in Sidebar (1h)                   ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 3    ║ T06 — Gate: send_invoice_email (2h)               ║
║          ║ T07 — Gate: export_data in Reports (1.5h)          ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 4    ║ T08 — Gate: wallets (1.5h)                        ║
║          ║ T09 — Gate: advanced_reports (1h)                  ║
║          ║ T11 — Update billing config to USD (1h)            ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 5    ║ T10 — activatePlan Admin Action (3h)              ║
║          ║ T12 — Fix billing/upgrade.blade.php (2h) [يبدأ]  ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 6    ║ T12 — Fix billing/upgrade.blade.php [يكتمل] (1h) ║
║          ║ T13 — Fix marketing/pricing.blade.php (2h)         ║
╠══════════╬═══════════════════════════════════════════════════╣
║ يوم 7    ║ T14 — E2E: Starter Limits (2h)                    ║
║ (QA)     ║ T15 — E2E: Feature Gates (1.5h)                   ║
║          ║ T16 — E2E: Admin Activation (1h)                   ║
║          ║ T17 — E2E: Pricing Pages (1h)                      ║
║          ║ T18 — Final Pricing Audit (1h) 🚀 LAUNCH          ║
╚══════════╩═══════════════════════════════════════════════════╝

المجموع: ~29 ساعة خلال 7 أيام
```

---

### الاعتماديات بين المهام

```
T01 (Enum)
  └── T02 (Middleware)
       └── T03 (Routes)
            └── T04 (Prompt Component)
                 └── T14 (E2E Limits Test)

T01 (Enum can())
  └── T06 (Gate: email)
  └── T07 (Gate: export)
  └── T08 (Gate: wallets)
  └── T09 (Gate: reports)
       └── T15 (E2E Gates Test)

T01 (Enum)
  └── T05 (Plan Badge)
       └── T15 (E2E Gates Test)

T11 (Config USD)
  └── T12 (Upgrade Page)
       └── T17 (E2E Pricing Test)

T10 (Admin Action)
  └── T16 (E2E Admin Test)

T13 (Pricing Page)
  └── T17 (E2E Pricing Test)

[T14 + T15 + T16 + T17]
  └── T18 (Final Audit) → 🚀 LAUNCH
```

---

---

## Launch Readiness Checklists

### ✅ Development Checklist

```
EPIC 1 — Subscription Limits
[ ] T01: SubscriptionPlan.php — maxProjects Free=3, Pro=∞
[ ] T01: SubscriptionPlan.php — maxTransactions Pro=1000
[ ] T01: SubscriptionPlan.php — maxClients(), maxInvoices(), maxQuotes() added
[ ] T01: SubscriptionPlan.php — maxTeamMembers(), maxStorageMB() added
[ ] T01: SubscriptionPlan.php — can(string $gate) method added (29 gates)
[ ] T02: CheckSubscriptionLimits — clients/invoices/quotes cases added
[ ] T02: CheckSubscriptionLimits — hint texts corrected
[ ] T03: crm.php routes/clients.store → middleware('subscription:clients')
[ ] T03: web.php routes/invoices.store → middleware('subscription:invoices')
[ ] T03: web.php routes/quotes.store  → middleware('subscription:quotes')
[ ] T04: components/upgrade-prompt.blade.php created
[ ] T04: app.blade.php includes <x-upgrade-prompt /> before <main>

EPIC 2 — Feature Gates
[ ] T05: Sidebar shows Plan Badge (Pro/Business) or "ترقّ للـ Pro" (Free)
[ ] T06: Invoice send-email action gated — locked UI for Free
[ ] T07: Export data gated — locked UI for Free
[ ] T08: /wallets redirects Free users to /billing/upgrade
[ ] T09: Advanced reports show Blur + CTA for Free

EPIC 3 — Admin Flow
[ ] T10: UserResource has "تفعيل خطة" Action with plan/ends_at/notes form
[ ] T10: Action updates subscription_plan on user + creates subscription record
[ ] T11: config/billing.php updated to USD-first structure
[ ] T11: getPlanPrices() returns USD config

EPIC 4 — Pages
[ ] T12: /billing/upgrade shows "$17/شهر" for Pro (not "99 SAR")
[ ] T12: /billing/upgrade shows "مشاريع غير محدودة" (not "10 مشاريع")
[ ] T12: /billing/upgrade shows "1,000 معاملة" (not "500 معاملة")
[ ] T12: /billing/upgrade shows "$45/شهر" for Business (not "299 SAR")
[ ] T13: /pricing CTA Starter → /register (not href="#")
[ ] T13: /pricing CTA Pro → /register or /billing/upgrade
[ ] T13: /pricing text "3 مشاريع" (not "مشروعَين")
[ ] T13: /pricing text Pro "1,000 معاملة" (not "500 معاملة")
[ ] T13: "14-day trial" text removed
```

---

### 🧪 QA Checklist

```
Starter Flow
[ ] مستخدم Free أضاف 5 عملاء → العميل السادس يُحجب
[ ] مستخدم Free أضاف 3 مشاريع → المشروع الرابع يُحجب (لا الثالث!)
[ ] مستخدم Free أضاف 5 فواتير/شهر → الفاتورة السادسة تُحجب
[ ] مستخدم Free أضاف 3 عروض/شهر → العرض الرابع يُحجب
[ ] مستخدم Free وصل 50 معاملة → المعاملة 51 تُحجب
[ ] Upgrade Prompt يظهر بعد كل حجب
[ ] زر "ترقية الآن" في Prompt يحوّل لـ /billing/upgrade
[ ] زر X في Prompt يُخفيه

Pro Flow
[ ] مستخدم Pro لا يصطدم بحد في أي مورد
[ ] مستخدم Pro يستطيع إرسال فاتورة بالبريد
[ ] مستخدم Pro يستطيع التصدير
[ ] مستخدم Pro يستطيع الوصول لـ /wallets
[ ] مستخدم Pro يستطيع رؤية التقارير المتقدمة
[ ] Plan Badge "⚡ Pro" يظهر في Sidebar

Admin Flow
[ ] Admin يجد "تفعيل خطة" في UserResource → Users table
[ ] Modal يفتح ويعرض (Plan / ends_at / notes)
[ ] تفعيل Pro → user.subscription_plan === 'pro'
[ ] تفعيل Pro → subscription record created
[ ] تفعيل Pro → user يرى badge "⚡ Pro"
[ ] تفعيل Pro → حدود Free لم تعد تنطبق

Pages
[ ] /pricing: 3 CTAs تعمل (لا href="#")
[ ] /pricing: "3 مشاريع" لـ Starter
[ ] /pricing: "1,000 معاملة" لـ Pro
[ ] /pricing: لا يوجد "14 يوماً"
[ ] /billing/upgrade: "$17" للـ Pro
[ ] /billing/upgrade: "1,000 معاملة"
[ ] /billing/upgrade: "مشاريع غير محدودة"
[ ] /billing/upgrade: WhatsApp link يعمل
```

---

### 💰 Pricing Checklist (vs SOURCE-OF-TRUTH)

```
[ ] Pro monthly = $17 — يظهر في /billing/upgrade ✓
[ ] Pro annual  = $13 — يظهر في /billing/upgrade ✓
[ ] Business monthly = $45 — يظهر في /billing/upgrade ✓
[ ] Business annual  = $34 — يظهر في /billing/upgrade ✓
[ ] Founder Pro monthly = $10 ← يُعلَن عند الإطلاق
[ ] Founder Pro annual  = $8  ← يُعلَن عند الإطلاق
[ ] SAR equivalent Pro monthly ≈ 64 ← يظهر
[ ] SAR equivalent Business monthly ≈ 169 ← يظهر
[ ] Free maxClients = 5 ← مطبّق في الكود
[ ] Free maxProjects = 3 ← مطبّق في الكود
[ ] Free maxInvoices/month = 5 ← مطبّق في الكود
[ ] Free maxQuotes/month = 3 ← مطبّق في الكود
[ ] Free maxTransactions/month = 50 ← مطبّق في الكود
[ ] Pro maxTransactions/month = 1,000 ← مطبّق في الكود
[ ] لا يوجد $27 في أي ملف: grep -r "\$27" resources/ ← 0 results ✓
[ ] لا يوجد $67 في أي ملف: grep -r "\$67" resources/ ← 0 results ✓
[ ] لا يوجد 99 SAR في أي View: grep -r "99.*SAR\|SAR.*99" resources/ ← 0 results ✓
```

---

### 🔧 Admin Checklist

```
[ ] Filament Admin يعمل (php artisan filament:check)
[ ] UserResource → Users table تُظهر عمود subscription_plan
[ ] UserResource → "تفعيل خطة" Action موجودة في Actions
[ ] UserResource → "إعادة الضبط" (resetPlan) Action موجودة
[ ] SubscriptionResource يُظهر الاشتراكات النشطة
[ ] Admin يستطيع البحث عن مستخدم بالإيميل
[ ] Google Sheet/Notion جاهز لتتبع الدفعات اليدوية:
    [الاسم | الإيميل | الخطة | تاريخ التفعيل | تاريخ الانتهاء | ملاحظات]
[ ] رقم WhatsApp الـ owner_whatsapp مضبوط في .env
```

---

### 🚀 Soft Launch Checklist (يوم 7)

```
قبل الإطلاق
[ ] php artisan config:cache — لا أخطاء
[ ] php artisan route:list — لا أخطاء
[ ] php artisan view:cache — لا أخطاء
[ ] صفحة /pricing تفتح بدون أخطاء
[ ] صفحة /billing/upgrade تفتح بدون أخطاء
[ ] Admin Panel يفتح بدون أخطاء
[ ] WhatsApp رقم الـ owner نشط ومُراقَب

يوم الإطلاق
[ ] إرسال announcement للشبكة الشخصية
[ ] Admin مستيقظ ومتاح للرد على WhatsApp
[ ] Google Sheet مفتوح وجاهز
[ ] رسالة ترحيب مكتوبة وجاهزة للإرسال بعد كل تفعيل

بعد أول عميل
[ ] تأكيد وصول الدفع
[ ] تفعيل الخطة من Admin Panel
[ ] إرسال رسالة ترحيب شخصية
[ ] تسجيل في Google Sheet
[ ] مراجعة أي أخطاء في logs
```

---

---

## Risk Register

### المخاطر التقنية

| # | الخطر | الاحتمال | الأثر | الخطورة الكلية | التخفيف |
|---|-------|---------|-------|----------------|---------|
| R01 | `can()` method تُرجع قيمة خاطئة لـ gate غير موجود → `default: false` | منخفض | عالٍ | 🟡 متوسط | `default => false` آمن — الميزة تُحجب بدلاً من أن تُفتح |
| R02 | User model ليس له `clients()` relationship مباشرة (CRM في module منفصل) | متوسط | عالٍ | 🔴 عالٍ | فحص User model قبل T02. قد يكون `$user->clients()` عبر CRM module |
| R03 | `invoices()` أو `quotes()` على User model غير موجودة أو تُرجع نتيجة مختلفة | متوسط | عالٍ | 🔴 عالٍ | اختبار في Tinker: `auth()->user()->invoices()->count()` قبل T02 |
| R04 | CRM routes في crm.php تُحمَّل عبر `CRMServiceProvider` — قد لا يطبّق auth middleware | منخفض | متوسط | 🟡 متوسط | `subscription` middleware يتحقق من `$request->user()` ويمرّر بدون user |
| R05 | activatePlan Action تُخفَق بسبب `updateOrCreate` conflict (T10) | منخفض | متوسط | 🟡 متوسط | اختبار Manual: Admin يفعّل نفس المستخدم مرتين |
| R06 | Livewire components تُرسل POST مباشرة لا عبر الـ routes المُسجَّلة | متوسط | متوسط | 🟡 متوسط | فحص QuoteController و InvoiceController إذا كانت تعمل مع Livewire |
| R07 | App.blade.php لا تدعم Alpine.js لزر X في الـ Prompt | منخفض | منخفض | 🟢 منخفض | Alpine.js مستخدم بالفعل في الـ layout (`x-data="{ sidebarOpen: false }"`) |

---

### المخاطر التجارية

| # | الخطر | الاحتمال | الأثر | الخطورة | التخفيف |
|---|-------|---------|-------|---------|---------|
| R08 | مستخدمون حاليون عندهم أكثر من 5 عملاء على Free — سيصطدمون بالحد فجأة | عالٍ | عالٍ | 🔴 حرج | **قبل رفع الكود:** مراجعة DB — كم مستخدم عنده > 5 عملاء. إذا وُجد: Grandfather Policy |
| R09 | مستخدمون حاليون عندهم > 3 مشاريع على Free | متوسط | عالٍ | 🔴 حرج | نفس R08 — يجب audit قبل الرفع |
| R10 | أول عميل يدفع ولا يجد Admin متاحاً لتفعيل الخطة فوراً | متوسط | عالٍ | 🔴 حرج | Admin Response Policy: الرد على WhatsApp خلال 2 ساعة — خارج أوقات العمل: رسالة آلية |
| R11 | رسالة WhatsApp لطلب الترقية لا تتضمن بيانات كافية للتحقق | متوسط | متوسط | 🟡 متوسط | تضمين email في WhatsApp message template تلقائياً |
| R12 | العميل يدفع ثم يُلغي — يتوقع استرداداً وسياسة غير واضحة | منخفض | متوسط | 🟡 متوسط | إضافة جملة "لا استرداد" في صفحة الترقية قبل الإطلاق |

---

### Launch Blockers (يجب حلها قبل الإطلاق)

> هذه مخاطر إذا لم تُحَل تمنع الإطلاق

| # | Blocker | الحل المطلوب |
|---|---------|-------------|
| B01 | مستخدمون حاليون عندهم > الحدود الجديدة | Grandfather query قبل رفع الكود (R08, R09) |
| B02 | `clients()` على User model غير موجودة | فحص قبل T02 — بديل: CRM module ClientController::count() |
| B03 | رقم WhatsApp غير مضبوط في .env | `OWNER_WHATSAPP=` في .env الإنتاج |
| B04 | أسعار `$27/$67` الخاطئة لا تزال في أي ملف | grep -r "\$27\|\$67" resources/ — 0 results |
| B05 | Admin Panel لا يعمل في الإنتاج | اختبار `/admin` قبل الإطلاق |

---

### Grandfather Query (تُنفَّذ قبل رفع الكود — B01/B02)

```sql
-- عدد مستخدمين Free عندهم > 5 عملاء
SELECT u.id, u.email, COUNT(c.id) as client_count
FROM users u
JOIN clients c ON c.user_id = u.id AND c.deleted_at IS NULL
WHERE u.subscription_plan = 'free'
GROUP BY u.id, u.email
HAVING client_count > 5
ORDER BY client_count DESC;

-- عدد مستخدمين Free عندهم > 3 مشاريع
SELECT u.id, u.email, COUNT(p.id) as project_count
FROM users u
JOIN projects p ON p.user_id = u.id
WHERE u.subscription_plan = 'free'
  AND p.status NOT IN ('archived', 'completed')
GROUP BY u.id, u.email
HAVING project_count > 3;
```

**إذا وُجدت نتائج:** منح هؤلاء المستخدمين تمديداً مؤقتاً أو إبلاغهم قبل التطبيق.

---

---

## ملاحق للمطور

### متغيرات البيئة المطلوبة (.env)

```env
# Billing — WhatsApp للتفعيل اليدوي
OWNER_WHATSAPP=970501234567       # رقم دولي بدون +

# Billing — الأسعار (تُستخدم فقط كـ fallback)
BILLING_PRICE_PRO_MONTHLY=17
BILLING_PRICE_PRO_ANNUAL=13
BILLING_PRICE_BUSINESS_MONTHLY=45
BILLING_PRICE_BUSINESS_ANNUAL=34
BILLING_CURRENCY=USD
```

---

### أوامر التحقق السريع

```bash
# تحقق من الأسعار الموجودة في Views
grep -rn "99\|299\|SAR.*ر\|27\$\|67\$" resources/views/billing/ --include="*.blade.php"

# تحقق من CTAs المكسورة
grep -rn 'href="#"' resources/views/marketing/pricing.blade.php

# تحقق من قيم Enum
php artisan tinker --execute="echo App\Support\Enums\SubscriptionPlan::Free->maxProjects();"
php artisan tinker --execute="echo App\Support\Enums\SubscriptionPlan::Pro->maxTransactionsPerMonth();"

# تحقق من Middleware على Routes
php artisan route:list | grep -E "clients.*store|invoices.*store|quotes.*store"

# تحقق من Billing Config
php artisan tinker --execute="print_r(config('billing.plans'));"
```

---

### قواعد تسمية الـ session flash

```php
// upgrade_prompt — يُظهر الـ Upgrade Prompt component
session()->flash('upgrade_prompt', [
    'resource' => 'clients|projects|invoices|quotes|transactions',
    'message'  => 'النص الرئيسي للرسالة',
    'hint'     => 'النص الإرشادي الثانوي (اختياري)',
]);

// upgrade_reason — يُظهر في /billing/upgrade
redirect()->route('billing.upgrade')->with('upgrade_reason', '...');
```

---

*Sprint-01 Board — جاهز للنسخ لـ GitHub Projects / Notion / Trello / Jira*
*المرجع: `docs/PRICING-SOURCE-OF-TRUTH.md` · الإصدار: 1.0 · التاريخ: 24 يونيو 2026*
