# توثيق — Togo Payment Gateway (togo.ps)

> **آخر تحديث:** 23 يونيو 2026
> **المرحلة:** Phase B — Payment Gateway
> **الحالة:** ✅ مكتمل (في انتظار إصلاح Togo من جانبهم)

---

## نظرة عامة

**Togo** بوابة دفع فلسطينية (togo.ps) تعمل عبر **Redirect** (وليس Webhooks).  
المستخدم يُحوَّل إلى صفحة Togo للدفع، ثم يُعاد توجيهه للتطبيق عند الانتهاء.

```
User يختار خطة
    ↓
POST /api/v1/actions (Create_Visa/RFP)  →  Togo يُنشئ order
    ↓
Redirect → https://api.togo.ps/api/v1/direct-pay?orderId=...
    ↓
User يدفع على صفحة Togo
    ↓
Redirect → /billing/togo/callback (نجاح) أو /billing/togo/cancel (إلغاء)
    ↓
GET /api/v1/orders?id=<order_id>  →  التحقق من الحالة
    ↓
activatePlan() إذا PAID
```

---

## الملفات

| الملف | الدور |
|-------|-------|
| `app/Modules/Billing/Services/TogoPaymentService.php` | Service الرئيسي — 4 خطوات API |
| `app/Modules/Billing/Contracts/PaymentProviderInterface.php` | Contract مشترك لجميع مزودي الدفع |
| `app/Console/Commands/TogoSetupReceiverCommand.php` | Artisan command لإنشاء receiver address |
| `app/Http/Controllers/BillingController.php` | checkout / togoCallback / togoCancel |
| `app/Filament/Pages/PaymentSettings.php` | صفحة إعدادات البوابة في Admin Panel |
| `resources/views/filament/pages/payment-settings.blade.php` | View لصفحة الإعدادات |
| `app/Providers/AppServiceProvider.php` | تحميل إعدادات DB → Config في boot() |
| `config/billing.php` | إعدادات مزود الدفع والأسعار |

---

## الإعداد الأولي (خطوة واحدة)

### 1. إضافة بيانات .env

```env
BILLING_PROVIDER=togo
TOGO_API_KEY=TOGO-xxxxxxxxxxxxxxxxxx
TOGO_RECEIVER_ADDRESS_ID=   # يُملأ بعد الخطوة 2
TOGO_CURRENCY=ILS
```

### 2. إنشاء Receiver Address

```bash
php artisan togo:setup-receiver
```

يطلب: الاسم الكامل، رقم الهاتف، رمز الدولة، اسم الدولة، المدينة.  
**⚠️ جميع الحقول يجب أن تكون بالإنجليزية (ASCII فقط) — Togo لا يقبل العربية.**

بعد النجاح يطبع الـ ID — أضفه في `.env`:
```env
TOGO_RECEIVER_ADDRESS_ID=rcv_xxxxxxxxxxxx
```

### 3. ضبط الإعدادات من لوحة الإدارة (بديل .env)

انتقل إلى **Admin → الإعدادات → إعدادات بوابة الدفع** وأدخل:
- API Key
- Receiver Address ID
- العملة
- أسعار الخطط

الإعدادات تُحفظ في جدول `settings` (group: `payment`) وتُحمَّل تلقائياً.

---

## خطوات API التفصيلية

### الخطوة 1 — إنشاء Receiver Address (مرة واحدة)

```
POST https://api.togo.ps/api/v1/receivers-addresses
x-api-key: <TOGO_API_KEY>

{
  "receiver_name": "hazem alyahya",        // ASCII فقط
  "receiver_phone_number": "0598663901",
  "country_code": "PS",
  "country_name": "Palestine",             // ASCII فقط
  "phone_connected_to_whats": false,
  "city": "Gaza",                          // ASCII فقط
  "details": ""
}
```

الاستجابة: `{ "data": { "id": "rcv_xxx", ... } }` — احفظ `id` في `.env`.

---

### الخطوة 2 — إنشاء RFP Order

```
POST https://api.togo.ps/api/v1/actions
x-api-key: <TOGO_API_KEY>

{
  "event": "Create_Visa",
  "data": {
    "type": "RFP",
    "value": 9.99,
    "receiver_address_id": "<rcv_id>",
    "receiver_email": "user@example.com",
    "currency": "ILS",
    "source": "external_website",
    "prevent_sms_link": false,
    "payment_success_redirect_link": "https://yourdomain.com/billing/togo/callback",
    "payment_cancel_redirect_link":  "https://yourdomain.com/billing/togo/cancel"
  }
}
```

الاستجابة: `{ "data": { "id": "ord_xxx", "hashed_id": "hsh_xxx", ... } }`

- `id` → يُحفظ في `session('togo_order_id')` للتحقق لاحقاً
- `hashed_id` → يُستخدم في رابط الدفع (الخطوة 3)

---

### الخطوة 3 — Redirect للمستخدم

```
https://api.togo.ps/api/v1/direct-pay?orderId=<hashed_id>&receiverEmail=<email>
```

يتم هذا تلقائياً من `BillingController::checkout()`.

---

### الخطوة 4 — التحقق من الدفع (Callback)

```
GET https://api.togo.ps/api/v1/orders?id=<order_id>
x-api-key: <TOGO_API_KEY>
```

**ملاحظة:** يستخدم `id` (وليس `hashed_id`).

```php
// في BillingController::togoCallback()
$orderId = session('togo_order_id');
$data = $togoService->verifyOrder($orderId);

if (($data['status'] ?? '') === 'PAID') {
    $billing->activatePlan($user, session('togo_order_plan'));
}
```

