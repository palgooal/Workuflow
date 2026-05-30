# موديول عروض الأسعار (Quotes)

> آخر تحديث: 29 مايو 2026 | الإصدار: 1.1.0

---

## Overview

موديول Quotes يُتيح للمستقل إنشاء عروض أسعار احترافية وإرسالها للعملاء عبر رابط آمن، ومتابعة حالتها من المسودة حتى التحويل لفاتورة أو مشروع. يشمل بوابة عميل عامة لا تتطلب تسجيل دخول.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| إرسال عرض سعر احترافي للعميل | ورقة عرض مُنسَّقة مع رابط آمن |
| متابعة حالة العرض | دورة حياة بـ 7 حالات مع timestamps |
| قبول/رفض العرض من العميل | بوابة عميل عامة بدون تسجيل دخول |
| تحويل العرض المقبول لفاتورة | `convertToInvoice()` مع نسخ البنود |
| تحويل العرض لمشروع + فاتورة دفعة واحدة | خيار `create_project` عند التحويل |
| تاريخ انتهاء الصلاحية | `valid_until` مع تحديث تلقائي للحالة |
| طباعة / تصدير PDF | `window.print()` بـ CSS مخصص |
| إنشاء العرض من عدة مسارات | ملف العميل، صفحة المشروع، `/quotes` |

---

## Database Structure

### Tables

#### quotes

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK | | auto | |
| ulid | char(26) | | | مفتاح المسار — unique |
| token | varchar(64) | | | رمز البوابة العامة — 48 حرف عشوائي — unique |
| user_id | FK → users | | | المالك |
| client_id | FK → clients | | | العميل المستهدف |
| project_id | char(26) | ✓ | NULL | المشروع المرتبط |
| number | varchar(50) | | | QUO-XXXX — unique per (user_id, number) |
| title | varchar(255) | ✓ | NULL | عنوان وصفي |
| status | varchar(20) | | 'draft' | QuoteStatus enum |
| issue_date | date | | | تاريخ الإصدار |
| valid_until | date | ✓ | NULL | تاريخ انتهاء الصلاحية |
| subtotal | decimal(12,2) | | 0 | المجموع الفرعي |
| tax_rate | decimal(5,2) | | 0 | نسبة الضريبة % |
| tax_amount | decimal(12,2) | | 0 | مبلغ الضريبة |
| discount | decimal(12,2) | | 0 | مبلغ الخصم |
| total | decimal(12,2) | | 0 | الإجمالي النهائي |
| currency | varchar(3) | | 'ILS' | العملة |
| notes | text | ✓ | NULL | ملاحظات للعميل |
| terms | text | ✓ | NULL | الشروط والأحكام |
| sent_at | timestamp | ✓ | NULL | وقت الإرسال |
| viewed_at | timestamp | ✓ | NULL | أول مشاهدة من العميل |
| accepted_at | timestamp | ✓ | NULL | وقت القبول |
| rejected_at | timestamp | ✓ | NULL | وقت الرفض |
| converted_at | timestamp | ✓ | NULL | وقت التحويل لفاتورة |
| client_ip | varchar(45) | ✓ | NULL | IP عند القبول/الرفض |
| rejection_reason | varchar(500) | ✓ | NULL | سبب الرفض من العميل |
| deleted_at | timestamp | ✓ | NULL | SoftDeletes |
| created_at / updated_at | timestamps | | | |

**Indexes:**
- `quotes_user_number_unique` — UNIQUE (`user_id`, `number`)
- `quotes_user_status_idx` — (`user_id`, `status`)
- `quotes_user_client_idx` — (`user_id`, `client_id`)
- `quotes_valid_until_idx` — (`valid_until`)

#### quote_items

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint PK | | |
| quote_id | FK → quotes | | cascadeOnDelete |
| description | varchar(500) | | وصف البند |
| quantity | decimal(10,2) | | الكمية |
| unit_price | decimal(12,2) | | سعر الوحدة |
| total | decimal(12,2) | | quantity × unit_price |
| sort_order | smallint | | ترتيب العرض |
| created_at / updated_at | timestamps | | |

---

## Enums

### QuoteStatus

**الموقع:** `app/Support/Enums/QuoteStatus.php`

| Value | Label | Icon | Description |
|-------|-------|------|-------------|
| `draft` | مسودة | 📝 | قابلة للتعديل |
| `sent` | مُرسَلة | 📤 | أُرسل للعميل |
| `viewed` | مشاهَدة | 👁️ | فُتح الرابط من العميل |
| `accepted` | مقبولة | ✅ | وافق العميل |
| `rejected` | مرفوضة | ❌ | رفض العميل |
| `expired` | منتهية | ⏰ | انتهت صلاحيتها |
| `converted` | محوَّلة | 🧾 | تحوّلت لفاتورة |

