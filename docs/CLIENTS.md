# موديول إدارة العملاء (CRM / Clients)

> آخر تحديث: 29 مايو 2026 | الإصدار: 2.2.0

---

## Overview

موديول CRM هو نظام إدارة علاقات العملاء المتكامل داخل دراهم. يوفر ملف عميل 360° يجمع الفواتير والمشاريع والعروض والمتابعات ونقاط الصحة في مكان واحد. مبني كـ Laravel Module مستقل في `app/Modules/CRM/`.

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| تتبع جميع عملاء المستقل | Client Model مع BelongsToUser |
| ملف عميل 360° | صفحة show بتبويبات: نشاط/مشاريع/فواتير/عروض/متابعات |
| تصنيف العملاء بوسوم | نظام Tags متعدد مع ألوان وأيقونات |
| متابعة صحة العلاقة مع العميل | ClientHealthScoreService (خوارزمية V2) |
| جدولة متابعات (follow-ups) | نظام Follow-ups مع أنواع وأولويات |
| استيراد/تصدير العملاء | xlsx/csv مع wizard |
| أتمتة مهام متكررة | AutomationRule Engine (3 قواعد أساسية) |
| إحصائيات العميل الحية | حساب من الفواتير مباشرة لا من DB |

---

## Database Structure

### Tables

#### clients

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint PK | | |
| public_id | char(26) | | ULID — مفتاح المسار |
| user_id | FK → users | | المالك |
| name | varchar(255) | | اسم العميل |
| email | varchar(255) | ✓ | |
| phone | varchar(50) | ✓ | |
| company | varchar(255) | ✓ | اسم الشركة |
| position | varchar(100) | ✓ | المسمى الوظيفي |
| website | varchar(255) | ✓ | |
| address | varchar(255) | ✓ | |
| city | varchar(100) | ✓ | |
| country | varchar(2) | ✓ | كود ISO |
| status | varchar(20) | ✓ | ClientStatus enum |
| source | varchar(20) | ✓ | ClientSource enum |
| is_archived | boolean | | false |
| is_active | boolean | | true |
| total_revenue | decimal(12,2) | ✓ | مُحدَّث من الفواتير |
| total_paid | decimal(12,2) | ✓ | مُحدَّث عند الدفع |
| invoice_count | int | ✓ | |
| health_score | int | ✓ | 0-100 |
| last_payment_at | timestamp | ✓ | |
| last_contact_at | timestamp | ✓ | |
| notes | text | ✓ | ملاحظات خاصة |
| deleted_at | timestamp | ✓ | SoftDeletes |
| created_at / updated_at | timestamps | | |

#### client_tags

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| user_id | FK → users | |
| name | varchar(100) | |
| color | varchar(7) | HEX |
| icon | varchar(10) | ✓ Emoji |
| is_system | boolean | وسم النظام (لا يُحذف) |
| priority | int | ترتيب العرض |

#### client_tag (pivot)

| Column | Type |
|--------|------|
| client_id | FK |
| tag_id | FK |
| created_at | timestamp |

#### client_follow_ups

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| client_id | FK → clients | |
| user_id | FK → users | |
| type | varchar(20) | FollowUpType enum |
| title | varchar(255) | |
| notes | text | ✓ |
| due_at | timestamp | موعد الاستحقاق |
| priority | varchar(20) | FollowUpPriority enum |
| status | varchar(20) | pending/completed/cancelled |
| completed_at | timestamp | ✓ |

#### client_activities

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| client_id | FK → clients | |
| actor_id | FK → users | ✓ |
| type | varchar(50) | ClientActivityType enum |
| description | text | |
| occurred_at | timestamp | |

#### client_health_scores

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| client_id | FK → clients | |
| score | int | 0-100 |
| factors | json | تفاصيل كل عامل |
| scored_at | timestamp | |

#### automation_rules

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| user_id | FK → users | |
| name | varchar(255) | |
| trigger_event | varchar(50) | |
| conditions | json | |
| actions | json | |
| is_active | boolean | |

---

## Enums

### ClientStatus

| Value | Label | Description |
|-------|-------|-------------|
| `active` | نشط | عميل نشط |
| `inactive` | غير نشط | توقف التعامل |
| `lead` | عميل محتمل | لم يُكمل عملية بعد |
| `vip` | VIP | عميل مميز |

### ClientSource

| Value | Label |
|-------|-------|
| `referral` | إحالة |
| `social_media` | تواصل اجتماعي |
| `website` | الموقع |
| `direct` | مباشر |
| `other` | أخرى |

