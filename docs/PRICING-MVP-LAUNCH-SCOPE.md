# PRICING-MVP-LAUNCH-SCOPE.md
## نطاق إطلاق التسعير — الحد الأدنى القابل للتسويق (MVP)
**الإصدار:** 1.0 | **التاريخ:** يونيو 2026 | **الجمهور:** فريق التطوير والمؤسسون

---

## ⚡ الحقيقة المفاجئة

**البنية التحتية موجودة بالفعل بنسبة 70%.**

قبل قراءة أي شيء آخر: Darahum لديها بالفعل:
- ✅ `SubscriptionPlan` Enum (Free/Pro/Business)
- ✅ `CheckSubscriptionLimits` Middleware (للمشاريع والمعاملات)
- ✅ `SubscriptionService::activatePlan()` 
- ✅ `SubscriptionResource` في Filament — يتيح إنشاء وتعديل الاشتراكات
- ✅ `UserResource` مع حقل `subscription_plan` قابل للتعديل مباشرة من الAdmin
- ✅ صفحة `billing/upgrade.blade.php` مع تدفق WhatsApp
- ✅ صفحة `billing/index.blade.php` مع بانر الخطة الحالية
- ✅ صفحة `marketing/pricing.blade.php` مع بطاقات الخطط

الفجوة الحقيقية بين الحالة الراهنة وإطلاق MVP هي **أيام، ليست أسابيع.**

---

## 1. الملخص التنفيذي

### ما هو مُدرج في MVP

| المكوّن | الوضع الحالي | المطلوب |
|---------|-------------|---------|
| خطط الاشتراك (Starter/Pro/Business) | ✅ موجودة | تصحيح الأرقام فقط |
| حدود المشاريع والمعاملات | ✅ موجودة | تصحيح القيم في Enum |
| حدود العملاء والفواتير والعروض | ❌ غائبة | إضافة 3 methods + 3 checks |
| البوابات (Feature Gates) — 6 أساسية | ❌ غائبة جزئياً | بناء مبسّط في Enum |
| صفحة الترقية (WhatsApp flow) | ✅ موجودة | تصحيح الأسعار والأرقام |
| التفعيل اليدوي من Admin | ✅ موجود | تحسين طفيف |
| صفحة التسعير التسويقية `/pricing` | ✅ موجودة | تصحيح المحتوى |
| شارة الخطة في الواجهة | ❌ غائبة | إضافة بسيطة في layout |

### ما هو مستبعد من MVP

**كل ما يلي يُبنى بعد أول 20 عميل مدفوع:**

- دورات الفوترة السنوية/الشهرية (annual billing cycles)
- أتمتة مقاعد المؤسسين (founder seat automation)
- فترة السماح (grace period automation)
- جدول `founder_seats` / `plan_limit_overrides` / `subscription_audit_logs`
- حدود التخزين (storage limits)
- حدود أعضاء الفريق (team member limits)
- بوابة الدفع Togo (تأجيل حتى إصلاح bug الـ ByteString)
- الـ Webhooks والـ API وأتمتة المهام
- White Label والتقارير المتقدمة للمسؤولين
- نظام الإحالة (referral system)
- Subscription Analytics Dashboard
- مهام cron للانتهاء التلقائي للاشتراكات

### لماذا هذا الاستبعاد منطقي؟

المستخدمون الأوائل العشرون لن يكتشفوا غياب grace period أو founder automation. ما سيلاحظونه هو: هل الأسعار واضحة؟ هل يستطيعون الترقية؟ هل النظام يحجب الميزات بشكل صحيح؟ ركّز فقط على هذا.

---

## 2. مبادئ MVP

### 2.1 سفينة بسرعة — قاعدة 48 ساعة
أي ميزة تستغرق أكثر من 48 ساعة للبناء تُعاد تقييمها. إما تُبسَّط أو تؤجَّل.

**التطبيق:** Feature Gates تُبنى بـ 6 شروط `if` في Enum، ليس بـ Service كامل + Middleware + Exception + Blade directive.

### 2.2 تجنب الهندسة الزائدة — قاعدة "هل يعمل؟"
السؤال الوحيد: هل يمنع المستخدم من تجاوز الحد؟ إذا كانت الإجابة نعم، فالتنفيذ صحيح — بغض النظر عن أناقة الكود.

**التطبيق:** التحقق من الحدود يحدث في الـ Middleware الموجود فعلاً. لا نحتاج Service منفصلة للحدود.

### 2.3 حقّق التعلم بسرعة — قاعدة الملاحظة المبكرة
كل يوم تأخير في الإطلاق = يوم بلا بيانات حقيقية من مستخدمين حقيقيين.

**التطبيق:** أطلق بـ Admin يدوي (الوضع الحالي بالفعل). اجمع 20 عميل مدفوع. ثم آتمت.