**قواعد الانتقال:**

```
Draft → Sent → Viewed → Accepted → Converted
                      ↘ Rejected
                (Expired: يُحسَب تلقائياً — ليس حالة نهائية)
```

| الدالة | الحالات المسموحة |
|--------|----------------|
| `isEditable()` | Draft فقط |
| `canBeSent()` | Draft, Sent, Viewed |
| `isPending()` | Sent, Viewed |
| `canConvert()` | Accepted فقط |

---

## Models

### Quote

**الموقع:** `app/Models/Quote.php`

#### Relationships

```php
user()    → belongsTo(User::class)
client()  → belongsTo(Client::class)
project() → belongsTo(Project::class)     // nullable
items()   → hasMany(QuoteItem::class)
invoice() → hasOne(Invoice::class, 'reference', 'number')  // بعد التحويل
```

#### Scopes

لا scopes مخصصة — يعتمد على `BelongsToUser` Global Scope للعزل التلقائي.

#### Boot — Auto-Generation

```php
static::creating(function (self $quote) {
    $quote->ulid   = Str::ulid()->toString();          // مفتاح مسار
    $quote->token  = Str::random(48);                  // رمز البوابة
    $quote->number = self::generateNumber($quote->user_id); // QUO-XXXX
});
```

#### Key Methods

```php
// إعادة حساب الإجماليات وحفظها
recalculate(): void
    subtotal  = sum(items.quantity × items.unit_price)
    taxAmount = round(subtotal × taxRate / 100, 2)
    total     = max(0, subtotal + taxAmount - discount)

// هل انتهت الصلاحية؟ (لا تنطبق على الحالات النهائية)
isExpired(): bool
    valid_until < today AND status NOT IN [accepted, rejected, converted]

// رابط البوابة العامة
portalUrl(): string
    route('quotes.portal', $this->token)

// توليد رقم فريد per-user
generateNumber(int $userId): string
    count = Quote::withTrashed()->where('user_id', $userId)->count()
    // + حلقة race-condition للتحقق
    return 'QUO-' . str_pad($next, 4, '0', STR_PAD_LEFT)

// Route Key
getRouteKeyName(): string → 'ulid'
```

### QuoteItem

**الموقع:** `app/Models/QuoteItem.php`

```php
quote() → belongsTo(Quote::class)
```

**Casts:** `quantity`, `unit_price`, `total` → decimal:2

---

## Services

لا يوجد QuoteService مستقل — المنطق في `QuoteController` مباشرة لأن العمليات بسيطة وغير متكررة.

---

## Controllers

### QuoteController

**الموقع:** `app/Http/Controllers/QuoteController.php`

#### المسارات المحمية (Auth Required)

| Method | Route | Middleware | Description |
|--------|-------|-----------|-------------|
| GET | /quotes | auth,verified | قائمة مع إحصائيات |
| GET | /quotes/create | auth,verified | نموذج الإنشاء |
| POST | /quotes | auth,verified | حفظ عرض جديد |
| GET | /quotes/{ulid} | auth,verified | عرض تفصيلي |
| GET | /quotes/{ulid}/edit | auth,verified | نموذج التعديل (Draft فقط) |
| PUT | /quotes/{ulid} | auth,verified | حفظ التعديلات |
| DELETE | /quotes/{ulid} | auth,verified | حذف ناعم |
| POST | /quotes/{ulid}/mark-sent | auth,verified | تغيير → Sent |
| POST | /quotes/{ulid}/convert | auth,verified | تحويل لفاتورة ± مشروع |

#### المسارات العامة (No Auth)

| Method | Route | Description |
|--------|-------|-------------|
| GET | /q/{token} | بوابة العميل — يُسجّل Viewed عند أول زيارة |
| POST | /q/{token}/accept | قبول العرض + تسجيل client_ip |
| POST | /q/{token}/reject | رفض العرض + rejection_reason + client_ip |

#### convertToInvoice — المنطق الكامل

```
Request: create_project?, project_name?, project_type?
  ↓
[اختياري] إنشاء Project (contract_value = quote.total)
         ربط project_id بالعرض
  ↓
إنشاء Invoice (نسخ: client_id, project_id, currency, tax_rate, discount, notes, terms)
  ↓
نسخ QuoteItems → InvoiceItems
  ↓
invoice.recalculate()
  ↓
quote.status = Converted, quote.converted_at = now()
  ↓
redirect → invoices.show
```

---

## Policies

لا يوجد `QuotePolicy` مستقل. الحماية تعتمد على:
- `BelongsToUser` Global Scope — يمنع رؤية عروض الآخرين تلقائياً
- `abort_if(! $quote->status->canConvert(), 422)` — فحوصات صريحة في Controller
- `abort_unless($request->user()?->hasRole('super_admin'), 403)` — في Impersonation

---

## Frontend

### Views

