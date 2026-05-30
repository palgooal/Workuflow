# موديول المعاملات (Transactions)

> آخر تحديث: 29 مايو 2026 | الإصدار: 2.1.0

---

## Overview

موديول Transactions هو المحرك المالي الأساسي في دراهم. كل دخل أو مصروف يُسجَّل كـ Transaction مرتبطة بمشروع وفئة. يدعم ملخصاً مالياً per-currency عند وجود عملات متعددة.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| تسجيل الدخل والمصروفات | Transaction مع type enum |
| ربط المعاملة بمشروع | `project_id` nullable FK |
| تصنيف المعاملات | `category_id` FK |
| فلترة وبحث | TransactionService::getPaginated() |
| ملخص مالي per-currency | TransactionService::getSummary() |
| إنشاء تلقائي عند دفع فاتورة | InvoiceController::markPaid() |
| معاملات متكررة | RecurringTransaction + Scheduler |

---

## Database Structure

### Tables

#### transactions

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | char(26) | | ULID — PK |
| user_id | FK → users | | |
| project_id | char(26) | ✓ | FK → projects.id |
| category_id | FK → categories | ✓ | |
| type | varchar(20) | | TransactionType enum |
| amount | decimal(12,2) | | |
| currency | varchar(3) | | |
| description | varchar(500) | | |
| payee | varchar(255) | ✓ | المستفيد أو الدافع |
| transaction_date | date | | |
| reference | varchar(100) | ✓ | رقم مرجعي |
| notes | text | ✓ | |
| deleted_at | timestamp | ✓ | SoftDeletes |
| created_at / updated_at | timestamps | | |

**Indexes:** `(user_id, transaction_date)`, `(user_id, type)`, `(project_id)`

---

## Enums

### TransactionType

| Value | Label |
|-------|-------|
| `income` | دخل |
| `expense` | مصروف |
| `transfer` | تحويل |

---

## Models

### Transaction

**الموقع:** `app/Models/Transaction.php`

#### Relationships

```php
user()     → belongsTo(User::class)
project()  → belongsTo(Project::class)   // nullable
category() → belongsTo(Category::class)  // nullable
```

#### Key Methods

```php
isIncome(): bool   → type === TransactionType::Income
isExpense(): bool  → type === TransactionType::Expense
```

---

## Services

### TransactionService

**الموقع:** `app/Modules/Transactions/Services/TransactionService.php`

#### getPaginated(Request $request): LengthAwarePaginator

فلاتر: type, project, category, date_from, date_to, search

#### getSummary(Request $request): array

```php
// يُرجع ملخصاً per-currency
[
    'by_currency' => [
        'ILS' => ['income' => 5000, 'expenses' => 1200, 'net' => 3800],
        'USD' => ['income' => 800,  'expenses' => 200,  'net' => 600],
    ],
    'multi_currency' => true,
    // للتوافق:
    'income'   => 5000,   // أول عملة
    'expenses' => 1200,
    'net'      => 3800,
    'count'    => 45,
]
```

**ملاحظة:** الفلاتر type, category, project, date مُطبَّقة قبل التجميع — الملخص يعكس الفلتر الحالي.

---

## Controllers

### TransactionController

| Method | Route | Description |
|--------|-------|-------------|
| GET | /transactions | قائمة + summary per-currency |
| GET | /transactions/create | نموذج الإنشاء |
| POST | /transactions | حفظ معاملة جديدة |
| GET | /transactions/{id}/edit | نموذج التعديل |
| PUT | /transactions/{id} | حفظ التعديلات |
| DELETE | /transactions/{id} | حذف ناعم |

---

## Frontend

### Views

| View | Purpose |
|------|---------|
| `transactions/index.blade.php` | قائمة + ملخص per-currency |
| `transactions/create.blade.php` | نموذج ديناميكي |
| `transactions/edit.blade.php` | نموذج تعديل |

### Multi-Currency Display

```
عملة واحدة → 3 بطاقات (دخل + مصروف + صافي)
عملات متعددة → جدول (العملة | الدخل | المصروف | الصافي)
              + بانر تحذيري
```

---

## Security Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Ownership** | BelongsToUser Global Scope |
| **SoftDeletes** | وثائق مالية محمية |
| **project_id null** | `if ($project_id) { $data['project_id'] = ... }` — لا يُرسَل صراحةً |

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| تصدير CSV/Excel | متوسطة |
| استيراد من CSV | منخفضة |
| مسح الإيصالات OCR | مستقبلية |
