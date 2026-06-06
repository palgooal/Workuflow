# توثيق — إدارة الفريق والخدمات في المشاريع
## دراهم | مال وأعمال

> تاريخ التوثيق: يونيو 2026
> الحالة: ✅ مكتمل بالكامل — في انتظار `php artisan migrate` (3 migrations جديدة)

---

## أولاً — القرارات المنفذة (تبسيط الفورم)

### 1. حذف ميزانية التكاليف (expense_budget)

**القرار:** حذف حقل `expense_budget` من نموذج إنشاء وتعديل المشروع.

**السبب:** الجمهور المستهدف لا يفكر بمنطق "سقف المصروفات". المعاملات هي المكان الصحيح.

| الملف | التغيير |
|-------|---------|
| `resources/views/projects/_form.blade.php` | حذف حقل الإدخال |
| `app/Http/Requests/Projects/StoreProjectRequest.php` | حذف قاعدة التحقق |
| `app/Http/Requests/Projects/UpdateProjectRequest.php` | حذف قاعدة التحقق |
| `app/Modules/Projects/DTOs/ProjectData.php` | حذف الخاصية والـ mapping |
| `app/Modules/Projects/Actions/CreateProjectAction.php` | حذف من array الحفظ |
| `app/Modules/Projects/Actions/UpdateProjectAction.php` | حذف من array التحديث |

**ما تُرك:** عمود `expense_budget` في الداتابيز (حماية بيانات قديمة). العرض في `show.blade.php` محاط بـ `@if($summary['expense_budget'])`.

---

### 2. حذف نوع "مصروف" من الخدمات

**القرار:** الخدمات دائماً `income`. حذف radio buttons، إضافة hidden input ثابت.

| الملف | التغيير |
|-------|---------|
| `resources/views/projects/_form.blade.php` | حذف radio buttons، hidden input قيمته `income` |
| `StoreProjectRequest` + `UpdateProjectRequest` | `in:income,expense` → `in:income` |

---

## ثانياً — البنية التقنية المنفذة

### schema الفعلي بعد التنفيذ

```sql
-- الجدول الرئيسي (project_service) — بعد حذف أعمدة الفريق القديمة وإضافة الجديدة
project_service
├── id
├── project_id              (ULID → projects, cascadeOnDelete)
├── service_id              (→ services)
├── client_id               (nullable → clients)
├── amount                  (decimal 12,2) — ما يدفعه العميل
├── type                    (income) — دائماً income
├── notes                   (nullable)
├── target_margin_pct       (tinyInt nullable) ← جديد — هامش مستهدف مخصص لهذه الخدمة
└── timestamps

-- جدول المنفذين (جديد بالكامل — يستبدل team_member_id القديم)
project_service_members
├── id
├── project_service_id      (→ project_service, cascadeOnDelete)
├── team_member_id          (nullable → team_members, nullOnDelete)
├── team_cost               (decimal 12,2, nullable)
├── team_cost_paid          (boolean, default: false)
└── timestamps

-- عمود جديد على users
users.target_margin_pct     (tinyInt, default: 40) — الهامش العام للمستخدم
```

### Migrations المضافة

| الملف | المحتوى |
|-------|---------|
| `2026_06_06_000001` | إنشاء `project_service_members` + ترحيل البيانات + حذف أعمدة `team_member_id/team_cost/team_cost_paid` من `project_service` |
| `2026_06_06_000002` | إضافة `target_margin_pct` لـ `users` (default: 40) |
| `2026_06_06_000003` | إضافة `target_margin_pct` nullable لـ `project_service` |

### Models المضافة

| الملف | الوصف |
|-------|-------|
| `app/Models/ProjectServicePivot.php` | Pivot model يرث من `Pivot`، يحتوي على `members()` hasMany |
| `app/Models/ProjectServiceMember.php` | Model لمنفذي الخدمة مع علاقات `teamMember()` و`projectService()` |

### نموذج البيانات المُرسَلة (الشكل النهائي)

```json
{
  "services": [
    {
      "service_id": 1,
      "amount": 10000,
      "type": "income",
      "notes": "تصميم وتطوير كامل",
      "target_margin_pct": 45,
      "members": [
        { "team_member_id": "01J...", "team_cost": 2000 },
        { "team_member_id": "01J...", "team_cost": 3000 }
      ]
    }
  ]
}
```

---

## ثالثاً — المراحل المنفذة

### ✅ المرحلة 1 — البنية التحتية

| الملف | ما تم |
|-------|-------|
| `ProjectServicePivot` | Pivot model + `members()` hasMany |
| `ProjectServiceMember` | Model جديد |
| `Project::services()` | `using(ProjectServicePivot)` + `withPivot(['id', ..., 'target_margin_pct'])` |
| `StoreProjectRequest` | Validation لـ `services.*.members` nested + `target_margin_pct` |
| `UpdateProjectRequest` | نفس التغيير |
| `ProjectController` | `store()`, `update()`, `payTeamMember()`, `syncServiceMembers()` helper |
| `ProjectFinancialService` | `calcServicesMargin()` — هامش كل خدمة مستقل |
| `routes/web.php` | Route يستقبل `memberId` لا `serviceId` |

---

### ✅ المرحلة 2 — الـ UI

