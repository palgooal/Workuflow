# موديول الفواتير (Invoices)

> آخر تحديث: 29 مايو 2026 | الإصدار: 1.2.0

---

## Overview

موديول Invoices يُتيح للمستقل إنشاء فواتير احترافية وإرسالها للعملاء، وتتبع حالة الدفع، والتكامل التلقائي مع معاملات الدخل. يدعم الإنشاء التلقائي من المشاريع ومن عروض الأسعار.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| إنشاء فاتورة احترافية | ورقة فاتورة مُنسَّقة مع بنود وإجماليات |
| تتبع حالة الفاتورة | دورة حياة بـ 5 حالات |
| تسجيل الدفع تلقائياً كمعاملة دخل | `markPaid()` ينشئ Transaction |
| إنشاء فاتورة تلقائياً من المشروع | `ProjectController::store()` |
| تحويل عرض سعر لفاتورة | `QuoteController::convertToInvoice()` |
| طباعة / تصدير PDF | CSS @media print |
| ربط فاتورة بعميل | `client_id` FK |
| ربط فاتورة بمشروع | `project_id` FK (nullable) |

---

## Database Structure

### Tables

#### invoices

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK | | | |
| ulid | char(26) | | | مفتاح المسار — unique |
| user_id | FK → users | | | |
| client_id | FK → clients | | | |
| project_id | char(26) | ✓ | NULL | FK → projects.id (ULID) |
| number | varchar(50) | | | INV-XXXX — unique per (user_id, number) |
| title | varchar(255) | ✓ | NULL | |
| status | varchar(20) | | 'draft' | InvoiceStatus enum |
| issue_date | date | | | |
| due_date | date | ✓ | NULL | تاريخ الاستحقاق |
| paid_at | timestamp | ✓ | NULL | وقت تسجيل الدفع |
| currency | varchar(3) | | 'ILS' | |
| subtotal | decimal(12,2) | | 0 | |
| tax_rate | decimal(5,2) | | 0 | |
| tax_amount | decimal(12,2) | | 0 | |
| discount | decimal(12,2) | | 0 | |
| total | decimal(12,2) | | 0 | |
| notes | text | ✓ | NULL | |
| terms | text | ✓ | NULL | |
| reference | varchar(100) | ✓ | NULL | رقم العرض عند التحويل |
| deleted_at | timestamp | ✓ | NULL | SoftDeletes |
| created_at / updated_at | timestamps | | | |

**Indexes:**
- `invoices_user_number_unique` — UNIQUE (`user_id`, `number`)
- `invoices_user_status_idx` — (`user_id`, `status`)
- `invoices_user_client_idx` — (`user_id`, `client_id`)

#### invoice_items

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint PK | | |
| invoice_id | FK → invoices | | cascadeOnDelete |
| description | varchar(500) | | وصف البند |
| quantity | decimal(10,2) | | |
| unit_price | decimal(12,2) | | |
| total | decimal(12,2) | | quantity × unit_price |
| sort_order | smallint | | ترتيب العرض |
| created_at / updated_at | timestamps | | |

---

## Enums

### InvoiceStatus

**الموقع:** `app/Support/Enums/InvoiceStatus.php`

| Value | Label | Icon | Description |
|-------|-------|------|-------------|
| `draft` | مسودة | 📝 | قابلة للتعديل |
| `sent` | مُرسَلة | 📤 | أُرسل للعميل |
| `paid` | مدفوعة | ✅ | سُدِّدت |
| `overdue` | متأخرة | ⚠️ | تجاوزت due_date |
| `cancelled` | ملغاة | ❌ | |

**دورة الحياة:**

```
Draft → Sent → Paid
             ↓ (عند تجاوز due_date تلقائياً)
           Overdue → Paid
Draft/Sent/Overdue → Cancelled
```

---

## Models

### Invoice

**الموقع:** `app/Models/Invoice.php`

#### Relationships

```php
user()    → belongsTo(User::class)
client()  → belongsTo(Client::class)
project() → belongsTo(Project::class)      // nullable
items()   → hasMany(InvoiceItem::class)
```

#### Boot — Auto-Generation

```php
static::creating(function (self $invoice) {
    $invoice->ulid   = Str::ulid()->toString();
    $invoice->number = self::generateNumber($invoice->user_id);
});
```

#### Key Methods

```php
// إعادة حساب الإجماليات وحفظها
recalculate(): void
    subtotal  = sum(items.quantity × items.unit_price)
    taxAmount = round(subtotal × taxRate / 100, 2)
    total     = max(0, subtotal + taxAmount - discount)

// هل الفاتورة متأخرة؟
isOverdue(): bool
    due_date < today AND status NOT IN [paid, cancelled]

// توليد رقم فريد per-user
generateNumber(int $userId): string
    // عدّ withTrashed() + حلقة race-condition
    return 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT)

// Route Key
getRouteKeyName(): string → 'ulid'
```

