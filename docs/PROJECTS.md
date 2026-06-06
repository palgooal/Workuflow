# موديول المشاريع (Projects)

> آخر تحديث: 7 يونيو 2026 | الإصدار: 2.2.0

---

## Overview

موديول Projects هو المحور الأساسي لعزل البيانات المالية. كل مشروع له إيراداته ومصروفاته وأرباحه منفصلة. يدعم ربط العملاء والخدمات وأعضاء الفريق، ويُنشئ فاتورة تلقائياً عند الإنشاء.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| عزل مالي لكل مشروع | Project ← Transactions |
| ربط المشروع بعميل | `client_id` FK |
| ربط الخدمات بمشروع | Pivot `project_service` |
| تتبع قيمة العقد | `contract_value` مع progress bar |
| ميزانية التكاليف | `expense_budget` مع تنبيه تجاوز |
| إنشاء فاتورة تلقائياً | عند إنشاء مشروع جديد بعميل |
| ملخص مالي per-currency | `ProjectFinancialService::getSummary()` |
| أرشفة المشاريع المنتهية | `SoftDeletes` |

---

## Database Structure

### Tables

#### projects

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | char(26) | | ULID — PK |
| user_id | FK → users | | |
| client_id | FK → clients | ✓ | |
| name | varchar(255) | | |
| description | text | ✓ | |
| color | varchar(7) | | HEX |
| currency | varchar(3) | | |
| type | varchar(20) | | ProjectType enum |
| status | varchar(20) | | ProjectStatus enum (default: active) |
| contract_value | decimal(12,2) | ✓ | قيمة العقد |
| expense_budget | decimal(12,2) | ✓ | ميزانية التكاليف |
| deleted_at | timestamp | ✓ | SoftDeletes |
| created_at / updated_at | timestamps | | |

> **ملاحظة:** `is_active` ليست عمود حقيقي — هو accessor `getIsActiveAttribute()` للتوافق مع الكود القديم، يُرجع `true` إذا كان `status === ProjectStatus::Active`.

#### project_service (pivot)

| Column | Type | Description |
|--------|------|-------------|
| project_id | char(26) | |
| service_id | FK → services | |
| amount | decimal(12,2) | ✓ |
| type | varchar(10) | income/expense |
| client_id | FK → clients | ✓ |
| notes | text | ✓ |
| team_member_id | FK → team_members | ✓ |
| team_cost | decimal(12,2) | ✓ |
| team_cost_paid | boolean | |

---

## Enums

### ProjectType

| Value | Label | Icon |
|-------|-------|------|
| `business` | تجاري | 💼 |
| `personal` | شخصي | 🏠 |

### ProjectStatus

**الموقع:** `app/Support/Enums/ProjectStatus.php`

| Value | Label | Icon | Badge (Tailwind) |
|-------|-------|------|-----------------|
| `active` | نشط | 🟢 | `bg-emerald-100 text-emerald-700` |
| `completed` | مكتمل | ✅ | `bg-blue-100 text-blue-700` |
| `on_hold` | متوقف | ⏸ | `bg-amber-100 text-amber-700` |
| `cancelled` | ملغي | ❌ | `bg-red-100 text-red-700` |

**Methods:**
```php
->label()        // النص العربي
->icon()         // Emoji
->color()        // green | blue | amber | red
->tailwindBadge()// CSS classes للـ badge
->isActive()     // bool — هل الحالة نشطة؟
->sortOrder()    // ترتيب للعرض (1..4)
```

**تغيير الحالة من الواجهة:** كل بطاقة مشروع تحتوي على sub-menu يُرسل `PATCH /projects/{id}/status` مع `status` المختارة.

---

## Models

### Project

**الموقع:** `app/Models/Project.php`

#### Relationships

```php
user()        → belongsTo(User::class)
client()      → belongsTo(Client::class)
transactions()→ hasMany(Transaction::class)
invoices()    → hasMany(Invoice::class)
quotes()      → hasMany(Quote::class)
services()    → belongsToMany(Service::class, 'project_service')
              →  ->withPivot(['amount', 'type', 'client_id', 'team_member_id', 'team_cost', 'team_cost_paid'])
```

#### Scopes

```php
scopeActive($query)   → where('status', ProjectStatus::Active)
scopeBusiness($query) → where('type', ProjectType::Business)
scopePersonal($query) → where('type', ProjectType::Personal)
```

#### Key Methods

```php
totalIncome(): float    → transactions Income sum
totalExpenses(): float  → transactions Expense sum
```

---

## Services

### ProjectFinancialService

**الموقع:** `app/Modules/Projects/Services/ProjectFinancialService.php`

#### getSummary(Project $project): array

يُرجع ملخصاً مالياً شاملاً مع دعم per-currency:

