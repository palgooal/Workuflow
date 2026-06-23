# توثيق — Manual Billing Flow (قبل Payment Gateway)

> **آخر تحديث:** 23 يونيو 2026
> **المرحلة:** Phase A — ما قبل الإطلاق الرسمي
> **الحالة:** ⚠️ تم تجاوزه — Togo Payment Gateway مُفعَّل الآن (راجع `docs/TOGO-PAYMENT-GATEWAY.md`)

---

## الفلسفة

بدلاً من انتظار Payment Gateway (Stripe/Tap/Paddle)، يعتمد النظام حالياً على **الترقية اليدوية عبر واتساب**:

```
المستخدم يصل للحد  →  Upgrade Modal / صفحة upgrade  →  واتساب المؤسس  →  تحويل بنكي  →  Admin يغير الخطة
```

هذا النهج يُمكّن من الحصول على أول 10-20 عميل دافع **قبل** الاستثمار في تكامل Payment Gateway.

---

## الملفات المعدَّلة اليوم

| الملف | التغيير |
|-------|---------|
| `config/billing.php` | إضافة `owner_whatsapp` key |
| `resources/views/billing/index.blade.php` | استبدال بانر "Payment Not Ready" بـ CTA واتساب أخضر + أزرار الترقية توجّه لـ `/upgrade` |
| `resources/views/billing/upgrade.blade.php` | **ملف جديد** — صفحة الترقية اليدوية |
| `resources/views/invoices/show.blade.php` | إضافة شرط `@if($whatsappPhone)` على زر "واتساب + PDF" |
| `app/Http/Middleware/CheckSubscriptionLimits.php` | تحويل `abort(403)` إلى `redirect()->back()` مع `session flash` |
| `resources/views/components/upgrade-modal.blade.php` | **ملف جديد** — Modal يظهر تلقائياً عند تجاوز الحد |
| `resources/views/layouts/app.blade.php` | إضافة `<x-upgrade-modal />` |
| `routes/web.php` | إضافة `GET /billing/upgrade` |
| `app/Http/Controllers/BillingController.php` | إضافة `upgrade()` method |
| `resources/views/settings/index.blade.php` | تحسين تبويب الخطة: CTA سياقي، تحذيرات 80%/100%، إحصائيات كاملة |

---

## الإعداد المطلوب

أضف في `.env`:
```env
OWNER_WHATSAPP=966XXXXXXXXX
```

> بدون هذا المتغير: أزرار واتساب مخفية ويظهر بديل (رابط صفحة الخطط فقط).

---

## تدفق تجربة المستخدم (UX Flow)

### 1. الترقية الطوعية

```
settings#plan  →  "ترقية الخطة"     →  /billing/upgrade  →  واتساب
settings#plan  →  "عرض جميع الخطط"  →  /billing
/billing       →  "الترقية إلى Pro"  →  /billing/upgrade  →  واتساب
```

### 2. الترقية عند الوصول للحد

```
User يحاول إنشاء مشروع/معاملة زيادة
  ↓
CheckSubscriptionLimits Middleware
  ↓
session()->flash('upgrade_prompt', [...])
  ↓
redirect()->back()
  ↓
upgrade-modal.blade.php يظهر تلقائياً (x-show + Alpine.js)
  ↓
زر "تواصل على واتساب" أو "عرض خطط الاشتراك"
```

---

## بنية upgrade-modal

```blade
{{-- يُفعَّل بـ session('upgrade_prompt') --}}
@if(session('upgrade_prompt'))
    {{-- Alpine x-data="{ open: true }" --}}
    {{-- يحتوي: رسالة الحد + hint + زر واتساب + رابط /billing --}}
@endif
```

بيانات الـ flash:
```php
session()->flash('upgrade_prompt', [
    'resource' => 'projects',          // projects | transactions
    'message'  => 'وصلت للحد الأقصى (2 مشاريع)...',
    'hint'     => 'الترقية إلى Pro تتيح لك حتى 10 مشاريع.',
]);
```

---

## تبويب الخطة في الإعدادات (`settings#plan`)

### الإصلاحات المُطبَّقة

| المشكلة | الحل |
|---------|------|
| CTA نص ثابت | CTA سياقي: 3 حالات (عادي / 80% / 100%) بأرقام حقيقية |
| ✗ علامات شبه مخفية (gray-300) | SVG icons + `line-through` واضح |
| حدود ناقصة (مشاريع + معاملات فقط) | أُضيفت: فواتير + عملاء كإحصائيات |
| لا تحذير عند 80% | بانر أصفر (80-99%) وأحمر (100%) |
| إيموجي 🆓 | SVG icon احترافي |
| "محدودة المميزات" (سلبي) | "ابدأ مجاناً — يمكنك الترقية في أي وقت" |

### منطق التحذير

```php
$projPct  = ($projectsUsed / $projectsMax) * 100;
$txPct    = ($txThisMonth  / $txMax)       * 100;
$nearLimit = ($projPct >= 80 || $txPct >= 80);   // بانر أصفر
$atLimit   = ($projPct >= 100 || $txPct >= 100); // بانر أحمر
```

---

## المرحلة التالية — Payment Gateway ✅ منجز

تم ربط **Togo Payment Gateway** (togo.ps) في 23 يونيو 2026:

- `BILLING_PROVIDER=togo` في `.env`
- `TogoPaymentService` يُطبّق `PaymentProviderInterface`
- `BillingController::checkout()` يستدعي `createCheckoutUrl()` وتُحوَّل لصفحة Togo

> راجع التوثيق الكامل: **`docs/TOGO-PAYMENT-GATEWAY.md`**

---

## مراجع

- `config/billing.php` — إعدادات مزود الدفع والأسعار
- `app/Http/Middleware/CheckSubscriptionLimits.php` — التحقق من الحدود
- `app/Support/Enums/SubscriptionPlan.php` — حدود كل خطة
- `docs/LAUNCH-READINESS-REVIEW.md` — السياق الاستراتيجي
