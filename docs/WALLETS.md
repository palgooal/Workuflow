# موديول الصناديق والخزائن (Wallets)

> تاريخ الإنشاء: 7 يونيو 2026 | آخر تحديث: 7 يونيو 2026 | الإصدار: 1.1.0

---

## Overview

موديول Wallets يُمكّن المستخدم من تتبع رصيده الفعلي عبر صناديق متعددة (كاش، بنك، محافظ مخصصة). كل معاملة يمكن ربطها بصندوق، والتحويلات بين الصناديق مدعومة بالكامل.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| تتبع الرصيد النقدي | Wallet model بحساب ديناميكي للرصيد |
| أنواع مختلفة من الصناديق | WalletType enum: cash / bank / custom |
| ربط المعاملات بصندوق | `wallet_id` **إجباري** على كل معاملة — الأموال لا تُسجَّل بدون صندوق |
| اختيار الصندوق عند دفع الفاتورة | modal في صفحة الفاتورة يعرض الصناديق مع الرصيد |
| التحويل بين الصناديق | `wallet_transfers` table + WalletTransfer model |
| رسوم التحويل | حقل `fee` على `wallet_transfers` |
| عملات متعددة | كل صندوق له عملة مستقلة |

---

## Database Structure

### جدول `wallets`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | char(26) | | ULID — PK |
| user_id | FK → users | | |
| name | varchar(255) | | اسم الصندوق |
| type | varchar(20) | | WalletType enum |
| currency | varchar(3) | | SAR / USD / ... |
| initial_balance | decimal(15,2) | | الرصيد الافتتاحي |
| color | varchar(7) | | HEX |
| icon | varchar(10) | ✓ | Emoji اختياري |
| description | text | ✓ | |
| is_active | boolean | | true |
| created_at / updated_at | timestamps | | |

### جدول `wallet_transfers`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | char(26) | | ULID — PK |
| user_id | FK → users | | |
| from_wallet_id | char(26) → wallets | | الصندوق المصدر |
| to_wallet_id | char(26) → wallets | | الصندوق الوجهة |
| amount | decimal(15,2) | | المبلغ المحوَّل |
| fee | decimal(15,2) | | رسوم التحويل (default 0) |
| description | varchar(255) | ✓ | |
| reference | varchar(100) | ✓ | رقم مرجعي |
| transferred_at | date | | تاريخ التحويل |
| created_at / updated_at | timestamps | | |

### تعديل `transactions`

أُضيف عمود:
```
wallet_id  char(26) NOT NULL (required)  FK → wallets(id) ON DELETE SET NULL
```

> **قرار تصميمي:** `wallet_id` إجباري — كل معاملة يجب أن تنتمي لصندوق. لا يمكن تسجيل دخل أو مصروف بدون تحديد أين تذهب الأموال.

---

## Enums

### WalletType

**الموقع:** `app/Support/Enums/WalletType.php`

| Value | Label | Icon | Badge |
|-------|-------|------|-------|
| `cash` | كاش | 💵 | `bg-emerald-100 text-emerald-700` |
| `bank` | بنك | 🏦 | `bg-blue-100 text-blue-700` |
| `custom` | مخصص | 📦 | `bg-purple-100 text-purple-700` |

---

## Models

### Wallet

**الموقع:** `app/Models/Wallet.php`

#### Relations
```php
user()        → belongsTo(User::class)
transactions()→ hasMany(Transaction::class)
transfersOut()→ hasMany(WalletTransfer::class, 'from_wallet_id')
transfersIn() → hasMany(WalletTransfer::class, 'to_wallet_id')
```

#### Scopes
```php
scopeActive($query) → where('is_active', true)
```

#### حساب الرصيد
```php
balance(): float
// = initial_balance + income_transactions - expense_transactions
//   + transfers_in - transfers_out - fees_out
```

> **مهم:** الرصيد يُحسب في الذاكرة ويستدعي عدة queries — لا تستخدمه في loops كبيرة بدون eager loading.

### WalletTransfer

**الموقع:** `app/Models/WalletTransfer.php`

```php
fromWallet() → belongsTo(Wallet::class, 'from_wallet_id')
toWallet()   → belongsTo(Wallet::class, 'to_wallet_id')
```

---

## Controllers

### WalletController

**الموقع:** `app/Http/Controllers/WalletController.php`

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | /wallets | wallets.index | قائمة الصناديق |
| GET | /wallets/create | wallets.create | نموذج الإنشاء |
| POST | /wallets | wallets.store | حفظ صندوق جديد |
| GET | /wallets/{wallet} | wallets.show | تفاصيل + معاملات + تحويلات |
| GET | /wallets/{wallet}/edit | wallets.edit | نموذج التعديل |
| PUT | /wallets/{wallet} | wallets.update | حفظ التعديلات |
| DELETE | /wallets/{wallet} | wallets.destroy | حذف |
| GET | /wallets-transfer | wallets.transfer.create | نموذج التحويل |
| POST | /wallets-transfer | wallets.transfer.store | تنفيذ التحويل |

---

## Frontend

### Views