```php
[
    'by_currency'      => ['ILS' => ['income'=>5000, 'expenses'=>1200, 'net'=>3800, 'margin'=>76.0]],
    'multi_currency'   => false,
    'project_currency' => 'ILS',
    // للتوافق — قيم عملة المشروع:
    'income'           => 5000,
    'expenses'         => 1200,
    'net_profit'       => 3800,
    'margin'           => 76.0,     // % صافي الربح
    'tx_count'         => 15,
    'last_activity'    => Carbon,
    // قيمة العقد:
    'contract_value'   => 10000,
    'contract_collected'=> 50.0,   // %
    'contract_remaining'=> 5000,
    // الميزانية:
    'expense_budget'   => 2000,
    'budget_used_percent'=> 60.0,
    'budget_remaining' => 800,
    'budget_overrun'   => false,
]
```

**ملاحظة:** قيمة العقد والميزانية تُقارَن بعملة المشروع فقط حتى عند وجود عملات متعددة.

#### getPortfolioSummary(): array

ملخص كل مشاريع المستخدم per-currency.

---

## Controllers

### ProjectController

| Method | Route | Description |
|--------|-------|-------------|
| GET | /projects | قائمة + Portfolio Summary per-currency |
| GET | /projects/create | نموذج الإنشاء |
| POST | /projects | حفظ + إنشاء فاتورة تلقائية |
| GET | /projects/{id} | صفحة المشروع مع KPIs + معاملات + عروض |
| GET | /projects/{id}/edit | نموذج التعديل |
| PUT | /projects/{id} | حفظ التعديلات |
| DELETE | /projects/{id} | حذف ناعم |
| PATCH | /projects/{id}/status | تغيير حالة المشروع |
| POST | /projects/{id}/pay-team/{service} | دفع تكلفة عضو الفريق |

#### updateStatus — تفاصيل

```php
// Validation
'status' => ['required', 'in:active,completed,on_hold,cancelled']

// Logic
$newStatus = ProjectStatus::from($request->status);
$project->update(['status' => $newStatus]);
return back()->with('success', '...');
```

> **تنبيه:** يتطلب `use Illuminate\Http\Request` في أعلى الـ controller. غيابه يُسبب `ReflectionException: Class "App\Http\Controllers\Request" does not exist`.

---

## Frontend

### Views

| View | Purpose |
|------|---------|
| `projects/index.blade.php` | قائمة + Portfolio KPIs per-currency |
| `projects/show.blade.php` | KPIs + progress bars + معاملات + عروض |
| `projects/create.blade.php` | نموذج الإنشاء |
| `projects/edit.blade.php` | نموذج التعديل |
| `projects/_card.blade.php` | بطاقة المشروع — Alpine.js dropdown لتغيير الحالة |

### بطاقة المشروع — `_card.blade.php`

كل بطاقة تستخدم Alpine.js بمتغيرين:

```js
x-data="{ menuOpen: false, statusOpen: false }"
```

**هيكل القائمة:**
```
⋮ (زر النقاط) → menuOpen toggle
  └── عرض التفاصيل
  └── تعديل
  └── [اسم الحالة الحالية] → statusOpen toggle
        └── [الحالات الأخرى] — كل حالة داخل <form method="POST" PATCH>
  └── حذف
```

**قرارات تصميمية مهمة:**

| القرار | السبب |
|--------|-------|
| `@click.outside` على `div.relative` (الحاوي) لا على زر النقاط | وضعه على الزر يُغلق القائمة عند الضغط على أي عنصر داخلها لأن الضغط يقع خارج الزر نفسه |
| `overflow: visible` على البطاقة (بدون `overflow-hidden`) | `overflow-hidden` يقطع الـ dropdown ويخفي خيارات الحالة |
| `rounded-t-2xl` على شريط اللون العلوي | بديل `overflow-hidden` للحفاظ على الشكل الدائري للزوايا |
| الـ dropdown يفتح لأعلى (`bottom-full mb-1`) | فتحه لأسفل يتجاوز حدود الـ viewport ويقطع خيار "ملغي" |

### Multi-Currency Display

```
عملة واحدة → 4 بطاقات KPI (دخل + مصروف + صافي + هامش)
عملات متعددة → جدول 5 أعمدة (العملة | الدخل | المصروف | الصافي | الهامش)
              + بانر: "مقارنة العقد والميزانية بعملة المشروع (ILS) فقط"
```

---

---

## Bug Fixes History

### v2.2.0 — 7 يونيو 2026

| Bug | Root Cause | Fix |
|-----|-----------|-----|
| تغيير الحالة يُغلق القائمة فوراً | `@click.outside` على زر ⋮ يُطلق عند الضغط داخل الـ dropdown | نقل `@click.outside` إلى `div.relative` الحاوي |
| خيار "ملغي" غير مرئي في sub-menu | `overflow-hidden` على البطاقة + dropdown يفتح لأسفل خارج الـ viewport | إزالة `overflow-hidden`، إضافة `rounded-t-2xl` لشريط اللون، تغيير فتح الـ dropdown لأعلى |
| 500 عند إرسال تغيير الحالة | `use Illuminate\Http\Request` مفقود في `ProjectController` | إضافة الـ import |

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| Milestones وتقسيم المراحل | متوسطة |
| تقرير ربحية المشروع PDF | متوسطة |
| مؤشر صحة المشروع | منخفضة |