### 2.4 أجّل التعقيد — قاعدة "الحاجة تسبق البناء"
لا تبنِ ما لم تُطلبه. المؤسسون الأوائل سيخبرونك بما يحتاجونه فعلاً.

**التطبيق:** لا جدول `founder_seats`. المؤسس الأول يُعيَّن يدوياً من Admin مع ملاحظة في حقل metadata.

---

## 3. Must Have — ما يجب بناؤه قبل الإطلاق

### 3.1 تصحيح أرقام الخطة في Enum 🔴 CRITICAL

**الوضع الحالي في `SubscriptionPlan.php`:**
```php
// مشاريع Free = 2  ← WRONG (يجب 3)
// مشاريع Pro = 10  ← WRONG (يجب ∞)
// معاملات Pro = 500 ← WRONG (يجب 1,000)
```

**التصحيح المطلوب:**
```php
public function maxProjects(): int {
    return match($this) {
        self::Free     => 3,           // ✅ تصحيح
        self::Pro      => PHP_INT_MAX, // ✅ تصحيح
        self::Business => PHP_INT_MAX,
    };
}

public function maxTransactionsPerMonth(): int {
    return match($this) {
        self::Free     => 50,
        self::Pro      => 1000,        // ✅ تصحيح
        self::Business => PHP_INT_MAX,
    };
}
```

- **المبرر التجاري:** صفحة التسعير تعرض أرقاماً مختلفة عن ما يطبّقه الكود. إطلاق مع هذا التناقض = مشكلة ثقة.
- **تأثير المستخدم:** مستخدم Free يصل للحد عند 2 مشاريع بدل 3.
- **التعقيد:** 30 دقيقة.

---

### 3.2 إضافة حدود العملاء والفواتير والعروض 🔴 CRITICAL

**إضافة إلى `SubscriptionPlan.php`:**
```php
public function maxClients(): int {
    return match($this) {
        self::Free     => 5,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxInvoicesPerMonth(): int {
    return match($this) {
        self::Free     => 5,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}

public function maxQuotesPerMonth(): int {
    return match($this) {
        self::Free     => 3,
        self::Pro      => PHP_INT_MAX,
        self::Business => PHP_INT_MAX,
    };
}
```

**إضافة إلى `CheckSubscriptionLimits.php`:**
```php
'clients'      => $this->checkClients($user, $plan),
'invoices'     => $this->checkInvoices($user, $plan),
'quotes'       => $this->checkQuotes($user, $plan),
```

- **المبرر التجاري:** خطة Free بدون حدود على العملاء = لا سبب للترقية.
- **تأثير المستخدم:** تشغيل محفّزات الترقية الحقيقية.
- **التعقيد:** 2-3 ساعات.

---

### 3.3 Feature Gates الستة الأساسية 🔴 CRITICAL

من الـ 29 بوابة في PRICING-IMPLEMENTATION-PLAN، احتاج MVP فقط إلى **6 بوابات** تمسّ قرار الشراء مباشرة:

**إضافة إلى `SubscriptionPlan.php`:**
```php
public function canSendInvoiceEmail(): bool {
    return $this !== self::Free;
}

public function canUseAdvancedReports(): bool {
    return $this !== self::Free;
    // canExport() و hasAdvancedReports() موجودان بالفعل
}

public function canUseWallets(): bool {
    return $this !== self::Free;
}

public function canUseMultiCurrency(): bool {
    return $this === self::Business;
}

public function canAccessApi(): bool {
    return $this === self::Business;
    // موجود بالفعل
}
```

**البوابات الموجودة بالفعل:**
- `canExport()` ✅
- `canAccessApi()` ✅
- `hasAdvancedReports()` ✅

**التطبيق في Blade:**
```blade
{{-- بدلاً من @feature directive معقّد --}}
@if(auth()->user()->currentPlan()->canSendInvoiceEmail())
    {{-- زر إرسال الإيميل --}}
@else
    <button onclick="window.location='{{ route('billing.upgrade') }}'"
            class="opacity-50 cursor-not-allowed">
        إرسال بالبريد
        <span class="text-xs bg-amber-100 text-amber-700 px-1 rounded">Pro</span>
    </button>
@endif
```

- **المبرر التجاري:** ميزات مقفلة = محفّز ترقية. بدون قفل = لا سبب للدفع.
- **تأثير المستخدم:** يرى المستخدم الميزة المحجوبة ورابط الترقية فوراً.
- **التعقيد:** 1-2 يوم (تطبيق الشروط في الـ Views المعنية).

---

### 3.4 شارة الخطة في الواجهة 🟡 HIGH

```blade
{{-- في layouts/app.blade.php أو sidebar --}}
@php $plan = auth()->user()->currentPlan(); @endphp
@if($plan !== \App\Support\Enums\SubscriptionPlan::Free)
<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full
    {{ $plan->value === 'pro' ? 'bg-brand-100 text-brand' : 'bg-purple-100 text-purple-700' }}">
    {{ $plan->label() }}
</span>
@endif
```

