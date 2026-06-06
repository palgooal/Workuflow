# توثيق — إدارة الفريق والخدمات في المشاريع
## دراهم | مال وأعمال

> تاريخ التوثيق: يونيو 2026
> الحالة: مخطط للتنفيذ بالترتيب

---

## أولاً — القرارات المنفذة (ما تم تغييره)

### 1. حذف ميزانية التكاليف (expense_budget)

**القرار:** حذف حقل `expense_budget` من نموذج إنشاء وتعديل المشروع.

**السبب:**
- الجمهور المستهدف (مستقلون وأصحاب أعمال صغيرة) لا يفكر بمنطق "سقف المصروفات"
- يخلق تساؤلاً: أين أسجّل التكاليف — هنا أم في المعاملات؟
- المعاملات هي المكان الصحيح لتسجيل المصروفات

**الملفات المعدّلة:**
| الملف | التغيير |
|-------|---------|
| `resources/views/projects/_form.blade.php` | حذف حقل الإدخال |
| `app/Http/Requests/Projects/StoreProjectRequest.php` | حذف قاعدة التحقق |
| `app/Http/Requests/Projects/UpdateProjectRequest.php` | حذف قاعدة التحقق |
| `app/Modules/Projects/DTOs/ProjectData.php` | حذف الخاصية والـ mapping |
| `app/Modules/Projects/Actions/CreateProjectAction.php` | حذف من array الحفظ |
| `app/Modules/Projects/Actions/UpdateProjectAction.php` | حذف من array التحديث |

**ما تُرك كما هو:**
- عمود `expense_budget` في الداتابيز — محفوظ لسلامة البيانات القديمة
- `ProjectFinancialService` — لا يزال يقرأه لكنه يعود null لجميع المشاريع الجديدة
- `show.blade.php` — العرض محاط بـ `@if($summary['expense_budget'])` فلن يظهر

---

### 2. حذف نوع "مصروف" من الخدمات

**القرار:** الخدمات دائماً من نوع `income` — حذف خيار `expense` من الـ UI تماماً.

**السبب:**
- "الخدمات المقدمة" = ما تبيعه للعميل = دخل بطبيعته
- إذا كان هناك مصروف (مورد خارجي) فمكانه في `team_cost` أو في المعاملات
- خيار "مصروف" كان يُربك المستخدم في سياق الخدمات

**الملفات المعدّلة:**
| الملف | التغيير |
|-------|---------|
| `resources/views/projects/_form.blade.php` | حذف radio buttons، إضافة hidden input بقيمة `income` |
| `app/Http/Requests/Projects/StoreProjectRequest.php` | `in:income,expense` → `in:income` |
| `app/Http/Requests/Projects/UpdateProjectRequest.php` | `in:income,expense` → `in:income` |

**ما تُرك كما هو:**
- عمود `type` في جدول `project_service` — قيم `expense` القديمة تبقى صالحة
- البيانات القديمة من نوع `expense` ستتحول لـ `income` عند أول تعديل للمشروع

---

## ثانياً — الحالة الراهنة لجدول project_service

```sql
project_service
├── id
├── project_id          (ULID → projects)
├── service_id          (→ services)
├── client_id           (nullable → clients)
├── amount              (decimal 12,2) — ما يدفعه العميل
├── type                (income | expense) — الآن دائماً income
├── notes               (nullable)
├── team_member_id      (nullable → team_members) — منفذ واحد فقط ← القيد الحالي
├── team_cost           (decimal 12,2, nullable) — ما تدفعه للمنفذ
├── team_cost_paid      (boolean) — هل دُفعت تكلفة المنفذ؟
└── timestamps
```

### الفجوة الحالية
`team_cost` **موجود في البيانات لكنه غير مرتبط بحسابات الربحية** في `ProjectFinancialService`.
الخدمة تُحسب كدخل كامل بدون طرح تكلفة المنفذ = هامش خاطئ.

---

## ثالثاً — التصميم الجديد

### المبدأ الأساسي

```
هامش الخدمة = قيمة الخدمة (من العميل) − مجموع تكاليف المنفذين
```

### القيد الحالي والحل المطلوب

**حالياً:** خدمة → منفذ واحد
**المطلوب:** خدمة → منفذون متعددون (each with their own cost)

```
الخدمة: تطوير موقع         10,000 ر.س
├── أحمد  (frontend)         2,000 ر.س
├── سارة  (backend)          3,000 ر.س
└── ─────────────────────────────────
    إجمالي التكلفة:          5,000 ر.س
    هامش الخدمة:             5,000 ر.س (50%)
```

---

## رابعاً — البنية التقنية المخططة

### الجدول الجديد: project_service_members

```sql
project_service_members
├── id
├── project_service_id   (→ project_service, cascadeOnDelete)
├── team_member_id       (→ team_members, nullOnDelete)
├── team_cost            (decimal 12,2, nullable)
├── team_cost_paid       (boolean, default: false)
└── timestamps
```

