# موديول الصناديق والخزائن (Wallets)

> تاريخ الإنشاء: 7 يونيو 2026 | الإصدار: 1.0.0

---

## Overview

موديول Wallets يُمكّن المستخدم من تتبع رصيده الفعلي عبر صناديق متعددة (كاش، بنك، محافظ مخصصة). كل معاملة يمكن ربطها بصندوق، والتحويلات بين الصناديق مدعومة بالكامل.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| تتبع الرصيد النقدي | Wallet model بحساب ديناميكي للرصيد |
| أنواع مختلفة من الصناديق | WalletType enum: cash / bank / custom |
| ربط المعاملات بصندوق | `wallet_id` nullable FK على `transactions` |
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
wallet_id  char(26) nullable  FK → wallets(id) ON DELETE SET NULL
```

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
| `StoreTransactionRequest` | قاعدة `nullable\|exists:wallets,id` |
| `UpdateTransactionRequest` | قاعدة `nullable\|exists:wallets,id` |
| `TransactionController::create()` | تمرير `$wallets` للـ view |
| `TransactionController::edit()` | تمرير `$wallets` للـ view |
| `transactions/_form.blade.php` | حقل select للصندوق |

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

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| تنبيه عند رصيد منخفض | متوسطة |
| تقرير حركة الصندوق PDF | منخفضة |
| ربط الصندوق بالمشروع تلقائياً | منخفضة |
| دعم تحويلات متعددة العملة مع سعر الصرف | مستقبلية |