- **المبرر التجاري:** المستخدم المدفوع يجب أن يشعر بالفرق فوراً.
- **تأثير المستخدم:** تأكيد بصري أن الترقية نجحت.
- **التعقيد:** 30 دقيقة.

---

### 3.5 تصحيح محتوى صفحة التسعير التسويقية `/pricing` 🔴 CRITICAL

الصفحة الحالية تعرض:
- "حتى مشروعَين نشطَين" ← يجب "حتى 3 مشاريع"
- "500 معاملة / شهر" (في upgrade view) ← يجب "1,000 معاملة / شهر"
- الـ CTA buttons يجب أن تعمل (تحويل لـ `/billing/upgrade` أو `/register`)

**الإصلاح:** تحديث النصوص فقط. لا تعديل هيكلي.

- **المبرر التجاري:** تناقض بين الصفحة التسويقية والتطبيق الفعلي = فقدان الثقة.
- **تأثير المستخدم:** المستخدم يقرر بناءً على ما يرى.
- **التعقيد:** 1-2 ساعة.

---

### 3.6 صفحة الترقية `/billing/upgrade` — تصحيح الأسعار 🔴 CRITICAL

الصفحة موجودة وتعمل. تحتاج فقط:
1. تصحيح "500 معاملة" → "1,000 معاملة"
2. تصحيح "حتى 10 مشاريع" → "مشاريع غير محدودة"
3. تصحيح العملة: USD أساس مع معادل SAR/JOD/ILS

- **التعقيد:** 1 ساعة.

---

### 3.7 رسالة Upgrade Prompt عند الوصول للحد 🔴 CRITICAL

الـ Middleware الحالي يعمل لكن رسالة الـ session flash تقول "Upgrade إلى Pro تتيح لك حتى 10 مشاريع" ← خطأ.

**التصحيح:**
```php
'hint' => 'الترقية إلى Pro تتيح لك مشاريع غير محدودة.',
```

وإضافة component مشترك في layout لعرض الـ upgrade_prompt flash:
```blade
@if(session('upgrade_prompt'))
<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center justify-between">
    <p class="text-sm text-amber-800">{{ session('upgrade_prompt.message') }}</p>
    <a href="{{ route('billing.upgrade') }}"
       class="text-sm font-semibold text-amber-700 underline">ترقية الآن ←</a>
</div>
@endif
```

- **التعقيد:** 1 ساعة.

---

### 3.8 Admin: تفعيل الخطة اليدوي — تحسين Action 🟡 HIGH

الـ Admin يستطيع الآن تعديل `subscription_plan` مباشرة في UserResource. لكن هذا لا ينشئ سجل في جدول `subscriptions`.

**إضافة Action في UserResource:**
```php
Tables\Actions\Action::make('activatePlan')
    ->label('تفعيل خطة')
    ->icon('heroicon-o-rocket-launch')
    ->form([
        Forms\Components\Select::make('plan')
            ->label('الخطة')
            ->options([
                'pro'      => 'Pro — $27/شهر',
                'business' => 'Business — $67/شهر',
            ])->required(),
        Forms\Components\DatePicker::make('ends_at')
            ->label('تاريخ الانتهاء')
            ->default(now()->addMonth())
            ->required(),
        Forms\Components\TextInput::make('notes')
            ->label('ملاحظات (رقم إيصال / اسم العميل)')
            ->placeholder('مثال: تحويل بنكي - 150 SAR - يونيو 2026'),
    ])
    ->action(function (User $record, array $data, SubscriptionService $service): void {
        $subscription = $service->activatePlan($record, $data['plan']);
        $subscription->update(['ends_at' => $data['ends_at']]);
        // حفظ الملاحظة في provider_subscription_id مؤقتاً
        $subscription->update(['provider_subscription_id' => $data['notes']]);
        Notification::make()->title('تم تفعيل الخطة بنجاح')->success()->send();
    })
    ->visible(fn (User $record) => $record->subscription_plan === SubscriptionPlan::Free),
```

- **المبرر التجاري:** هذا هو قلب النظام. بدونه لا يوجد مسار لتحصيل المال.
- **التعقيد:** 2-3 ساعات.

---

## 4. Should Have — ما يأتي بعد الإطلاق

### 4.1 أتمتة مقاعد المؤسسين
**لماذا ينتظر:** الأوائل العشرون يُعيَّنون يدوياً. Admin يحتفظ بجدول Excel لتتبع المقاعد. عند الوصول لـ 80 مستخدم، نبني الأتمتة.

### 4.2 دورات الفوترة السنوية/الشهرية
**لماذا ينتظر:** الآن كل اشتراك = شهر واحد. Admin يجدّد يدوياً. التعقيد التقني للـ billing cycles يُبنى بعد إثبات الطلب.

### 4.3 فترة السماح (Grace Period)
**لماذا ينتظر:** إذا انتهى اشتراك مستخدم، Admin يجدّد. لا توجد حالات كافية بعد لتبرير الأتمتة.