**`_form.blade.php` (Alpine.js):**

الدوال المضافة:
```js
addMember(svcIndex)         // إضافة منفذ لخدمة
removeMember(idx, mIdx)     // حذف منفذ
serviceMargin(svc)          // { revenue, cost, margin, pct }
marginColor(pct)            // class CSS حسب الهامش
costPct(svc)                // نسبة التكلفة من الإيراد
projectMarginSummary()      // إجمالي الهامش لكل الخدمات
effectiveMarginPct(svc)     // هامش الخدمة المخصص ?? إعداد المستخدم
suggestedPrice(svc)         // السعر الموصى به = تكلفة ÷ (1 - هامش_مستهدف)
fetchServiceHistory(svc)    // جلب متوسط الهامش التاريخي للخدمة
```

**`show.blade.php`:**
- قسم "Team Assignments" القديم → "هامش الخدمات" الجديد
- لكل خدمة: اسم + إيراد + تكلفة الفريق + هامش + progress bar ملون
- لكل منفذ: اسم + تكلفة + زر "دفع" يستخدم `project_service_members.id`

---

### ✅ المرحلة 3 — التنبيهات

| التنبيه | الحالة | التفعيل |
|---------|--------|---------|
| تكلفة الخدمة ≥ 80% من إيرادها | لحظي (Alpine.js) | دائماً |
| الخدمة بخسارة | لحظي (Alpine.js) | دائماً |
| إجمالي المشروع < 20% | لحظي (Alpine.js) | عند وجود خدمتين+ |
| إجمالي المشروع بخسارة | لحظي (Alpine.js) | دائماً |
| الهامش أقل من المتوسط التاريخي بـ 50% | تاريخي (AJAX) | بعد 1+ مشروع سابق |
| عرض متوسط الهامش التاريخي عند اختيار الخدمة | تاريخي (AJAX) | بعد 1+ مشروع سابق |

**Endpoint:** `GET /projects/service-margin-history/{serviceId}` → يعيد `avg_margin`, `times_used`, `label`

---

### ✅ المرحلة 4 — اقتراح السعر الذكي

**المعادلة:**
```
السعر الموصى به = إجمالي تكاليف المنفذين ÷ (1 − effectiveMarginPct / 100)
مثال: 5,000 ÷ (1 − 0.40) = 8,333 ر.س
```

**UX:**
- زر 💡 فوق حقل القيمة عند وجود تكاليف وغياب سعر — ضغطة واحدة تُطبق السعر
- بانر "تطبيق" عندما يكون السعر المدخل أقل من الهامش المستهدف

---

### ✅ الإعداد العام + المخصص للخدمة

**منطق الأولوية:**
```
effectiveMarginPct(svc):
  سvc.target_margin_pct (1-99)  →  إذا محدد لهذه الخدمة
  user.target_margin_pct        →  الإعداد العام للمستخدم (default: 40%)
```

**في الفورم:**
- زر صغير بجانب الملاحظات يعرض نسبة الهامش العام
- ضغطة عليه → حقل إدخال أزرق لتخصيص هامش مخصص لهذه الخدمة فقط
- زر × لإزالة التخصيص والرجوع للعام

**في الإعدادات (`settings/index.blade.php`):**
- Slider تفاعلي (1-99%) يحفظ في `users.target_margin_pct`

---

### ✅ تقارير ربحية الخدمات

**Methods مضافة لـ `ReportService`:**

| الدالة | المخرج |
|--------|--------|
| `getServiceProfitability()` | ربحية كل خدمة (revenue, cost, margin, margin_pct, project_count) |
| `getTeamMemberEfficiency()` | تكاليف كل منفذ + نسبته من إيراد خدماته |

**في `reports/index.blade.php`:**
- جدول ربحية الخدمات مرتب تنازلياً بالهامش — الخاسر باللون الأحمر
- قسم كفاءة الفريق: بار يمتد حسب نسبة التكلفة من الإيراد (أحمر >80%)

---

## رابعاً — المعادلة المالية الكاملة

```
هامش الخدمة    = amount - Σ(members.team_cost)
هامش المشروع   = Σ(services.amount) - Σ(project_service_members.team_cost)
ربح المشروع    = معاملات الدخل - معاملات المصروف  (transactions)
```

> **ملاحظة:** هامش الخدمة مبني على تكاليف الفريق المباشرة.
> ربح المشروع الكلي مبني على المعاملات المسجّلة.
> الاثنان مكملان لبعض.

---

## خامساً — ملاحظات التشغيل

```bash
# تشغيل الـ migrations الثلاثة بالترتيب
php artisan migrate

# Migrations تُنفَّذ:
# 1. 2026_06_06_000001 — project_service_members (إنشاء + ترحيل + حذف أعمدة قديمة)
# 2. 2026_06_06_000002 — users.target_margin_pct
# 3. 2026_06_06_000003 — project_service.target_margin_pct
```

**تحذيرات:**
- الـ migration الأول يحذف `team_member_id`, `team_cost`, `team_cost_paid` من `project_service` بعد نسخها — لا رجعة بدون `migrate:rollback`
- البيانات القديمة (منفذ واحد لكل خدمة) تُرحَّل تلقائياً لـ `project_service_members`