### FollowUpType

| Value | Label | Icon |
|-------|-------|------|
| `call` | اتصال | 📞 |
| `email` | بريد | 📧 |
| `meeting` | اجتماع | 🤝 |
| `task` | مهمة | ✅ |
| `note` | ملاحظة | 📌 |

### HealthScoreGrade

| Score | Grade | Label |
|-------|-------|-------|
| 75-100 | A | ممتاز |
| 50-74 | B | جيد |
| 25-49 | C | متوسط |
| 0-24 | D | ضعيف |

---

## Models

### Client

**الموقع:** `app/Models/Client.php`

#### Relationships

```php
user()        → belongsTo(User::class)
tags()        → belongsToMany(ClientTag::class, 'client_tag')
projects()    → hasMany(Project::class)
invoices()    → hasMany(Invoice::class)
quotes()      → hasMany(Quote::class)
followUps()   → hasMany(ClientFollowUp::class)
activities()  → hasMany(ClientActivity::class)
healthScores()→ hasMany(ClientHealthScore::class)
```

#### Accessors

```php
// نقاط الصحة كـ Grade
gradeAttribute(): ?HealthScoreGrade
    → HealthScoreGrade::fromScore($this->health_score)

// المبلغ المستحق (مُحسَّب)
outstandingAttribute(): float
    → max(0, total_revenue - total_paid)
```

#### Scopes

```php
scopeActive($query)       → where('is_archived', false)->where('is_active', true)
scopeNotArchived($query)  → where('is_archived', false)
```

---

## Services

### ClientService

**الموقع:** `app/Modules/CRM/Services/ClientService.php`

#### Public Methods

```php
listClients(int $userId, ClientFiltersDTO $filters): LengthAwarePaginator
findWithRelations(int $clientId, int $userId): ?Client
create(CreateClientDTO $dto): Client
update(Client $client, UpdateClientDTO $dto): Client
delete(Client $client, int $userId): void
archive(Client $client, int $userId): void
restore(Client $client, int $userId): void
stats(int $userId): array   // إحصاءات الداشبورد
```

### ClientHealthScoreService

**الموقع:** `app/Modules/CRM/Services/ClientHealthScoreService.php`

#### الخوارزمية (V2 — Recency Bias)

```
Score = Σ (factor_score × weight) × 100

الأوزان (config/crm.php → health_score.weights):
  payment_rate        0.35  — معدل الدفع (paid / invoiced)
  work_frequency      0.25  — تكرار التعاملات خلال 12 شهراً
  revenue_value       0.20  — قيمة الإيراد النسبية (≥10,000 = 1.0)
  contact_regularity  0.10  — انتظام التواصل
  response_rate       0.10  — حصة المتابعات المكتملة

Recency Bias:
  كل عامل = 70% آخر 3 أشهر + 30% آخر 12 شهراً
```

#### Public Methods

```php
calculate(Client $client): int        // يحسب ويحفظ في DB
preview(Client $client): array        // يحسب بدون حفظ
recalculateForUser(int $userId): array // batch لكل مستخدم
```

### SmartTagSuggestionService

```php
suggest(Client $client): array   // يقترح وسوماً بناءً على بيانات العميل
```

---

## Actions

### CreateClientAction

- **Input:** `CreateClientDTO` (name, email, phone, company...)
- **Output:** `Client`
- **Effects:** ينشئ العميل + يسجّل نشاط `created`

### UpdateClientAction

- **Input:** `Client`, `UpdateClientDTO`
- **Output:** `Client`
- **Effects:** يُحدِّث الحقول المتغيرة فقط عبر `toChangedArray()` + يسجّل نشاط `updated`

### LogClientActivityAction

- **Input:** `Client`, `ClientActivityType`, `string $description`, `?User $actor`
- **Output:** `ClientActivity`

---

## Controllers

### ClientController (CRM)

**الموقع:** `app/Modules/CRM/Http/Controllers/ClientController.php`

| Method | Route | Description |
|--------|-------|-------------|
| GET | /clients | قائمة مع فلاتر وبحث وإحصائيات |
| GET | /clients/create | نموذج الإنشاء |
| POST | /clients | حفظ عميل جديد |
| GET | /clients/{publicId} | ملف العميل 360° |
| GET | /clients/{publicId}/edit | نموذج التعديل |
| PUT | /clients/{publicId} | حفظ التعديلات |
| DELETE | /clients/{publicId} | حذف ناعم |
| POST | /clients/{publicId}/archive | أرشفة |
| POST | /clients/{publicId}/restore | استعادة |
| GET | /clients/{publicId}/timeline | JSON نشاطات |