### 4.4 Analytics Dashboard للمبيعات
**لماذا ينتظر:** بـ 20 عميل، جدول Google Sheets يكفي. Dashboard يُبنى عند الحاجة لـ scale.

### 4.5 صفحة Billing إضافة لسجل المدفوعات
**لماذا ينتظر:** المستخدمون الأوائل يعرفون أنهم دفعوا — لديهم الإيصال. سجل المدفوعات في الواجهة يأتي مع الأتمتة.

### 4.6 Onboarding مدفوع خاص
**لماذا ينتظر:** الـ Onboarding العام يعمل. Onboarding مخصص للمدفوعين يأتي بعد فهم ما يحتاجه المدفوعون فعلاً.

---

## 5. Nice To Have — للمستقبل

| الميزة | السبب في التأجيل |
|--------|-----------------|
| Webhook events | لا شريك يحتاجها الآن |
| API access | لا مطوّر يطلبها من المستخدمين الأوائل |
| White Label | ميزة Enterprise تأتي لاحقاً |
| Referral System | يُبنى عند وجود قاعدة مستخدمين |
| Advanced Audit Logs | تعقيد كبير، قيمة منخفضة الآن |
| Multi-currency UI | عرض المعادل نصياً يكفي للمرحلة الأولى |
| Subscription portal (Paddle-style) | WhatsApp يكفي لـ 20 عميل |
| Cron jobs للانتهاء التلقائي | Admin يدوي يكفي |

---

## 6. قائمة الميزات المؤجلة (Deferred Features)

### من PRICING-IMPLEMENTATION-PLAN.md — مؤجّل كاملاً:

| الميزة | المبرر |
|--------|--------|
| `founder_seats` table | Admin يحتفظ بـ Google Sheet. 100 مقعد = أيام/أسابيع، ليست دقائق. |
| `plan_limit_overrides` table | لا استثناءات حتى الآن. الكل يطبّق القواعد العامة. |
| `subscription_audit_logs` table | لا عملاء بعد. نبني السجل حين نحتاج تتبع التغييرات. |
| `pricing_configs` table | الأسعار ثابتة في config. لا حاجة لـ DB-driven pricing الآن. |
| `grace_period_ends_at` column | يُضاف عند بناء الأتمتة. |
| `billing_cycle` column | كل اشتراك = شهر. |
| `FeatureGateService` class | شرط `if` في Enum يكفي تماماً. |
| `RequireFeature` Middleware | الـ View check كافٍ للـ MVP. |
| `FeatureNotAvailableException` | Redirect بسيط يكفي. |
| `@feature` Blade directive | `@if($plan->canX())` أوضح وأسرع. |
| جميع الـ 23 بوابة المتبقية | 6 بوابات تغطي 90% من قرارات الترقية. |
| Cron job لانتهاء الاشتراكات | Admin يجدّد يدوياً. |
| حدود التخزين (storage_mb) | تتبع التخزين معقّد. يأجل حتى يُطلب. |
| حدود أعضاء الفريق | لا يوجد Team feature كامل بعد. |
| Annual billing toggle وظيفي | شهري فقط في البداية. |
| Filament: 5 صفحات Admin جديدة | `SubscriptionResource` الموجود يكفي. |
| Togo Payment Gateway | Bug قائم. Manual أولاً. |

---

## 7. نموذج الاشتراك المبسّط (MVP)

### 7.1 مسار المستخدم (User Flow)
```
المستخدم يصل للحد
    → Flash message: "وصلت للحد الأقصى، الترقية تفتح [X]"
    → زر "ترقية الآن" → /billing/upgrade
    
مستخدم يريد الترقية
    → /billing أو /billing/upgrade
    → يرى بطاقات الخطط مع الأسعار
    → يضغط "تواصل للترقية" → WhatsApp مع رسالة جاهزة
    
رسالة WhatsApp الجاهزة:
"مرحباً، اشتراكي: [email] - أريد الترقية لخطة [Pro/Business]"
```

### 7.2 مسار المشرف (Admin Flow)
```
1. يستقبل WhatsApp من العميل
2. يتأكد من وصول الدفع (تحويل / كاش)
3. يفتح Admin Panel → Users → يبحث عن المستخدم
4. يضغط Action "تفعيل خطة"
5. يختار الخطة وتاريخ الانتهاء (شهر من اليوم)
6. يكتب ملاحظة: "تحويل بنكي - 150 SAR"
7. يضغط "تفعيل" ← النظام يحدّث subscription_plan + ينشئ سجل subscription
8. يرسل رسالة شكر للمستخدم عبر WhatsApp
```

### 7.3 الشاشات المطلوبة