### InvoiceItem

```php
invoice() → belongsTo(Invoice::class)
```

**Casts:** `quantity`, `unit_price`, `total` → decimal:2

---

## Controllers

### InvoiceController

**الموقع:** `app/Http/Controllers/InvoiceController.php`

| Method | Route | Description |
|--------|-------|-------------|
| GET | /invoices | قائمة مع إحصائيات |
| GET | /invoices/create | نموذج الإنشاء |
| POST | /invoices | حفظ فاتورة جديدة |
| GET | /invoices/{ulid} | عرض تفصيلي |
| GET | /invoices/{ulid}/edit | نموذج التعديل |
| PUT | /invoices/{ulid} | حفظ التعديلات |
| DELETE | /invoices/{ulid} | حذف ناعم |
| POST | /invoices/{ulid}/mark-sent | → Sent |
| POST | /invoices/{ulid}/mark-paid | → Paid + Transaction |
| POST | /invoices/{ulid}/cancel | → Cancelled |

#### markPaid() — المنطق الكامل

```
invoice.status = Paid
invoice.paid_at = now()
  ↓
إنشاء Transaction:
  type     = Income
  amount   = invoice.total
  payee    = client.name
  reference = invoice.number
  [project_id إذا وُجد]
  ↓
تحديث Client:
  total_paid      += amount
  total_revenue   = sum(non-cancelled invoices)
  last_payment_at = now()
```

---

## الإنشاء التلقائي من المشروع

عند إنشاء مشروع جديد مرتبط بعميل، تُنشأ فاتورة مسودة تلقائياً:

```php
// ProjectController::store()
if (! empty($validated['client_id'])) {
    $this->createDraftInvoice($project, $validated);
}

// private createDraftInvoice()
// تصفية: فقط الخدمات من نوع 'income' تصبح بنوداً في الفاتورة
$services = array_filter(
    $validated['services'] ?? [],
    fn ($svc) => ($svc['type'] ?? 'income') === 'income'
);
```

**لماذا income فقط؟** خدمات type=expense هي تكاليف تشغيلية (أدوات، مساعدون) — لا تُفوتَّر للعميل.

---

## Frontend

### Views

| View | Purpose | Alpine.js |
|------|---------|-----------|
| `invoices/index.blade.php` | قائمة + إحصائيات + pagination | — |
| `invoices/create.blade.php` | نموذج إنشاء ديناميكي | `invoiceForm()` |
| `invoices/edit.blade.php` | نموذج تعديل مع بيانات محملة | `invoiceForm()` |
| `invoices/show.blade.php` | ورقة فاتورة + أزرار إجراءات | — |

---

## User Flow

```
المستقل
  │
  ├─ إنشاء يدوي: /invoices/create
  ├─ إنشاء تلقائي: عند إنشاء مشروع جديد
  └─ تحويل من عرض: QuoteController::convertToInvoice()
       │
       ▼
  مسودة (Draft)
       │
       ▼ markSent()
  مُرسَلة (Sent)
       │
       ▼ markPaid()
  مدفوعة (Paid)
  + Transaction دخل تلقائياً
  + تحديث إحصائيات العميل
```

---

## Security Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Ownership** | `BelongsToUser` Global Scope |
| **SoftDeletes** | وثائق مالية محمية من الحذف النهائي |
| **Project FK** | `char(26)` لتوافق ULID — لا unsignedBigInteger |
| **Null project_id** | لا يُرسَل صراحةً — يُستخدم `if ($invoice->project_id)` |

---

## Known Bugs Fixed

| # | الخطأ | السبب | الإصلاح |
|---|-------|-------|---------|
| 1 | `SQLSTATE 3780` FK مجتمع | `project_id` كان `unsignedBigInteger` | تغيير إلى `char(26)` |
| 2 | `SQLSTATE 1364` NOT NULL | `transactions.project_id` schema drift | Migration: `->nullable()->change()` |
| 3 | `SQLSTATE 1048` null يلغي DEFAULT | `null` صريح في INSERT | حذف المفتاح من المصفوفة |
| 4 | `Duplicate entry 'INV-0001'` | unique عالمي | composite unique `(user_id, number)` |
| 5 | إحصائيات العميل = 0 | قيم DB لم تُحدَّث | حساب حي + تحديث عند كل دفع |

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| إرسال الفاتورة بالبريد الإلكتروني | عالية |
| قوالب فواتير قابلة للتخصيص (شعار + ألوان) | متوسطة |
| تصدير PDF عالي الجودة (dompdf) | متوسطة |
| تذكيرات تلقائية للفواتير المتأخرة | متوسطة |
| بوابة عميل لعرض وتحميل الفواتير | منخفضة |
| دفع إلكتروني مباشر من الفاتورة | مستقبلية |