**ملاحظة مهمة:** `show()` يحسب `total_revenue`, `total_paid`, `invoice_count` حياً من الفواتير ويحفظها في DB إذا تغيّرت. يحسب `health_score` إذا كان `null`.

---

## Policies

### ClientPolicy

**الموقع:** `app/Modules/CRM/Policies/ClientPolicy.php`

| Action | Rule |
|--------|------|
| viewAny | المستخدم مُسجَّل ونشط |
| view | `client.user_id === auth.id` |
| create | المستخدم مُسجَّل ونشط |
| update | `client.user_id === auth.id` |
| delete | `client.user_id === auth.id` |
| archive | `client.user_id === auth.id` |
| restore | `client.user_id === auth.id` |

---

## Frontend

### Views

| View | Purpose |
|------|---------|
| `crm/clients/index.blade.php` | قائمة + فلاتر + إحصائيات (نشط/مؤرشف/lead/VIP) |
| `crm/clients/create.blade.php` | نموذج إنشاء |
| `crm/clients/edit.blade.php` | نموذج تعديل |
| `crm/clients/show.blade.php` | ملف العميل 360° بـ 5 تبويبات |
| `crm/follow-ups/index.blade.php` | لوحة متابعات (3 أعمدة) |
| `crm/tags/index.blade.php` | إدارة الوسوم مع Drag & Drop |
| `crm/segments/index.blade.php` | الشرائح المحفوظة |
| `crm/automation-rules/index.blade.php` | قواعد الأتمتة |

### ملف العميل 360° — تبويبات

| التبويب | المحتوى |
|---------|---------|
| 📋 النشاط | Timeline مع AJAX loading |
| 📁 المشاريع | قائمة المشاريع مع إحصائيات |
| 🧾 الفواتير | قائمة الفواتير مع حالاتها |
| 📋 عروض الأسعار | قائمة العروض مع حالاتها |
| ⏰ المتابعات | متابعات العميل مع إضافة سريعة |

### KPI Cards — دعم العملات المتعددة

عند وجود فواتير بعملات مختلفة، تُعرض الإحصائيات مجمّعة حسب العملة:

```
[إجمالي الإيراد]    [إجمالي المدفوع]    [المستحق]    [نقاط الصحة]
  5,000 ILS           3,000 ILS          2,000 ILS      78/100
  $1,200              $800               $400
```

---

## User Flow

```
المستخدم
  │
  ▼
/clients — قائمة العملاء
  │
  ├─ إنشاء عميل جديد
  │   └─ CreateClientAction → LogActivity('created')
  │
  └─ ملف العميل /clients/{id}
      │
      ├─ تبويب النشاط ← AJAX /timeline
      ├─ تبويب المشاريع ← $projects (eager loaded)
      ├─ تبويب الفواتير ← $clientInvoices (per-currency stats)
      ├─ تبويب العروض ← $clientQuotes
      └─ تبويب المتابعات ← follow-ups CRUD
```

---

## Security Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Ownership** | `BelongsToUser` Global Scope + `ClientPolicy` |
| **SoftDeletes** | بيانات العملاء لا تُحذف نهائياً |
| **Archived Clients** | محمية من التعديل الجزئي |
| **Public ID** | ULID في الـ URL لا `id` رقمي |

---

## Performance Considerations

| الاعتبار | التطبيق |
|---------|---------|
| **Eager Loading** | `with(['tags', 'client'])` في القوائم |
| **Chunked Processing** | `chunkById()` في `recalculateForUser()` |
| **Cache** | Tag suggestions مُخزَّنة مؤقتاً |
| **Lazy Stats** | `total_revenue/total_paid` تُحسَب عند الحاجة لا دائماً |

---

## Artisan Commands

```bash
# إعادة حساب health_score لجميع العملاء
php artisan crm:recalculate-health {userId?}

# تحديث الشرائح (Segments)
php artisan crm:refresh-segments {userId?}

# مزامنة إحصائيات العملاء مع الفواتير
php artisan crm:reconcile-stats {userId?}
```

---

## Future Enhancements

| الميزة | الأولوية |
|--------|---------|
| CRM Sprint 8 — Client Portal (Business Plan) | عالية |
| إرسال بريد إلكتروني من ملف العميل | متوسطة |
| تصدير ملف العميل كـ PDF | متوسطة |
| ربط العميل بحساب بريد (Gmail/Outlook) | منخفضة |
| AI insights لتقييم العميل | مستقبلية |