| View | Purpose | Alpine.js Component |
|------|---------|-------------------|
| `quotes/index.blade.php` | قائمة + إحصائيات (total/pending/accepted/rejected/converted/value) | — |
| `quotes/create.blade.php` | نموذج إنشاء ديناميكي | `quoteForm()` |
| `quotes/edit.blade.php` | نموذج تعديل مع بيانات محملة | `quoteForm()` |
| `quotes/show.blade.php` | ورقة عرض + أزرار إجراءات + modal تحويل | `open-convert-modal` event |
| `quotes/portal.blade.php` | بوابة عميل عامة (no layout) | `showRejectForm` |

### Alpine.js — quoteForm()

```javascript
{
    items: [],          // مصفوفة البنود [{description, quantity, unit_price}]
    taxRate: 0,         // نسبة الضريبة
    discount: 0,        // الخصم الثابت
    subtotal: 0,        // محسوب تلقائياً
    taxAmount: 0,       // محسوب تلقائياً
    total: 0,           // محسوب تلقائياً

    addItem()           // إضافة بند فارغ
    addServiceItem(name) // إضافة من كتالوج الخدمات
    removeItem(index)   // حذف بند (الحد الأدنى: بند واحد)
    recalc()           // إعادة حساب الإجماليات
}
```

**تحذير ParseError:** لا تستخدم `@json(old('items', [['key'=>'val']]))` — Blade لا يعالج `[` داخل `@json()` في PHP 8.2. استخدم:
```php
@php $defaultItems = old('items') ?: [['description'=>'','quantity'=>1,'unit_price'=>0]]; @endphp
@json($defaultItems)
```

---

## User Flow

```
المستقل                          العميل
  │                                │
  ▼                                │
إنشاء عرض (Draft)                 │
  │                                │
  ▼                                │
تعديل البنود والإجماليات           │
  │                                │
  ▼                                │
"تسجيل كمُرسَل" → status=Sent      │
  │                                │
  ├─── نسخ رابط العميل ────────────►│
  │    /q/{token}                   │
  │                                ▼
  │                           فتح الرابط
  │                        status = Viewed
  │                        viewed_at = now()
  │                                │
  │                        ┌───────┴───────┐
  │                        ▼               ▼
  │                   قبول العرض      رفض العرض
  │                status=Accepted  status=Rejected
  │                accepted_at      rejected_at
  │                client_ip        rejection_reason
  │                                 client_ip
  ▼
تحويل لفاتورة
[خيار: إنشاء مشروع]
status = Converted
```

---

## Security Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Ownership** | `BelongsToUser` Global Scope — تلقائي على كل استعلام |
| **Token Security** | 48 حرف Base62 عشوائي = 62^48 احتمال — مستحيل التخمين |
| **ULID vs Sequential ID** | route key = `ulid` لا `id` — يمنع enumeration |
| **SoftDeletes** | وثائق مالية لا تُحذف نهائياً |
| **Rate Limiting** | محمي بـ `web` middleware + CSRF |
| **IP Logging** | `client_ip` عند القبول/الرفض كدليل أساسي |
| **No Auth on Portal** | `/q/{token}` عام — لكن token مستحيل التخمين |

---

## Performance Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Eager Loading** | `with(['client', 'project'])` في index |
| **Indexes** | `(user_id, status)`, `(user_id, client_id)`, `(valid_until)` |
| **SoftDeletes في generateNumber** | `withTrashed()` لضمان عدم التكرار |
| **Lazy recalculate** | `recalculate()` يُستدعى بعد create/update فقط |

---

## Known Bugs Fixed

| # | الخطأ | السبب | الإصلاح |
|---|-------|-------|---------|
| 1 | `ParseError: Unclosed '['` | `@json()` + مصفوفة متداخلة | نقل إلى `@php` block |
| 2 | `Duplicate entry 'QUO-0001'` | unique عالمي على `number` | composite unique `(user_id, number)` |
| 3 | `Duplicate entry 'INV-0001'` | نفس المشكلة في invoices | نفس الإصلاح |

---

## Future Enhancements

| الميزة | الأولوية | الملاحظة |
|--------|---------|---------|
| التوقيع الرقمي | عالية | اسم + Canvas + IP + UserAgent + timestamp + base64 PNG |
| `quote_activities` audit trail | عالية | جدول يُسجّل: sent/viewed/accepted/rejected/converted/edited |
| إشعار بريدي عند القبول/الرفض | متوسطة | Mailable + Queue |
| قوالب عروض محفوظة | متوسطة | حفظ عرض كقالب وإعادة استخدامه |
| انتهاء صلاحية تلقائي | منخفضة | Scheduled Command يغيّر حالة العروض المنتهية |
| PDF عالي الجودة | منخفضة | `barryvdh/laravel-dompdf` بدلاً من `window.print()` |
