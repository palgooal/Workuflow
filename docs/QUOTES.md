# نظام عروض الأسعار — دراهم

> تاريخ الإنشاء: 28 مايو 2026  
> الإصدار: 1.0.0  
> المطوّر: دراهم — نظام إدارة مالي للمستقلين

---

## نظرة عامة

نظام عروض الأسعار يتيح للمستقل إنشاء عروض أسعار احترافية وإرسالها للعملاء عبر رابط آمن، ومتابعة حالتها من المسودة حتى التحويل لفاتورة.

### الميزات الأساسية

- إنشاء عروض أسعار بتعدد البنود مع حساب تلقائي للإجماليات
- رابط عميل آمن لا يتطلب تسجيل دخول (`/q/{token}`)
- قبول/رفض العرض من بوابة العميل مع تسجيل سبب الرفض
- تحويل العرض المقبول مباشرة إلى فاتورة
- تاريخ انتهاء صلاحية مع تحديث تلقائي للحالة
- طباعة / تصدير PDF عبر `window.print()`
- إنشاء من ثلاثة مسارات: ملف العميل، صفحة المشروع، صفحة /quotes

---

## قاعدة البيانات

### جدول `quotes`

| العمود | النوع | الوصف |
|--------|------|-------|
| id | bigint PK | |
| ulid | char(26) unique | مفتاح المسار (route key) |
| token | varchar(64) unique | رمز البوابة العامة (48 حرف عشوائي) |
| user_id | FK → users | المستخدم المالك |
| client_id | FK → clients | العميل المستهدف |
| project_id | char(26) nullable | المشروع المرتبط (اختياري) |
| number | varchar(50) unique | الرقم التسلسلي (QUO-0001) |
| title | varchar(255) nullable | عنوان وصفي للعرض |
| status | varchar(20) | الحالة (QuoteStatus Enum) |
| issue_date | date | تاريخ الإصدار |
| valid_until | date nullable | تاريخ انتهاء الصلاحية |
| subtotal | decimal(12,2) | المجموع الفرعي |
| tax_rate | decimal(5,2) | نسبة الضريبة % |
| tax_amount | decimal(12,2) | مبلغ الضريبة |
| discount | decimal(12,2) | مبلغ الخصم |
| total | decimal(12,2) | الإجمالي النهائي |
| currency | varchar(3) | العملة (ILS افتراضي) |
| notes | text nullable | ملاحظات للعميل |
| terms | text nullable | الشروط والأحكام |
| sent_at | timestamp nullable | وقت الإرسال |
| viewed_at | timestamp nullable | أول مشاهدة من العميل |
| accepted_at | timestamp nullable | وقت القبول |
| rejected_at | timestamp nullable | وقت الرفض |
| converted_at | timestamp nullable | وقت التحويل لفاتورة |
| client_ip | varchar(45) nullable | IP العميل عند القبول/الرفض |
| rejection_reason | varchar(500) nullable | سبب رفض العميل |
| deleted_at | timestamp nullable | الحذف الناعم (SoftDeletes) |

### جدول `quote_items`

| العمود | النوع | الوصف |
|--------|------|-------|
| id | bigint PK | |
| quote_id | FK → quotes cascadeDelete | |
| description | varchar(500) | وصف البند |
| quantity | decimal(10,2) | الكمية |
| unit_price | decimal(12,2) | سعر الوحدة |
| total | decimal(12,2) | الكمية × سعر الوحدة |
| sort_order | smallint | ترتيب العرض |

---

## دورة حياة العرض (QuoteStatus)

```
Draft → Sent → Viewed → Accepted → Converted
                     ↘ Rejected
              (منتهي الصلاحية = Expired — يُحسب تلقائياً)
```

| الحالة | القيمة | الأيقونة | الوصف |
|--------|--------|---------|-------|
| Draft | `draft` | 📝 | مسودة — قابلة للتعديل |
| Sent | `sent` | 📤 | أُرسل للعميل |
| Viewed | `viewed` | 👁️ | شاهده العميل (أول فتح للرابط) |
| Accepted | `accepted` | ✅ | قبله العميل |
| Rejected | `rejected` | ❌ | رفضه العميل |
| Expired | `expired` | ⏰ | انتهت صلاحيته |
| Converted | `converted` | 🧾 | حُوِّل إلى فاتورة |

### قواعد الانتقال

- `isEditable()` → Draft فقط
- `canBeSent()` → Draft / Sent / Viewed
- `isPending()` → Sent / Viewed (ينتظر رد العميل)
- `canConvert()` → Accepted فقط
- `isExpired()` → `valid_until` ماضٍ + الحالة ليست Accepted/Rejected/Converted

---

## Model: Quote

**الموقع:** `app/Models/Quote.php`

### الخصائص التلقائية (Boot)

