# Workuflow — Known Bugs & Pending Gaps
## Living Document — يُحدَّث مع كل إصلاح

> **Document Type:** Bug Tracker + Technical Debt Register
> **Last Updated:** June 2026
> **Convention:** أضف تاريخ الاكتشاف والإصلاح لكل بند

---

## 🔴 Bugs — أخطاء فعلية تؤثر على المستخدم

### BUG-01 — `client_activities.created_at` غير موجود
**الخطورة:** Critical — يُعطّل Health Score لكل العملاء  
**الاكتشاف:** June 2026  
**الحالة:** ✅ مُصلَح — June 2026 (`ClientHealthScoreService.php` سطر 331)

**الوصف:**
`ClientHealthScoreService::countContacts()` كانت تستعلم بـ `created_at`:
```php
DB::table('client_activities')
    ->where('client_id', $clientId)
    ->where('created_at', '>=', now()->subMonths($months))  // ❌ خطأ
    ->count();
```

لكن جدول `client_activities` لا يحتوي على عمود `created_at` — يستخدم `occurred_at` بدلاً منه.

**الأثر:** استثناء `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'created_at'` عند كل عملية حساب، يُسجَّل كـ Warning في اللوج ويتجاوز العميل بدون درجة صحة.

**الإصلاح:**
```php
// BEFORE
->where('created_at', '>=', now()->subMonths($months))

// AFTER
->where('occurred_at', '>=', now()->subMonths($months))
```

**الملف:** `app/Modules/CRM/Services/ClientHealthScoreService.php`  
**الدالة:** `countContacts(int $clientId, int $months): int` و `getFollowUpStats()`

**خطأ مصاحب:** `getFollowUpStats()` كانت تستعلم `->whereNull('deleted_at')` على جدول `client_follow_ups` الذي لا يملك هذا العمود (لا يستخدم SoftDeletes). **الإصلاح:** حذف هذا الشرط.

---

### BUG-02 — `<x-app-layout>` غير موجود في Follow-Ups view
**الخطورة:** Critical — صفحة المتابعات تعرض محتوى فارغاً  
**الاكتشاف:** June 2026  
**الحالة:** ✅ مُصلَح — June 2026

**الوصف:**
`resources/views/crm/follow-ups/index.blade.php` كان يستخدم `<x-app-layout>` (Blade component غير موجود في المشروع). بقية الصفحات تستخدم `@extends('layouts.app')`.

**الأثر:** الصفحة تُحمَّل بدون أي محتوى — لا errors، لا بيانات.

**الإصلاح:** تحويل الـ view لـ `@extends('layouts.app')` مع `@section('content')`.

---

### BUG-03 — `@show-toast.window` يُفسَّر كـ Blade `@show` directive
**الخطورة:** Critical — يُغلق الـ section مبكراً ويرمي exception  
**الاكتشاف:** June 2026 (ظهر بعد إصلاح BUG-02)  
**الحالة:** ✅ مُصلَح — June 2026

**الوصف:**
Alpine.js event attribute بصيغة `@show-toast.window="..."` — Blade يُفسِّر `@show` كـ built-in directive (`$__env->yieldSection()`) ينهي ويُخرج الـ section الحالية. يتسبب في:
```
InvalidArgumentException: Cannot end a section without first starting one.
```

**الإصلاح:**
```blade
{{-- BEFORE --}}
<div @show-toast.window="show = true; ...">

{{-- AFTER --}}
<div x-on:show-toast.window="show = true; ...">
```

**قاعدة عامة:** أي Alpine.js `@event` يبدأ باسم يطابق Blade directive (`@show`, `@error`, `@auth`, `@can` إلخ) يجب كتابته بـ `x-on:` — راجع `docs/CRM-FOLLOW-UPS.md §9` للقائمة الكاملة.

---

## 🟠 Gaps — ثغرات تقنية ذات أولوية متوسطة

### GAP-01 — `saved_segments.filters` بدون schema validation
**الأولوية:** High  
**الحالة:** ✅ مُصلَح — June 2026  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` M-07

**الوصف:** عند حفظ شريحة، يُخزَّن `filters` JSON مباشرة بدون التحقق من بنيته. فلتر بحقل `field` خاطئ أو `op` غير معروف يُسقط `ClientSegmentEngine` بـ runtime exception.

**الإصلاح المطلوب:** إضافة `validateFilterSchema()` في `SaveSegmentAction` قبل الحفظ (راجع `CRM-HEALTH-SEGMENTS.md §3.5`).

---

### GAP-02 — Health Score لا يُعاد حسابه عند تسجيل دفعة
**الأولوية:** Medium  
**الحالة:** ✅ مُصلَح — June 2026  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` §1.2 (C-02)

**الوصف:** الدرجة تُحسَب يومياً الساعة 2:00 صباحاً أو يدوياً. لا يوجد trigger عند تسجيل معاملة/دفعة جديدة — يعني المستخدم يدفع ثم يرى نفس الدرجة حتى الغد.

**الإصلاح:**
- `app/Modules/CRM/Jobs/RecalculateClientHealthScoreJob.php` — Job جديد يحسب درجة عميل واحد، `tries=2`، `uniqueId()` لمنع التكرار
- `InvoiceController::markPaid()` — يُطلق `RecalculateClientHealthScoreJob::dispatch($clientId)->onQueue('crm-default')->delay(5s)` بعد تحديث الإجماليات

---

### GAP-03 — `refreshCountsForUser()` لا تُجدوَل — عدادات الشرائح تتقادم
**الأولوية:** Medium  
**الحالة:** ⏳ معلّق