| الشاشة | الحالة | المطلوب |
|--------|--------|---------|
| `/pricing` (تسويقية) | ✅ موجودة | تصحيح الأرقام والـ CTAs |
| `/billing` | ✅ موجودة | تصحيح الأرقام |
| `/billing/upgrade` | ✅ موجودة | تصحيح الأرقام + العملة |
| Upgrade Prompt flash | ✅ موجودة جزئياً | إضافة component في layout |
| شارة الخطة في layout | ❌ غائبة | 30 دقيقة |
| Admin: تفعيل خطة Action | ⚠️ جزئي | إضافة Action كامل |

### 7.4 قاعدة البيانات المطلوبة

لا جداول جديدة. الجداول الموجودة تكفي:
- `users.subscription_plan` ✅ موجود
- `subscriptions` table ✅ موجود

الأعمدة الإضافية الوحيدة الضرورية:
```sql
-- اختيارية للـ MVP، لكن مفيدة للتتبع
ALTER TABLE subscriptions ADD COLUMN notes TEXT NULL;
-- أو استخدم provider_subscription_id لتخزين الملاحظات مؤقتاً
```

---

## 8. Feature Gates MVP

### المبدأ: بسيط يعمل > معقّد لا يُنجَز

بدلاً من `FeatureGateService` + Middleware + Exception + Blade directive:

```php
// في SubscriptionPlan.php — أضف فقط:
public function can(string $gate): bool {
    return match($gate) {
        'export'           => $this !== self::Free,
        'advanced_reports' => $this !== self::Free,
        'send_email'       => $this !== self::Free,
        'wallets'          => $this !== self::Free,
        'multi_currency'   => $this === self::Business,
        'api_access'       => $this === self::Business,
        default            => false,
    };
}
```

### كيف تتحقق من الوصول
```php
// في Controller
if (!$user->currentPlan()->can('export')) {
    return redirect()->route('billing.upgrade')
        ->with('upgrade_reason', 'التصدير متاح في خطة Pro فأعلى');
}

// في Blade
@if(auth()->user()->currentPlan()->can('send_email'))
    <button>إرسال بالبريد</button>
@else
    <div class="relative">
        <button disabled class="opacity-40">إرسال بالبريد</button>
        <a href="{{ route('billing.upgrade') }}"
           class="absolute inset-0 flex items-center justify-center bg-white/60 rounded">
            <span class="text-xs bg-amber-500 text-white px-2 py-0.5 rounded-full">Pro ⚡</span>
        </a>
    </div>
@endif
```

### كيف تمنع الاستغلال
- كل check يحدث في Controller أو Middleware (ليس فقط في View)
- الـ `CheckSubscriptionLimits` Middleware مسجّل على routes الـ `store`
- لا يعتمد على JavaScript أو Client-side فقط

### 6 بوابات MVP وتطبيقها

| البوابة | الإجراء عند المستخدم Free | الإجراء عند Pro/Business |
|---------|--------------------------|------------------------|
| `export` | رسالة "التصدير في Pro" + redirect | تصدير يعمل |
| `advanced_reports` | صفحة Reports تظهر مع Blur + CTA | التقارير تعمل |
| `send_email` | زر مع قفل + CTA | الزر يعمل |
| `wallets` | صفحة Wallets مع CTA | الوصول مفتوح |
| `multi_currency` | إخفاء الخيار أو CTA Business | متاح |
| `api_access` | صفحة API Settings مع CTA | متاح |

---

## 9. Usage Limits MVP

### الحدود الكاملة:

| المورد | Free | Pro | Business | النافذة |
|--------|------|-----|----------|---------|
| clients | 5 | ∞ | ∞ | total |
| projects | 3 | ∞ | ∞ | total active |
| invoices | 5 | ∞ | ∞ | monthly |
| quotes | 3 | ∞ | ∞ | monthly |
| transactions | 50 | 1,000 | ∞ | monthly |

**لا نُطبّق في MVP:** storage_mb, team_members (لا Feature كاملة لهم بعد).

### التحقق الخلفي (Backend Validation)
الـ Middleware الموجود `CheckSubscriptionLimits` + إضافة:
```php
'clients'  => $this->checkClients($user, $plan),
'invoices' => $this->checkInvoices($user, $plan),
'quotes'   => $this->checkQuotes($user, $plan),
```

وتسجيل الـ Middleware على Routes:
```php
// في web.php
Route::post('/clients', [ClientController::class, 'store'])
    ->middleware('subscription.limits:clients');
Route::post('/invoices', [InvoiceController::class, 'store'])
    ->middleware('subscription.limits:invoices');
Route::post('/quotes', [QuoteController::class, 'store'])
    ->middleware('subscription.limits:quotes');
```

### سلوك الواجهة (UI Behavior)
1. **عداد في رأس الصفحة:** "3/5 عملاء" للـ Free
2. **زر Disabled مع lock icon** عند الوصول للحد
3. **Flash message** مع رابط الترقية