```php
// عند الإنشاء:
$quote->ulid  = Str::ulid();         // مفتاح المسار
$quote->token = Str::random(48);      // رمز البوابة العامة
$quote->number = 'QUO-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
```

### العلاقات

```php
user()    → belongsTo(User)
client()  → belongsTo(Client)
project() → belongsTo(Project)  // nullable
items()   → hasMany(QuoteItem)
invoice() → hasOne(Invoice, 'reference', 'number')  // بعد التحويل
```

### الدوال المساعدة

```php
recalculate()   // subtotal → taxAmount → total → save
isExpired()     // valid_until ماضٍ + حالة غير نهائية
portalUrl()     // route('quotes.portal', $this->token)
getRouteKeyName() → 'ulid'
```

---

## Controller: QuoteController

**الموقع:** `app/Http/Controllers/QuoteController.php`

### المسارات المحمية بـ Auth

| الفعل | المسار | الدالة | الوصف |
|------|--------|-------|-------|
| GET | /quotes | index | قائمة مع إحصائيات |
| GET | /quotes/create | create | نموذج الإنشاء |
| POST | /quotes | store | حفظ عرض جديد |
| GET | /quotes/{ulid} | show | عرض تفصيلي |
| GET | /quotes/{ulid}/edit | edit | نموذج التعديل (Draft فقط) |
| PUT | /quotes/{ulid} | update | حفظ التعديلات |
| DELETE | /quotes/{ulid} | destroy | حذف ناعم |
| POST | /quotes/{ulid}/mark-sent | markSent | تغيير الحالة إلى Sent |
| POST | /quotes/{ulid}/convert | convertToInvoice | تحويل لفاتورة |

### المسارات العامة (بدون Auth)

| الفعل | المسار | الدالة | الوصف |
|------|--------|-------|-------|
| GET | /q/{token} | portal | بوابة العميل |
| POST | /q/{token}/accept | accept | قبول العرض |
| POST | /q/{token}/reject | reject | رفض العرض |

### `convertToInvoice()` — منطق التحويل

```php
// 1. (اختياري) إنشاء مشروع جديد إذا طُلب ذلك:
//    - project.name           = project_name من الطلب
//    - project.contract_value = quote.total
//    - يُربط المشروع بالعرض (quote.project_id = project.id)
//
// 2. إنشاء الفاتورة:
//    - ينسخ: client_id, project_id, currency, notes, tax_rate, discount, total
//    - ينسخ جميع البنود (QuoteItem → InvoiceItem)
//    - يضبط: invoice.reference = quote.number
//
// 3. تحديث العرض:
//    - quote.status       = Converted
//    - quote.converted_at = now()
//
// 4. يعيد التوجيه إلى صفحة الفاتورة الجديدة
```

### حقول الطلب الاختيارية

| الحقل | النوع | الوصف |
|-------|------|-------|
| `create_project` | boolean | إنشاء مشروع مرتبط |
| `project_name` | string (max 255) | اسم المشروع (مطلوب إذا `create_project=1`) |
| `project_type` | `business` \| `personal` | نوع المشروع (افتراضي: `business`) |

> **ملاحظة:** خيار إنشاء المشروع يظهر فقط إذا كان العرض **غير مرتبط بمشروع مسبقاً**.

---

## بوابة العميل (Client Portal)

### كيف تعمل

1. المستقل يضغط "تسجيل كمُرسَل" → الحالة تصبح `Sent`
2. ينسخ رابط العميل: `https://domain.com/q/{token}`
3. العميل يفتح الرابط (لا يحتاج تسجيل دخول)
4. عند أول فتح: `Sent → Viewed`، يُسجَّل `viewed_at`
5. العميل يضغط "قبول العرض" أو يفتح نموذج الرفض
6. يُسجَّل الإجراء + `client_ip` + `accepted_at`/`rejected_at`
7. يصل إشعار للمستقل (flash message عند الرجوع للصفحة)

### الأمان

- الرمز مكوَّن من 48 حرف عشوائي (Base62) → 62^48 احتمال ≈ مستحيل التخمين
- لا يُعرض ID العرض في الرابط
- العميل لا يرى أي بيانات غير عرضه
- IP مُسجَّل كدليل أساسي فقط (ليس توقيعاً قانونياً)

---

## التكامل مع باقي النظام

### من ملف العميل (`/clients/{id}`)

- تبويب "عروض الأسعار" يعرض جميع عروض العميل
- زر "إنشاء عرض سعر" يوجه إلى `/quotes/create?client_id={id}`

### من صفحة المشروع (`/projects/{id}`)

- قسم "عروض الأسعار" يعرض عروض المشروع
- زر "إنشاء عرض" يوجه إلى `/quotes/create?project_id={id}&client_id={client_id}`

### من الصفحة المستقلة (`/quotes`)