---

## Routes

```php
// في routes/web.php (داخل auth middleware)
Route::prefix('billing')->name('billing.')->group(function () {
    Route::get('/togo/callback', [BillingController::class, 'togoCallback'])->name('togo.callback');
    Route::get('/togo/cancel',   [BillingController::class, 'togoCancel'])->name('togo.cancel');
});
```

---

## Session Keys

| Key | القيمة | الاستخدام |
|-----|--------|-----------|
| `togo_order_id` | `id` من استجابة RFP | التحقق في verifyOrder() |
| `togo_order_plan` | `'pro'` أو `'business'` | تفعيل الخطة بعد الدفع |

---

## PaymentProviderInterface

```php
interface PaymentProviderInterface {
    public function createCheckoutUrl(User $user, string $plan): string;
    public function createPortalUrl(User $user): string;
    public function parseWebhook(string $payload, string $signature): array;
}
```

ربط المزود في `AppServiceProvider::register()`:
```php
$this->app->bind(PaymentProviderInterface::class, function () {
    return match (config('billing.provider')) {
        'togo'  => new TogoPaymentService(),
        default => throw new \RuntimeException('لا يوجد مزود دفع مفعّل'),
    };
});
```

---

## إعدادات لوحة الإدارة (Filament)

**المسار:** Admin → الإعدادات → إعدادات بوابة الدفع  
**Class:** `App\Filament\Pages\PaymentSettings`

### أقسام الصفحة

| القسم | المحتوى |
|-------|---------|
| مزود الدفع | Select: togo أو فارغ |
| بيانات Togo | API Key (password) + Receiver Address ID + العملة |
| إنشاء Receiver Address | نموذج قابل للطي (collapsed إذا ID موجود) |
| أسعار الخطط | Pro Price + Business Price + عملة العرض |

### إجراءات الصفحة (Header Actions)

| الإجراء | اللون | الوظيفة |
|---------|-------|---------|
| حفظ الإعدادات | أخضر | يحفظ كل الإعدادات في جدول `settings` |
| اختبار الاتصال | أزرق | GET /api/v1/currency-exchange للتحقق من API Key |
| إنشاء Receiver Address | برتقالي | يستدعي POST /api/v1/receivers-addresses ويحفظ الـ ID |
| مسح الـ ID | أحمر | يُفرّغ `togo_receiver_address_id` من DB |

### تخزين الإعدادات (جدول settings)

| المفتاح | group | القيمة |
|---------|-------|--------|
| `billing_provider` | payment | `togo` |
| `togo_api_key` | payment | مفتاح API |
| `togo_receiver_address_id` | payment | rcv_xxx |
| `togo_currency` | payment | ILS |
| `billing_price_pro` | payment | 9.99 |
| `billing_price_business` | payment | 19.99 |
| `billing_currency_display` | payment | ₪ |

---

## تحميل الإعدادات من DB

في `AppServiceProvider::boot()` يُستدعى `applyPaymentSettings()`:

```php
private function applyPaymentSettings(): void {
    $p = Setting::group('payment');
    Config::set('billing.provider',                $p['billing_provider'] ?? ...);
    Config::set('billing.togo.api_key',            $p['togo_api_key'] ?? ...);
    Config::set('billing.togo.receiver_address_id',$p['togo_receiver_address_id'] ?? ...);
    Config::set('billing.togo.currency',           $p['togo_currency'] ?? ...);
    Config::set('billing.plans.pro.price',         $p['billing_price_pro'] ?? ...);
    Config::set('billing.plans.business.price',    $p['billing_price_business'] ?? ...);
}
```

هذا يتيح تغيير الإعدادات من Admin بدون تعديل `.env`.

---

## قيود مهمة

### ⚠️ ASCII فقط

Togo API يرفض أي حرف عربي (Unicode > 127) بالخطأ:
```
Cannot convert argument to a ByteString because the character
at index N has a value of 1605 which is greater than 255.
```

الحرف 1605 = `م` (مim) — يظهر عادةً من البيانات المخزّنة داخل خوادم Togo.

**الحل المؤقت:** تحقق `assertAscii()` في `TogoPaymentService` يرفع استثناءً واضحاً قبل الإرسال.

### ⚠️ لا يدعم Webhooks

Togo تعمل بـ Redirect فقط — `parseWebhook()` يرمي `LogicException`.  
التحقق من الدفع يتم في callback URL فقط.

### ⚠️ مشكلة حالية (جانب Togo)

عند استدعاء `POST /api/v1/receivers-addresses` بحقول ASCII كاملة، يُعيد الـ API خطأ ByteString بسبب بيانات عربية في حساب المستخدم على منصة Togo.  
**الحل:** تواصل مع دعم Togo لإصلاح بيانات الحساب أو للحصول على `receiver_address_id` مباشرة.

---

## الانتقال لمزود دفع آخر مستقبلاً

1. أنشئ class جديد يُطبّق `PaymentProviderInterface`
2. أضف case في `AppServiceProvider::register()`
3. غيّر `BILLING_PROVIDER` في `.env` أو من Admin Panel
4. لا تغيير مطلوب في Controllers أو Views

---

## مراجع

- `config/billing.php` — إعدادات الأسعار والمزود
- `docs/MANUAL-BILLING-FLOW.md` — المرحلة اليدوية (قبل Togo)
- `docs/SETTINGS-ADMIN.md` — توثيق صفحات إعدادات Admin
- `app/Modules/Billing/Contracts/PaymentProviderInterface.php` — العقد المشترك
- Togo API Docs: `Payment_Gateway_API.pdf` (محفوظ في المشروع)