### Upgrade Prompt
زر واحد موحّد يظهر في كل مكان:
```blade
{{-- _upgrade-prompt.blade.php --}}
@if(session('upgrade_prompt'))
<div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-center justify-between gap-4 mb-4">
    <div>
        <p class="text-sm font-medium text-amber-900">{{ session('upgrade_prompt.message') }}</p>
        <p class="text-xs text-amber-700 mt-0.5">{{ session('upgrade_prompt.hint') }}</p>
    </div>
    <a href="{{ route('billing.upgrade') }}"
       class="shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition">
        ترقية الآن
    </a>
</div>
@endif
```

---

## 10. Billing MVP — التدفق اليدوي الكامل

### الافتراضات
- لا بوابة دفع أوتوماتيكية (Togo معطّل بسبب ByteString bug)
- كل دفعة تمرّ عبر WhatsApp أو تحويل بنكي
- Admin يفعّل يدوياً

### تدفق طلب الترقية
```
[المستخدم] /billing/upgrade
    ↓ يضغط "تواصل للترقية — Pro"
    
[النظام] يفتح WhatsApp بالرسالة:
"مرحباً دراهم 👋
أريد الاشتراك في خطة Pro
الحساب: {{ auth()->user()->email }}
{{ auth()->user()->name }}"
    ↓

[Admin] يستقبل → يتأكد من الدفع → يفعّل من Admin Panel
    ↓

[النظام] subscription_plan يتحدث → Flash "تم تفعيل خطتك ✅"
```

### أسعار العرض في `/billing/upgrade`
```
Pro:       $27/شهر  ≈ 101 SAR  ≈ 19 JOD  ≈ 100 ILS
Business:  $67/شهر  ≈ 251 SAR  ≈ 48 JOD  ≈ 248 ILS
```

### عرض المدة
الـ MVP لا يتتبع monthly/annual. كل اشتراك = 30 يوم من تاريخ التفعيل.

---

## 11. Admin MVP — الشاشات الضرورية فقط

### ما هو موجود ويكفي:
- ✅ **UserResource** — تعديل subscription_plan مباشرة في Edit form
- ✅ **SubscriptionResource** — عرض وإنشاء وتعديل الاشتراكات
- ✅ **UserResource → resetPlan Action** — إلغاء الاشتراك وإرجاع لـ Free

### ما يجب إضافته:
- ⚠️ **UserResource → activatePlan Action** — تفعيل خطة مع تاريخ انتهاء وملاحظة (مفصّل في §3.8)

### ما يُحذف من القائمة (لا يُبنى الآن):
- ❌ Subscription Analytics Widget
- ❌ Revenue Dashboard
- ❌ Founder Seats Manager
- ❌ Plan Limit Overrides
- ❌ Audit Log Viewer

**المبدأ:** Admin يعمل بـ Filament الموجود + ملف Google Sheet للتتبع اليدوي.

---

## 12. Marketing MVP — `/pricing`

### الشاشة الحالية تعمل. تحتاج فقط:

**1. تصحيح المحتوى:**
```
الآن: "حتى مشروعَين" → يجب: "3 مشاريع نشطة"
الآن: "500 معاملة"   → يجب: "1,000 معاملة / شهر" (Pro)
```

**2. إصلاح CTAs:**
```blade
{{-- بدلاً من href="#" --}}
<a href="{{ auth()->check() ? route('billing.upgrade') : route('register') }}"
   class="btn-primary">
   {{ auth()->check() ? 'ترقية الآن' : 'ابدأ مجاناً' }}
</a>
```

**3. إضافة FAQ صغير (3 أسئلة):**
- هل يمكنني إلغاء في أي وقت؟
- كيف يتم الدفع؟
- هل هناك تجربة مجانية؟

**4. Social Proof placeholder (يُملأ فور أول عميل):**
```html
<!-- قسم: "انضمّ إلى أكثر من 50+ مستقلاً يستخدمون دراهم" -->
```

**ما لا يُبنى الآن:**
- ❌ مقارنة تفصيلية جدول كامل (موجودة بشكل كافٍ)
- ❌ Annual/Monthly toggle وظيفي (يظهر البصري، يُخفي الوظيفي)
- ❌ Calculator (ROI أو توفير)

---

## 13. Customer Success MVP

### قبل الإطلاق يجب وجود:

**1. رسالة ترحيب للمشتركين الجدد**
```
الموضوع: "مرحباً في دراهم Pro ⚡ — خطتك نشطة الآن"
المحتوى:
- تأكيد تفعيل الخطة
- رابط "استكشف ميزاتك الجديدة"
- رقم WhatsApp للدعم
```

**2. قناة WhatsApp للدعم**
رقم مخصص للمشتركين المدفوعين. الرد خلال 4 ساعات.

**3. مسار الشكاوى**
```
خطأ في التطبيق → WhatsApp → [يُصلح Admin] → يُخبر المستخدم
```