- قائمة شاملة بجميع العروض
- إحصائيات: إجمالي العروض، المعلقة، المقبولة، المرفوضة، قيمة المقبولة

---

## واجهات المستخدم

| الملف | الوصف |
|-------|-------|
| `resources/views/quotes/index.blade.php` | قائمة العروض + إحصائيات |
| `resources/views/quotes/create.blade.php` | نموذج الإنشاء (Alpine.js) |
| `resources/views/quotes/edit.blade.php` | نموذج التعديل |
| `resources/views/quotes/show.blade.php` | عرض تفصيلي + إجراءات |
| `resources/views/quotes/portal.blade.php` | بوابة العميل العامة |

### Alpine.js — `quoteForm()`

الدالة المسؤولة عن تفاعلية نماذج الإنشاء/التعديل:

```javascript
{
    items: [],          // مصفوفة البنود
    taxRate: 0,         // نسبة الضريبة
    discount: 0,        // الخصم
    subtotal: 0,        // يُحسب تلقائياً
    taxAmount: 0,       // يُحسب تلقائياً
    total: 0,           // يُحسب تلقائياً

    addItem()           // إضافة بند فارغ
    addServiceItem(name) // إضافة من كتالوج الخدمات
    removeItem(index)   // حذف بند (الحد الأدنى بند واحد)
    recalc()           // إعادة حساب الإجماليات
}
```

---

## الطباعة / PDF

يستخدم النظام `window.print()` مع CSS مخصص:

```css
@media print {
    .no-print { display: none !important; }  /* يخفي الأزرار والروابط */
    body { background: white !important; }
    .quote-paper { box-shadow: none !important; border: none !important; }
}
```

لا يوجد مكتبة خارجية — متصفح العميل يتولى التحويل لـ PDF.

---

## إضافات مستقبلية

### 1. التوقيع الرقمي (Digital Signature)

لتعزيز القوة القانونية للعرض المقبول:

```
- اسم الموقِّع (حقل نصي إلزامي عند القبول)
- توقيع بخطي (Canvas HTML5 — رسم بالماوس/اللمس)
- IP Address (مُسجَّل بالفعل)
- User Agent (متصفح + نظام تشغيل)
- Timestamp دقيق
- حفظ صورة التوقيع كـ base64 في العمود signature_data
```

**تعديلات DB المطلوبة:**

```sql
ALTER TABLE quotes 
ADD COLUMN signer_name VARCHAR(255) NULL,
ADD COLUMN signature_data TEXT NULL,       -- base64 PNG
ADD COLUMN signer_user_agent VARCHAR(500) NULL;
```

### 2. سجل النشاطات `quote_activities`

جدول تدقيق كامل يُسجِّل كل تغيير على العرض:

```php
Schema::create('quote_activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('event', 50); // sent, viewed, accepted, rejected, converted, edited
    $table->string('client_ip', 45)->nullable();
    $table->string('user_agent', 500)->nullable();
    $table->json('meta')->nullable(); // بيانات إضافية حسب نوع الحدث
    $table->timestamp('occurred_at')->useCurrent();
});
```

**الأحداث المُسجَّلة:**

| الحدث | المُشغِّل | البيانات الإضافية |
|-------|---------|-----------------|
| `created` | المستقل | — |
| `edited` | المستقل | `{fields_changed}` |
| `sent` | المستقل | — |
| `viewed` | العميل | `{ip, user_agent}` |
| `accepted` | العميل | `{ip, signer_name}` |
| `rejected` | العميل | `{ip, reason}` |
| `converted` | المستقل | `{invoice_id}` |

### 3. إشعارات البريد الإلكتروني

```
- عند قبول العرض → بريد للمستقل: "✅ قبل {client_name} عرضك {quote_number}"
- عند رفض العرض → بريد للمستقل: "❌ رفض {client_name} عرضك"
- قبل انتهاء الصلاحية بيوم → بريد للمستقل: "⏰ ينتهي عرض {number} غداً"
```

### 4. قوالب العروض (Templates)

حفظ عرض كقالب واستخدامه لاحقاً — مفيد للمستقلين ذوي خدمات ثابتة.

---

## ملاحظات تقنية

- **SoftDeletes** مفعَّل — الوثائق المالية لا تُحذف نهائياً
- **ULID** كمفتاح مسار — يمنع التخمين التسلسلي لروابط show/edit
- **Token مستقل** عن ULID — البوابة تستخدم token والنظام الداخلي يستخدم ulid
- **global scope BelongsToUser** — كل استعلام مقيَّد بـ `user_id` تلقائياً
- **recalculate()** يُستدعى بعد كل create/update لضمان دقة الإجماليات

---

## أوامر مفيدة

```bash
# تطبيق Migrations
php artisan migrate

# فحص جداول العروض
php artisan tinker
>>> \App\Models\Quote::count()
>>> \App\Models\Quote::first()->portalUrl()
```