**ملاحظة:** هذا الجدول يستبدل أعمدة `team_member_id` و`team_cost` و`team_cost_paid` الموجودة في `project_service`.
البيانات الحالية ستُرحَّل في الـ migration.

### نموذج البيانات المُرسَلة من الفورم

```json
{
  "services": [
    {
      "service_id": 1,
      "amount": 10000,
      "type": "income",
      "notes": "تصميم وتطوير كامل",
      "members": [
        { "team_member_id": "01J...", "team_cost": 2000 },
        { "team_member_id": "01J...", "team_cost": 3000 }
      ]
    }
  ]
}
```

### قواعد التحقق الجديدة

```php
'services.*.members'                => ['nullable', 'array'],
'services.*.members.*.team_member_id' => ['required_with:services.*.members', 'string', 'exists:team_members,id'],
'services.*.members.*.team_cost'      => ['nullable', 'numeric', 'min:0'],
```

---

## خامساً — خطة التنفيذ (بالترتيب)

### المرحلة 1 — البنية التحتية
**المهام:**
- [ ] إنشاء migration جديد لجدول `project_service_members`
- [ ] ترحيل البيانات الحالية من `project_service.team_member_id/team_cost`
- [ ] تحديث Model `ProjectService` / `Project` للعلاقات الجديدة
- [ ] تحديث `StoreProjectRequest` و`UpdateProjectRequest` بقواعد التحقق الجديدة
- [ ] تحديث `ProjectController` لحفظ المنفذين المتعددين
- [ ] تحديث `ProjectFinancialService` لحساب الهامش الصحيح

**نتيجة البيانات في ProjectFinancialService:**
```php
// per service:
'service_margin'       => $service->amount - $service->members->sum('team_cost'),
'service_margin_pct'   => ($service->amount > 0)
                          ? round((($service->amount - $totalCost) / $service->amount) * 100, 1)
                          : null,
'members_total_cost'   => $service->members->sum('team_cost'),
```

---

### المرحلة 2 — تحديث الـ UI
**المهام:**
- [ ] تحديث `_form.blade.php`: قسم الفريق داخل كل خدمة يصبح قائمة قابلة للإضافة والحذف
- [ ] إضافة مؤشر الهامش الحي (Alpine.js — حساب frontend في اللحظة)
- [ ] تصميم مؤشر الهامش مع ألوان تدريجية:

```
> 40%   🟢 هامش ممتاز
20-40%  🟡 هامش مقبول — راقب التكاليف
< 20%   🟠 هامش منخفض — تحذير
< 0%    🔴 خسارة — مراجعة فورية
```

- [ ] إظهار إجمالي هامش المشروع أسفل قائمة الخدمات قبل الحفظ

---

### المرحلة 3 — نظام التنبيهات (بعد تراكم البيانات)

**التنبيهات اللحظية (لا تحتاج تاريخ):**
- تكلفة المنفذين تجاوزت 80% من قيمة الخدمة
- مجموع تكاليف المشروع يتجاوز إيراده الكلي

**التنبيهات المبنية على التاريخ (بعد 10+ مشاريع):**
- "خدمة [التصميم] متوسط هامشها في مشاريعك 45% — هذا المشروع 15% فقط"
- "هذه الخدمة تاريخياً ذات هامش منخفض — هل تريد مراجعة السعر؟"

---

### المرحلة 4 — اقتراح السعر الذكي

**الميزة:**
> "بناءً على تكاليف فريقك، لتحقيق هامش 40% يجب أن تكون قيمة هذه الخدمة على الأقل: 8,333 ر.س"

**المعادلة:**
```
السعر الموصى به = إجمالي تكاليف المنفذين ÷ (1 - نسبة الهامش المستهدفة)
مثال: 5,000 ÷ (1 - 0.40) = 8,333 ر.س
```

**المتطلب:** بيانات تاريخية كافية + إعداد المستخدم لنسبة الهامش المستهدفة.

---

## سادساً — تأثير كل مرحلة على التقارير

| المرحلة | ما يُضاف للتقارير |
|---------|-------------------|
| 1 | هامش كل خدمة + إجمالي تكاليف المنفذين |
| 2 | عرض الهامش بصرياً في صفحة المشروع |
| 3 | تنبيهات ربحية في dashboard |
| 4 | توصيات تسعير في نموذج الإنشاء |

---

## سابعاً — ملاحظات التنفيذ

1. **الترحيل آمن:** البيانات القديمة في `project_service.team_member_id` ستُنسخ لـ `project_service_members` في نفس الـ migration
2. **الأعمدة القديمة:** بعد الترحيل تُحذف `team_member_id`, `team_cost`, `team_cost_paid` من `project_service`
3. **الأثر الصفري على التقارير الحالية:** لأن `team_cost` لم يكن مرتبطاً بالحسابات أصلاً
4. **team_cost_paid:** ينتقل للجدول الجديد — منطق الدفع يبقى per-member لا per-service