**4. جمع الملاحظات — الطريقة البدائية**
بعد أسبوعين: رسالة WhatsApp شخصية لكل مستخدم مدفوع:
"مرحباً [الاسم]، كيف تجد دراهم؟ ما الشيء الذي تحب أن نحسّنه؟"

**ما لا يُبنى الآن:**
- ❌ NPS Survey في التطبيق
- ❌ Intercom أو أداة دعم مدفوعة
- ❌ Knowledge Base
- ❌ Onboarding emails sequence

---

## 14. Checklist الإطلاق

### ✅ Pre-Launch Checklist (قبل الإطلاق)

**الكود:**
- [ ] تصحيح `SubscriptionPlan::maxProjects()` → Free=3, Pro=∞
- [ ] تصحيح `SubscriptionPlan::maxTransactionsPerMonth()` → Pro=1000
- [ ] إضافة `maxClients()`, `maxInvoicesPerMonth()`, `maxQuotesPerMonth()`
- [ ] إضافة `can(string $gate)` method
- [ ] إضافة clients/invoices/quotes لـ `CheckSubscriptionLimits`
- [ ] تسجيل الـ Middleware على routes الـ store
- [ ] إضافة `activatePlan` Action في UserResource
- [ ] إضافة شارة الخطة في layout
- [ ] إضافة `_upgrade-prompt` component في layout
- [ ] تطبيق 6 Feature Gates في Views المعنية

**المحتوى:**
- [ ] تصحيح نصوص `/pricing` (أرقام + CTAs)
- [ ] تصحيح نصوص `/billing/upgrade` (أرقام + عملة)
- [ ] إضافة الأسعار الصحيحة بالدولار مع المعادل

**Admin:**
- [ ] اختبار `activatePlan` Action من الـ Admin
- [ ] التأكد أن `subscription_plan` يتحدث على المستخدم
- [ ] التأكد أن سجل `subscription` يُنشأ بالتواريخ الصحيحة

**الاختبار:**
- [ ] إنشاء حساب تجريبي Free → التأكد من تطبيق الحدود
- [ ] ترقية لـ Pro من Admin → التأكد من رفع الحدود
- [ ] الوصول لحد العملاء → التأكد من ظهور Upgrade Prompt
- [ ] تجربة `/pricing` من متصفح غير مسجّل

---

### 🚀 Launch Day Checklist (يوم الإطلاق)

- [ ] announcement في WhatsApp/Social media
- [ ] التأكد أن رقم WhatsApp للطلبات نشط ومراقَب
- [ ] الـ Admin جاهز لتفعيل الطلبات الواردة
- [ ] Google Sheet جاهز: [الاسم | الإيميل | الخطة | تاريخ التفعيل | تاريخ التجديد | ملاحظات]
- [ ] رسالة ترحيب جاهزة للإرسال بعد كل تفعيل

---

### 📊 Week 1 Checklist

- [ ] رصد كل upgrade prompt ظهر (من logs أو session)
- [ ] رصد كل طلب ترقية وارد
- [ ] تسجيل كل عميل مدفوع في Google Sheet
- [ ] إرسال رسالة ترحيب شخصية لكل عميل جديد
- [ ] متابعة: هل الأسعار واضحة؟ هل الـ CTA يعمل؟

---

### 💰 First 20 Customers Checklist

- [ ] العميل #1-5: تواصل شخصي مكثّف، اسأل عن كل شيء
- [ ] العميل #6-10: ابدأ تتبع أسباب الترقية (ما الذي دفعهم؟)
- [ ] العميل #11-15: ابدأ تتبع أكثر Upgrade Prompt يظهر
- [ ] العميل #16-20: قرّر ما الذي تبنيه بعد ذلك

---

## 15. خارطة الطريق — 14 يوماً

### 📅 اليوم 1-2: إصلاحات Enum + Middleware

| المهمة | المسؤول | التعقيد |
|--------|---------|---------|
| تصحيح `maxProjects` و`maxTransactionsPerMonth` | Dev | 30 دقيقة |
| إضافة `maxClients`, `maxInvoicesPerMonth`, `maxQuotesPerMonth` | Dev | 1 ساعة |
| إضافة `can(string $gate)` method | Dev | 1 ساعة |
| إضافة clients/invoices/quotes لـ Middleware | Dev | 2 ساعة |
| تسجيل Middleware على routes | Dev | 30 دقيقة |

**المخرج:** الحدود تعمل بشكل صحيح على جميع الموارد.

**المخاطر:** منخفضة — تعديلات صغيرة في ملفات موجودة.

---

### 📅 اليوم 3-4: Feature Gates في Views

| المهمة | المخرج | المخاطر |
|--------|--------|---------|
| تطبيق gate `send_email` في Invoices | زر مقفول للـ Free | منخفضة |
| تطبيق gate `export` في Reports | زر مقفول للـ Free | منخفضة |
| تطبيق gate `wallets` في Wallets | صفحة مع CTA | منخفضة |
| تطبيق gate `advanced_reports` | Blur overlay + CTA | متوسطة |
| إضافة Upgrade Prompt component في layout | Flash يظهر везде | منخفضة |

