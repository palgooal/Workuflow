# موديول المشاريع (Projects)

> آخر تحديث: 29 مايو 2026 | الإصدار: 2.1.0

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
| is_active | boolean | | true |
| contract_value | decimal(12,2) | ✓ | قيمة العقد |
| expense_budget | decimal(12,2) | ✓ | ميزانية التكاليف |
| deleted_at | timestamp | ✓ | SoftDeletes |
| created_at / updated_at | timestamps | | |

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
scopeActive($query)   → where('is_active', true)
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
| POST | /projects/{id}/pay-team/{service} | دفع تكلفة عضو الفريق |

---

## Frontend

### Views

| View | Purpose |
|------|---------|
| `projects/index.blade.php` | قائمة + Portfolio KPIs per-currency |
| `projects/show.blade.php` | KPIs + progress bars + معاملات + عروض |
| `projects/create.blade.php` | نموذج الإنشاء |
| `projects/edit.blade.php` | نموذج التعديل |

### Multi-Currency Display

```
عملة واحدة → 4 بطاقات KPI (دخل + مصروف + صافي + هامش)
عملات متعددة → جدول 5 أعمدة (العملة | الدخل | المصروف | الصافي | الهامش)
              + بانر: "مقارنة العقد والميزانية بعملة المشروع (ILS) فقط"
```

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| Milestones وتقسيم المراحل | متوسطة |
| تقرير ربحية المشروع PDF | متوسطة |
| مؤشر صحة المشروع | منخفضة |
