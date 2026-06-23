# موديول إعدادات النظام (Admin Settings)

> آخر تحديث: 23 يونيو 2026 | الإصدار: 1.1.0

---

## Overview

يتيح هذا الموديول للمدير التحكم الكامل في إعدادات النظام — البريد الإلكتروني وقوالب الرسائل وبوابة الدفع — مباشرةً من لوحة الإدارة Filament دون الحاجة لتعديل `.env` أو إعادة نشر الكود.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| تغيير إعدادات SMTP بدون .env | Setting model + applyMailSettings() |
| تخصيص نص رسائل البريد | EmailTemplate model + RichEditor |
| معاينة القالب قبل الإرسال | modal معاينة في Filament |
| اختبار الإرسال الفعلي | زر "تجربة" بمتغيرات افتراضية |
| تطبيق الإعدادات فوراً | AppServiceProvider يقرأ من DB في كل request |
| إعدادات بوابة الدفع بدون .env | PaymentSettings Page + applyPaymentSettings() |

---

## Database Structure

### جدول `settings`

| Column | Type | Description |
|--------|------|-------------|
| key | varchar(255) PK | مفتاح الإعداد |
| value | text nullable | القيمة |
| group | varchar(50) | تجميع الإعدادات (mail / general) |
| created_at / updated_at | timestamps | |

**مجموعة `mail` — المفاتيح المستخدمة:**


| key | المقابل في .env |
|-----|----------------|
| `mail_host` | MAIL_HOST |
| `mail_port` | MAIL_PORT |
| `mail_username` | MAIL_USERNAME |
| `mail_password` | MAIL_PASSWORD |
| `mail_encryption` | MAIL_ENCRYPTION |
| `mail_scheme` | MAIL_SCHEME |
| `mail_from_address` | MAIL_FROM_ADDRESS |
| `mail_from_name` | MAIL_FROM_NAME |

---

### جدول `email_templates`

| Column | Type | Description |
|--------|------|-------------|
| key | varchar(255) PK | مفتاح القالب (password_reset / welcome / ...) |
| name | varchar(255) | اسم عربي للعرض في الأدمن |
| subject | varchar(255) | موضوع البريد |
| body | longtext | محتوى HTML للبريد |
| variables | json nullable | المتغيرات المتاحة `{"{{name}}": "اسم المستخدم"}` |
| is_active | boolean | هل القالب مفعَّل؟ |
| created_at / updated_at | timestamps | |

**القوالب الافتراضية المُدرجة عند migrate:**

| key | الاستخدام | المتغيرات |
|-----|-----------|-----------|
| `password_reset` | طلب إعادة تعيين كلمة المرور | `{{name}}`, `{{reset_url}}` |
| `welcome` | تسجيل مستخدم جديد | `{{name}}`, `{{login_url}}` |
| `email_verification` | تأكيد البريد الإلكتروني | `{{name}}`, `{{verify_url}}` |

---

## Models

### Setting

**الموقع:** `app/Models/Setting.php`

```php
// جلب قيمة واحدة (مع Cache)
Setting::get('mail_host', 'default')

// حفظ قيمة واحدة
Setting::set('mail_host', 'mail.darahum.com', 'mail')

// جلب مجموعة كاملة
Setting::group('mail')  // → ['mail_host' => '...', 'mail_port' => '...']

// حفظ مجموعة كاملة
Setting::setGroup('mail', ['mail_host' => '...', 'mail_port' => '...'])
```

**Cache:** كل قيمة مُخزَّنة في Cache مع مفتاح `setting:{key}`. تُمسح عند كل `set()` أو `setGroup()`.

---

### EmailTemplate

**الموقع:** `app/Models/EmailTemplate.php`

```php
// جلب قالب وتطبيق المتغيرات
$result = EmailTemplate::render('password_reset', [
    '{{name}}'      => $user->name,
    '{{reset_url}}' => $resetUrl,
]);
// يُرجع: ['subject' => '...', 'body' => '...']
// أو null إذا لم يوجد القالب أو كان غير مفعَّل
```

---

## Services / Providers

### AppServiceProvider — applyPaymentSettings() *(جديد — 23 يونيو 2026)*