**المخرج:** مستخدم Free يرى الميزات المقفولة ويُوجَّه للترقية.

---

### 📅 اليوم 5-6: Admin Action + UI

| المهمة | التعقيد |
|--------|---------|
| إضافة `activatePlan` Action في UserResource | 3 ساعات |
| إضافة شارة الخطة في `layouts/app.blade.php` | 30 دقيقة |
| اختبار التدفق الكامل: Admin يفعّل → User يرى التغيير | 1 ساعة |

---

### 📅 اليوم 7-8: تصحيح المحتوى والأسعار

| المهمة | التعقيد |
|--------|---------|
| تصحيح نصوص `/pricing` | 2 ساعة |
| تصحيح نصوص `/billing/upgrade` | 1 ساعة |
| إصلاح CTAs في `/pricing` | 1 ساعة |
| إضافة FAQ صغير في `/pricing` | 2 ساعة |

---

### 📅 اليوم 9-10: اختبار شامل (End-to-End)

| السيناريو | الحالة المتوقعة |
|----------|----------------|
| Free user وصل لـ 5 عملاء → حاول إضافة 6 | Prompt + Redirect |
| Free user حاول Export | Lock + CTA |
| Admin فعّل Pro لمستخدم | subscription_plan = pro, ends_at = +30 days |
| Pro user يحاول إضافة عميل | يعمل بدون قيد |
| `/pricing` من متصفح غير مسجّل | يعمل، CTAs تحوّل لـ /register |

---

### 📅 اليوم 11-12: تحضير Customer Success

| المهمة |
|--------|
| كتابة رسالة ترحيب Pro و Business |
| إعداد Google Sheet للتتبع |
| اختبار تدفق WhatsApp الكامل |
| تجهيز رقم WhatsApp للدعم |

---

### 📅 اليوم 13-14: الإطلاق المحدود (Soft Launch)

- إطلاق لـ 10 مستخدمين موثوقين
- جمع ملاحظات مكثّفة
- إصلاح أي أخطاء حرجة
- **الإطلاق العلني في نهاية اليوم 14**

---

## 16. التوصية النهائية

### "إذا كان لدينا أسبوعان ومطوّر واحد، ماذا نبني بالضبط؟"

**الأسبوع الأول (7 أيام) — الكود:**

```
اليوم 1: تصح Enum (أرقام صحيحة) + أضف maxClients/Invoices/Quotes
اليوم 2: وسّع CheckSubscriptionLimits للموارد الثلاثة + سجّل على Routes
اليوم 3: أضف can() method في Enum + طبّقها على Invoices (send_email gate)
اليوم 4: طبّق export gate في Reports + wallets gate في Wallets
اليوم 5: أضف activatePlan Action في UserResource (Filament)
اليوم 6: شارة الخطة في layout + Upgrade Prompt component موحّد
اليوم 7: اختبار End-to-End + إصلاح أي مشاكل
```

**الأسبوع الثاني (7 أيام) — الإطلاق:**

```
اليوم 8: صحّح محتوى /pricing و/billing/upgrade
اليوم 9: أصلح CTAs في /pricing
اليوم 10: اختبر كامل التدفق يدوياً (Free → Prompt → WhatsApp → Admin → Pro)
اليوم 11: جهّز Google Sheet + رسائل الترحيب + رقم WhatsApp
اليوم 12: Soft Launch لـ 5-10 أشخاص موثوقين
اليوم 13: اجمع ملاحظات، أصلح المشاكل
اليوم 14: إطلاق علني
```

### النتيجة

في 14 يوماً، Darahum ستكون قادرة على:
- ✅ عرض خطط واضحة بأسعار صحيحة
- ✅ تطبيق حدود حقيقية على الموارد
- ✅ حجب الميزات المتقدمة مع توجيه للترقية
- ✅ استقبال طلبات الترقية عبر WhatsApp
- ✅ تفعيل الاشتراكات في ثوانٍ من Admin
- ✅ إتاحة تجربة واضحة تدفع للترقية

**ما لن يكون جاهزاً — وهذا مقبول تماماً:**
لا أتمتة، لا بوابة دفع، لا grace period، لا 23 بوابة إضافية. كل ذلك يُبنى بعد أن تعرف بالضبط ما يريده عملاؤك الأوائل.

**الهدف الوحيد الآن:**
> تحصيل أول 20 دفعة. كل ما عدا ذلك يأتي لاحقاً.

---

*المرجعية: PRICING-STRATEGY-V1-FINAL.md | PRICING-IMPLEMENTATION-PLAN.md | COMMERCIAL-PRICING-GUIDE.md*
*جميع القرارات الاستراتيجية محفوظة ومجمّدة. هذه الوثيقة تُحدّد نطاق التنفيذ فقط.*