| View | Purpose |
|------|---------|
| `wallets/index.blade.php` | قائمة + ملخص per-currency |
| `wallets/_card.blade.php` | بطاقة صندوق — رصيد + دخل + مصروف |
| `wallets/create.blade.php` | نموذج إنشاء |
| `wallets/edit.blade.php` | نموذج تعديل |
| `wallets/show.blade.php` | تفاصيل + معاملات + تحويلات |
| `wallets/transfer.blade.php` | نموذج تحويل بين الصناديق |

### Sidebar
أُضيف رابط "الصناديق" في `layouts/app.blade.php` بعد "المعاملات" مباشرة.

---

## ربط المعاملات

تعديلات على الطبقة الكاملة لدعم `wallet_id`:

| الملف | التعديل |
|-------|---------|
| `Transaction::$fillable` | إضافة `wallet_id` |
| `Transaction::wallet()` | علاقة `belongsTo(Wallet::class)` |
| `TransactionData` DTO | إضافة `?string $wallet_id` |
| `CreateTransactionAction` | تمرير `wallet_id` للـ create |
| `UpdateTransactionAction` | تمرير `wallet_id` للـ update |
| `StoreTransactionRequest` | قاعدة `required\|exists:wallets,id` — إجباري |
| `UpdateTransactionRequest` | قاعدة `required\|exists:wallets,id` — إجباري |
| `TransactionController::create()` | تمرير `$wallets` للـ view |
| `TransactionController::edit()` | تمرير `$wallets` للـ view |
| `transactions/_form.blade.php` | حقل wallet بارز مع إطار ملون + رسالة "إلى أين ستذهب الأموال؟" |

---

## ربط الفواتير (markPaid)

عند تسجيل دفع فاتورة، يظهر **modal** يعرض الصناديق النشطة مع رصيد كل منها.

### التغييرات:

| الملف | التعديل |
|-------|---------|
| `InvoiceController::show()` | يُمرِّر `$wallets` للـ view |
| `InvoiceController::markPaid()` | يتحقق من `wallet_id` (required) ويمرره للمعاملة |
| `invoices/show.blade.php` | زر "تسجيل الدفع" يفتح modal بدل form مباشر |

### سلوك الـ modal:
- يعرض اسم الفاتورة والمبلغ
- يعرض جميع الصناديق النشطة مع النوع والعملة والرصيد الحالي
- الصندوق الأول محدد تلقائياً
- عند الإرسال تُسجَّل معاملة دخل مرتبطة بالصندوق المختار

```php
// InvoiceController::markPaid() — validation
$request->validate([
    'wallet_id' => ['required', 'string', 'exists:wallets,id'],
]);

// المعاملة تُنشأ مع wallet_id
$txData['wallet_id'] = $request->wallet_id;
Transaction::create($txData);
```

---

## Migration

لتفعيل الموديول شغّل:

```bash
php artisan migrate
```

ثلاث migrations بالترتيب:
1. `2026_06_07_000001_create_wallets_table`
2. `2026_06_07_000002_add_wallet_id_to_transactions_table`
3. `2026_06_07_000003_create_wallet_transfers_table`

### معالجة المعاملات القديمة بعد الـ Migration

`wallet_id` **nullable** في قاعدة البيانات، لكن **required** في الـ validation. المعاملات القديمة (قبل إضافة الصناديق) لن يمكن تعديلها حتى يُعيَّن لها صندوق.

**الحل الآلي — أمر artisan:**

```bash
# معاينة بدون تعديل
php artisan wallets:assign-default --dry-run

# تطبيق على جميع المستخدمين
php artisan wallets:assign-default

# تطبيق على مستخدم محدد
php artisan wallets:assign-default --user=USER_ID
```

**ما يفعله الأمر:**
- يبحث عن كل مستخدم لديه معاملات بدون `wallet_id`
- إذا كان لديه صناديق → يعيّن للصندوق الأقدم
- إذا لم يكن لديه صناديق → ينشئ «الصندوق العام» تلقائياً ثم يعيّن

**الملف:** `app/Console/Commands/AssignDefaultWallet.php`

> **ملاحظة UX:** عند فتح تعديل معاملة قديمة بدون صندوق، يظهر تنبيه أصفر يطلب من المستخدم تحديد صندوق قبل الحفظ.

---

## Bug Fixes History

### v1.1.0 — 7 يونيو 2026

| Bug | Root Cause | Fix |
|-----|-----------|-----|
| `BadMethodCallException: Call to undefined method Wallet::forUser()` | `BelongsToUser` لا تحتوي على `forUser()` — الـ Global Scope يفلتر تلقائياً | إزالة `forUser(auth()->id())` واستخدام `Wallet::` مباشرة |
| صفحة الصناديق فارغة | الـ views تستخدم `<x-app-layout>` بدل `@extends('layouts.app')` | إعادة كتابة Views بـ `@extends` + `@section('content')` |
| `Unknown column 'is_active'` في إنشاء/تعديل الفاتورة | `InvoiceController` يستخدم `where('is_active', true)` على جدول projects الذي تحول لـ `status` enum | استبدال بـ `Project::active()` scope |

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| تنبيه عند رصيد منخفض | متوسطة |
| تقرير حركة الصندوق PDF | منخفضة |
| ربط الصندوق بالمشروع تلقائياً | منخفضة |
| دعم تحويلات متعددة العملة مع سعر الصرف | مستقبلية |