**الموقع:** `app/Providers/AppServiceProvider.php`

يُشغَّل في `boot()` مع كل request بعد `applyMailSettings()`:

```
Setting::group('payment')
  ↓
Config::set('billing.provider', ...)              // togo | null
Config::set('billing.togo.api_key', ...)
Config::set('billing.togo.receiver_address_id', ...)
Config::set('billing.togo.currency', ...)
Config::set('billing.plans.pro.price', ...)
Config::set('billing.plans.business.price', ...)
```

**الأولوية:** DB > .env — الإعدادات في DB تتجاوز .env.  
**راجع:** `docs/TOGO-PAYMENT-GATEWAY.md` للتفاصيل الكاملة.

---

### AppServiceProvider — applyMailSettings()

**الموقع:** `app/Providers/AppServiceProvider.php`

يُشغَّل في `boot()` مع كل request:

```
Setting::group('mail')
  ↓
if mail_host موجود في DB:
    Config::set('mail.mailers.smtp.host', ...)
    Config::set('mail.mailers.smtp.port', ...)
    Config::set('mail.mailers.smtp.username', ...)
    Config::set('mail.mailers.smtp.password', ...)
    Config::set('mail.mailers.smtp.encryption', ...)
    Config::set('mail.mailers.smtp.scheme', ...)
    Config::set('mail.from.address', ...)
    Config::set('mail.from.name', ...)
```

**الأولوية:** DB > .env — إذا وُجدت إعدادات في DB تتجاوز .env.  
**Fallback:** إذا فشل الاتصال بـ DB (مثلاً أول migrate) يُتجاهَل الخطأ وتُستخدم قيم .env.

---

### CustomResetPasswordNotification

**الموقع:** `app/Notifications/CustomResetPasswordNotification.php`

يُستخدم بدلاً من `Illuminate\Auth\Notifications\ResetPassword`:

```
User::sendPasswordResetNotification($token)
  ↓
CustomResetPasswordNotification::toMail()
  ↓
EmailTemplate::render('password_reset', [...])
  ↓
إذا وُجد قالب → view('emails.template', ['body' => ...])
إذا لم يوجد  → parent::toMail() (القالب الافتراضي)
```

**التسجيل في User Model:**
```php
public function sendPasswordResetNotification($token): void {
    $this->notify(new CustomResetPasswordNotification($token));
}
```

---

## Filament Admin Pages

### صفحة إعدادات البريد

**الموقع:** `app/Filament/Pages/MailSettings.php`  
**المسار:** `/admin/mail-settings`  
**المجموعة:** الإعدادات

**الحقول:**
- SMTP: Host, Port, Username, Password, Encryption
- من: بريد المُرسِل, اسم المُرسِل
- اختبار: إرسال بريد تجريبي

**منطق الـ Encryption:**
- اختيار `SSL` → Port يتغير تلقائياً إلى 465, scheme=smtps
- اختيار `TLS` → Port يتغير تلقائياً إلى 587, scheme=null

---

### صفحة إعدادات بوابة الدفع *(جديد — 23 يونيو 2026)*

**الموقع:** `app/Filament/Pages/PaymentSettings.php`  
**المسار:** `/admin/payment-settings`  
**المجموعة:** الإعدادات

**الحقول:**
- مزود الدفع: Select (togo / فارغ)
- Togo: API Key (password)، Receiver Address ID، العملة (ILS/USD/JOD)
- إنشاء Receiver Address: نموذج قابل للطي (مطلوب ASCII فقط)
- أسعار الخطط: Pro، Business، عملة العرض

**إجراءات Header:**
- **حفظ الإعدادات** — يحفظ في جدول `settings` group=payment
- **اختبار الاتصال** — GET /api/v1/currency-exchange للتحقق من API Key
- **إنشاء Receiver Address** — POST /api/v1/receivers-addresses + يحفظ الـ ID
- **مسح الـ ID** — يُفرّغ receiver_address_id من DB

> راجع `docs/TOGO-PAYMENT-GATEWAY.md` للتفاصيل الكاملة.

---

### EmailTemplateResource