**الوصف:** `SavedSegment.client_count` يُحدَّث فقط عند استدعاء `refreshCountsForUser()` يدوياً. لا يوجد scheduler يستدعيها فتبقى الأرقام قديمة.

**الإصلاح المطلوب:** إضافة في `routes/console.php`:
```php
Schedule::command('crm:refresh-segment-counts')->dailyAt('03:00');
```
أو استدعاء `refreshCountsForUser()` بعد `recalculate-health-scores`.

---

### GAP-04 — `FollowUpService::dueForReminder()` لا تُجدوَل
**الأولوية:** Medium  
**الحالة:** ✅ مُصلَح — June 2026

**الوصف:** دالة `dueForReminder()` موجودة وتُرجع المتابعات المستحقة لإرسال تذكير، لكن لا يوجد Scheduler أو Command يستدعيها. التذكيرات لا تُرسَل.

**الإصلاح المطلوب:** إنشاء `SendFollowUpRemindersCommand` + جدولة كل 30 دقيقة أو كل ساعة.

---

### GAP-05 — `DetectInactiveClientsCommand` غير منفّذ
**الأولوية:** Medium  
**الحالة:** ✅ مُصلَح — June 2026  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` Sprint 6

**الوصف:** الـ spec يذكر `DetectInactiveClientsCommand` كجزء من Sprint 6 لاكتشاف العملاء الخاملين وتشغيل قواعد الـ Automation. غير موجود في الكود.

**الإصلاح:**
- `app/Console/Commands/DetectInactiveClientsCommand.php` — يُقيّم 3 triggers يومياً: `days_since_contact`, `health_score_below`, `invoice_overdue`
- يستدعي `AutomationRuleEngine::evaluateForAllClients()` لكل مستخدم نشط
- دعم `--dry-run`, `--user`, `--trigger` للتطوير والاختبار
- مُجدوَل في `routes/console.php` الساعة **04:00** (بعد recalculate + reconcile)

---

### GAP-06 — Actions تكتب `client_activities` داخل Transaction مباشرة
**الأولوية:** Medium  
**الحالة:** ✅ مُصلَح — June 2026  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` §1.1 (C-01)

**الوصف:** `CreateFollowUpAction` و `CompleteFollowUpAction` يكتبان في `client_activities` داخل الـ DB transaction مباشرة. إذا فشل تسجيل النشاط يُلغى الإجراء الأصلي، والعكس.

**الإصلاح:**
- `Events/FollowUpCreated.php` + `Events/FollowUpCompleted.php` — Events جديدة
- `Listeners/LogFollowUpCreatedActivity.php` + `Listeners/LogFollowUpCompletedActivity.php` — مع `$afterCommit = true`
- `CreateFollowUpAction` + `CompleteFollowUpAction` — يطلقان Event بدل الكتابة المباشرة
- `CRMServiceProvider` — تسجيل الـ Events مع الـ Listeners

---

## 🟡 Gaps — ثغرات منخفضة الأولوية / تحسينات

### GAP-07 — `client_health_scores` بدون `updated_at`
**الأولوية:** Low  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` N-01  
**الإصلاح:** إضافة migration: `$table->timestamps()` أو `$table->timestamp('updated_at')->nullable()`

### GAP-08 — `REVENUE_TOP` و `FREQ_TOP` hardcoded في الـ Service
**الأولوية:** Medium  
**الإصلاح:** نقلهما إلى `config/crm.php → health_score.thresholds`

### GAP-09 — لا يوجد score trend tracking
**الأولوية:** Medium  
**المرجع:** `CLIENTS-CRM-SPEC-V2.md` §4.6  
**الإصلاح:** إضافة `previous_score` و `trend` columns في `client_health_scores`

### GAP-10 — الشرائح لا تدعم OR logic
**الأولوية:** Low / Future  
**الوصف:** كل الفلاتر تُطبَّق بـ AND. لا يمكن بناء شريحة "العملاء المتأخرون OR الخاملون".

---

## ✅ سجل الإصلاحات المكتملة

| التاريخ | Bug/Gap | الملف |
|---|---|---|
| June 2026 | BUG-01: `created_at` → `occurred_at` في Health Score | `ClientHealthScoreService.php` |
| June 2026 | BUG-02: `<x-app-layout>` → `@extends('layouts.app')` | `crm/follow-ups/index.blade.php` |
| June 2026 | BUG-03: `@show-toast.window` → `x-on:show-toast.window` | `crm/follow-ups/index.blade.php` |
| June 2026 | إضافة `recalculateHealth` endpoint للمستخدمين (بدل artisan) | `ClientSegmentController.php` |
| June 2026 | GAP-01: schema validation للفلاتر قبل الحفظ | `ClientSegmentEngine.php`, `ClientSegmentController.php` |
| June 2026 | GAP-04: `SendFollowUpReminders` command + scheduler كل 30 دقيقة | `SendFollowUpReminders.php`, `routes/console.php` |
| June 2026 | GAP-02: `RecalculateClientHealthScoreJob` + dispatch من `markPaid()` | `RecalculateClientHealthScoreJob.php`, `InvoiceController.php` |
| June 2026 | GAP-05: `DetectInactiveClientsCommand` + scheduler 04:00 | `DetectInactiveClientsCommand.php`, `routes/console.php` |

---

*Document: `docs/KNOWN-BUGS-AND-GAPS.md`*
*يُحدَّث مع كل bug مكتشف أو gap مُغلَق*
