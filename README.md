# 💰 دراهم — SaaS Financial Platform

> نظّم فلوسك ومشاريعك كلها من مكان واحد واعرف بالضبط أين يذهب ربحك.

---

## 📚 وثائق المشروع

| الملف | الوصف |
|-------|-------|
| [📋 PROJECT.md](docs/PROJECT.md) | وصف المشروع — المشكلة، الحل، المميزات، الأهداف |
| [🏗️ ARCHITECTURE.md](docs/ARCHITECTURE.md) | الهيكل التقني — DB، Folder Structure، القرارات المعمارية |
| [✅ TASKS.md](docs/TASKS.md) | خطة المهام — 52 مهمة موزعة على 14 مرحلة |

> **قبل أي تطوير — اقرأ الوثائق أولاً.**

---

## 🚀 التقنيات

| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| Laravel | 12.x | Backend Framework |
| PHP | 8.3+ | Language |
| MySQL | 8.0+ | Database |
| Tailwind CSS | 3.x | Styling |
| Alpine.js | 3.x | Frontend Interactions |
| Vite | Latest | Asset Bundling |

---

## ⚙️ متطلبات التشغيل

- PHP >= 8.3
- Composer >= 2.x
- Node.js >= 20.x
- MySQL >= 8.0

---

## 🛠️ تثبيت المشروع

```bash
# 1. استنساخ المشروع
git clone https://github.com/your-org/workuflow.git
cd workuflow

# 2. تثبيت حزم PHP
composer install

# 3. تثبيت حزم Node
npm install

# 4. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 5. إعداد قاعدة البيانات
php artisan migrate --seed

# 6. تشغيل المشروع
php artisan serve
npm run dev
```

---

## 🗂️ هيكل المجلدات الرئيسي

```
workuflow/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers رفيعة
│   │   ├── Requests/           # Form Request Validation
│   │   └── Resources/          # API Resources
│   ├── Models/                 # Eloquent Models
│   ├── Modules/                # موديولات الأعمال
│   │   ├── Projects/
│   │   ├── Transactions/
│   │   ├── Debts/
│   │   ├── Budget/
│   │   ├── Recurring/
│   │   └── Reports/
│   ├── Services/               # خدمات مشتركة
│   └── Support/
│       ├── Enums/              # PHP Enums
│       ├── Traits/             # Reusable Traits
│       └── Helpers/
├── docs/                       # وثائق المشروع
│   ├── PROJECT.md
│   ├── ARCHITECTURE.md
│   └── TASKS.md
├── resources/
│   └── views/                  # Blade Templates
└── tests/
    └── Feature/                # Feature Tests
```

---

## 📦 موديولات النظام

| # | الموديول | الحالة |
|---|---------|--------|
| 1 | 🔐 Authentication | ⬜ pending |
| 2 | 📊 Dashboard | ⬜ pending |
| 3 | 📁 Projects | ⬜ pending |
| 4 | 💸 Transactions | ⬜ pending |
| 5 | 🏷️ Categories | ⬜ pending |
| 6 | 💰 Budget | ⬜ pending |
| 7 | 🔁 Recurring Transactions | ⬜ pending |
| 8 | 💳 Debts & Liabilities | ⬜ pending |
| 9 | 📈 Reports & Analytics | ⬜ pending |
| 10 | 🔔 Notifications | ⬜ pending |
| 11 | 💼 Subscriptions & Billing | ⬜ pending |
| 12 | ⚙️ Settings | ⬜ pending |

---

## 🧱 قواعد التطوير

> يجب الالتزام بهذه القواعد في كل سطر كود يُكتب.

### 1. Controllers رفيعة دائماً
```php
// ✅ صحيح
public function store(StoreTransactionRequest $request): RedirectResponse
{
    $transaction = app(CreateTransactionAction::class)->execute(
        TransactionData::fromRequest($request)
    );
    return redirect()->route('transactions.index')->with('success', 'تمت الإضافة');
}
```

### 2. Action Pattern لكل عملية
```php
// كل عملية = Action منفصل في app/Modules/{Module}/Actions/
class CreateTransactionAction
{
    public function execute(TransactionData $data): Transaction { ... }
}
```

### 3. BelongsToUser Trait إلزامي
```php
// كل Model يملكه المستخدم يجب أن يستخدم هذا الـ Trait
class Transaction extends Model
{
    use BelongsToUser; // عزل تلقائي لبيانات كل مستخدم
}
```

### 4. Enums بدلاً من Strings
```php
// ✅ صحيح
TransactionType::Income
// ❌ خطأ
'income'
```

### 5. لا N+1 Queries
```php
// ✅ صحيح
Project::with(['transactions', 'debts'])->get();
// ❌ خطأ — loop بدون eager loading
```

---

## 🔑 متغيرات البيئة المهمة

```env
APP_NAME=دراهم
APP_ENV=local
APP_URL=http://workuflow.test

DB_CONNECTION=mysql
DB_DATABASE=workuflow

QUEUE_CONNECTION=redis
CACHE_STORE=redis

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

---

## 🗺️ خطة البناء

```
Phase 1  → الأساس (DB + Enums + Models)
Phase 2  → المصادقة
Phase 3  → Layout + Components
Phase 4  → المشاريع + الشخصي/التجاري + الميزانية
Phase 5  → الفئات + Recurring
Phase 6  → المعاملات ⭐ (المحرك الأساسي)
Phase 7  → لوحة التحكم
Phase 8  → الديون
Phase 9  → التقارير
Phase 10 → الإشعارات
Phase 11 → الاشتراكات
Phase 12 → الإعدادات
Phase 13 → الأمان والجودة
Phase 14 → الإنتاج والـ API
```

---

*دراهم © 2026 — SaaS Financial Platform*