**الموقع:** `app/Filament/Resources/EmailTemplateResource.php`  
**المسار:** `/admin/email-templates`  
**المجموعة:** الإعدادات

| الإجراء | الوصف |
|---------|-------|
| **تعديل** | محرر نصي غني (RichEditor) للموضوع والمحتوى |
| **معاينة** | modal يعرض HTML النهائي للبريد |
| **تجربة** | إدخال إيميل → إرسال فوري بمتغيرات افتراضية |

**ملاحظة:** لا يوجد زر "إضافة" — القوالب ثابتة ويمكن تعديلها فقط.

---

## Views

| الملف | الغرض |
|-------|-------|
| `resources/views/emails/template.blade.php` | قالب HTML موحَّد لجميع الرسائل (Header + Body + Footer) |
| `resources/views/filament/pages/mail-settings.blade.php` | Blade لصفحة إعدادات البريد |
| `resources/views/filament/email-preview.blade.php` | معاينة القالب في modal |

### بنية `emails/template.blade.php`

```html
[Header: شعار دراهم بخلفية indigo]
[Body: {!! $body !!}]
[Footer: حقوق النشر + تنبيه]
```

متغير `$body` هو HTML من `email_templates.body` بعد استبدال المتغيرات.

---

## User Flow — تغيير إعدادات البريد

```
المدير → /admin/mail-settings
  │
  ├─ يُدخل Host/Port/Username/Password/Encryption
  ├─ يختار Encryption → Port يتغير تلقائياً
  ├─ يُدخل بريد اختبار
  ├─ يضغط "إرسال بريد تجريبي"
  │   ↓
  │   يُحفَظ في DB
  │   يُرسَل بريد تجريبي
  │   ✅ نجاح أو ❌ خطأ مع رسالة واضحة
  │
  └─ يضغط "حفظ الإعدادات"
      ↓
      Setting::setGroup('mail', [...])
      Cache يُمسَح
      الإعدادات الجديدة تسري فوراً على الطلب التالي
```

---

## User Flow — تعديل قالب بريد

```
المدير → /admin/email-templates
  │
  ├─ معاينة القالب (modal فوري)
  │
  ├─ تجربة الرسالة (من الجدول)
  │   ↓ إدخال إيميل → إرسال فوري بمتغيرات تجريبية
  │
  └─ تعديل القالب
      ↓
      محرر نصي غني (Bold/Links/Lists/...)
      متغيرات: {{name}}, {{reset_url}}, ...
      حفظ → يسري على الإرسال التالي فوراً
      زر "تجربة الرسالة" من صفحة التعديل
```

---

## Security Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **وصول محدود** | الصفحتان داخل `/admin` — super_admin فقط |
| **كلمة المرور** | لا تُعرض في الحقل عند الفتح — `password()` مع `revealable()` |
| **كلمة المرور فارغة** | إذا تُركت فارغة عند الحفظ لا تُحدَّث في DB |
| **XSS في القوالب** | `{!! $body !!}` — المدير هو من يكتب المحتوى (موثوق) |
| **Fallback** | إذا فشل DB يُستخدم .env — لا توقف للخدمة |

---

## إصلاحات موثَّقة

| # | المشكلة | السبب | الإصلاح |
|---|---------|-------|---------|
| 1 | 500 على forgot-password | `MAIL_SCHEME` غير مضبوط → Symfony يحاول `tls://` | أضف `MAIL_SCHEME=smtps` في .env |
| 2 | البريد يصل كـ Spam | MAIL_FROM_NAME="Workuflow" لا يتطابق مع domain | غيّر إلى "دراهم" + FROM=info@darahum.com |
| 3 | `tls scheme not supported` | Config cache قديم أو encryption=tls مع port 465 | `php artisan config:clear` + MAIL_SCHEME=smtps |

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| إضافة قوالب مخصصة جديدة من الأدمن | متوسطة |
| دعم متغيرات ديناميكية أكثر (company, logo) | متوسطة |
| إرسال إيميل جماعي لمجموعة مستخدمين | منخفضة |
| تاريخ إرسال لكل قالب (آخر إرسال) | منخفضة |
| دعم Attachments في القوالب | مستقبلية |
