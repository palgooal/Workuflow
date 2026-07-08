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

### BUG-04 — عدم اتساق حساب دخل/مصروف المشروع عبر 3 نقاط مختلفة (تجاهل العملة)
**الخطورة:** Critical — يعرض أرقام دخل متناقضة لنفس المشروع بحسب الصفحة المفتوحة
**الاكتشاف:** 2026-07-09 (تجربة مستخدم كاملة لموديول المشاريع — إنشاء/تعديل/فحص حسابات)
**الحالة:** ✅ مُصلَح — 2026-07-09

**الوصف:**
يوجد 3 دوال مختلفة تحسب دخل/مصروف المشروع، وكل واحدة تتعامل مع العملة بشكل مختلف:

1. `Project::totalIncome()` / `totalExpenses()` (`app/Models/Project.php` سطر 110-122) — تجمع `amount` من كل المعاملات **بدون أي فلترة على العملة إطلاقاً**. تُستخدم في بطاقات صفحة `/projects` (index cards عبر `projects/_card.blade.php`).
2. `ProjectFinancialService::getSummary()` (سطر 15-91) — تجمع المعاملات `by_currency`، لكن "الدخل/المصروف الأساسي" المعروض في أعلى صفحة `/projects/{id}` يُقرأ **فقط** من `$byCurrency[$project->currency]` — أي معاملات بعملة أخرى غير عملة المشروع تُهمَل تماماً بدون أي تحذير.
3. `ProjectFinancialService::getPortfolioSummary()` (سطر 148-194) — تجمع كل معاملات كل المشاريع `by_currency` حسب عملة **المعاملة نفسها** (بغض النظر عن عملة مشروعها)، وتُستخدم في بطاقات ملخص أعلى `/projects` عند وجود أكثر من عملة.

**الأثر (تم التحقق منه حياً بإعادة إنتاج كاملة):**
- أنشأنا مشروعاً باسم "UX-TEST مشروع SAR" بعملة SAR، وأضفنا معاملة دخل 3,000 SAR عليه.
- ثم عدّلنا عملة المشروع إلى USD (بدون أي تحذير من النظام رغم وجود معاملة SAR مرتبطة).
- نتيجة ذلك، صفحة تفاصيل المشروع (`/projects/{id}`) عرضت:
  - **إجمالي الدخل: 0.00 USD** ، **قيمة العقد المُستلم: 0%** ، **متبقي استلام: 10,000.00 USD كاملة**
  - بينما قسم "آخر المعاملات" في **نفس الصفحة** يعرض بوضوح `+3,000.00` كمعاملة موجودة فعلاً — تناقض مباشر ومرئي في نفس الشاشة.
- بنفس الطريقة، وُجد مسبقاً في بيانات حقيقية: مشروع "asdasdsadsad" بعملة ILS، وله معاملة فعلية (فاتورة INV-0007) بقيمة 48,052.40 لكنها مُسجَّلة بعملة USD فعلياً — فصفحة تفاصيله تُظهر "0.00 ILS" دخل، بينما بطاقته في صفحة `/projects` الرئيسية (التي تستخدم `totalIncome()` الخام) تُظهر "+48,052" بدون أي رمز عملة، موحية بأنها بعملة المشروع (ILS) بينما هي فعلياً بعملة أخرى (USD) — فرق يقارب 13 ضعف بسعر الصرف التقريبي.

**السبب الجذري:** لا يوجد أي تحذير أو منع عندما تختلف عملة معاملة عن عملة مشروعها — لا عند إنشاء/تعديل المعاملة، ولا عند تعديل عملة المشروع لاحقاً، ولا عرض بديل يوضّح "يوجد X بعملة أخرى غير محتسبة هنا".

**الإصلاح المُنفَّذ:**
- `Project::totalIncome()`/`totalExpenses()` أصبحتا تفلتران بـ`where('currency', $this->currency)` — بطاقات وجدول `/projects` تعرض الآن فقط مبالغ بعملة المشروع نفسها، متطابقة مع صفحة التفاصيل.
- `ProjectFinancialService::getSummary()` — تصحيح شرط `multi_currency` ليشمل حالة "عملة معاملات وحيدة لكنها مختلفة عن عملة المشروع" (وليس فقط `count($byCurrency) > 1`) — فتظهر تلقائياً جدول تفصيل العملات الموجود أصلاً بدل عرض 0.00 بصمت.
- إضافة `Project::hasForeignCurrencyTransactions()` + عمود مُحتسَب `foreign_currency_transactions_count` (عبر `withCount` في `ProjectController::index()`) لعرض تنبيه ⚠ صغير على بطاقة/صف المشروع في `/projects` عندما توجد معاملات بعملة أخرى غير محتسبة في الأرقام الظاهرة.
- إضافة رمز العملة بجانب كل معاملة في قسم "آخر المعاملات" بصفحة `/projects/{id}` (كان يعرض الرقم فقط بدون عملة، ما يوحي خطأً أنه بعملة المشروع).
- **لم يُنفَّذ بعد (GAP-11):** تحذير صريح عند تعديل عملة مشروع له معاملات موجودة بعملة مختلفة — يبقى ممكناً بدون تنبيه حتى الآن، لكن الأثر السلبي على العرض صار الآن مرئياً بدل مخفي (بفضل الإصلاح أعلاه).

**الملفات المعدَّلة:** `app/Models/Project.php`، `app/Modules/Projects/Services/ProjectFinancialService.php`، `app/Http/Controllers/ProjectController.php`، `resources/views/projects/show.blade.php`، `resources/views/projects/_card.blade.php`، `resources/views/projects/index.blade.php`

---

### BUG-05 — رصيد الصندوق (Wallet) يجمع المبالغ عبر كل العملات بدون تحويل أو تحذير
**الخطورة:** Critical — يُظهر رصيداً نقدياً خاطئاً للمستخدم (الرقم الأبرز في لوحة التحكم)
**الاكتشاف:** 2026-07-09 (نفس جلسة اختبار المشاريع، أثناء تسجيل معاملة اختبار)
**الحالة:** ✅ مُصلَح — 2026-07-09

**الوصف:**
`Wallet::balance()` و`totalIncome()`/`totalExpenses()` (`app/Models/Wallet.php` سطر 73-101 تقريباً) تجمع عمود `amount` لكل معاملات الصندوق **بدون أي فلترة أو تحويل حسب العملة** — تتعامل مع أي مبلغ بأي عملة كأنه بنفس عملة الصندوق مباشرة.
بالإضافة، `StoreTransactionRequest`/`UpdateTransactionRequest` (`app/Http/Requests/Transactions/`) لا يحتويان أي قاعدة تُلزم بأن تطابق عملة المعاملة عملة الصندوق (`wallet_id`) المُختار — الحقلان يُتحقق منهما بشكل منفصل تماماً بدون ربط.

**الأثر (تم التحقق منه حياً):**
- الصندوق الوحيد في الحساب "الصندوق العام" بعملة USD كان رصيده 500.00 USD.
- سجّلنا معاملة دخل جديدة بمبلغ 3,000 **SAR** واخترنا نفس الصندوق (USD) كوجهة للأموال — النظام قبل ذلك بدون أي تحذير أو منع.
- رصيد الصندوق أصبح فوراً **3,500.00 USD** — أي أن مبلغ الـ 3,000 ريال سعودي أُضيف حرفياً كأنه 3,000 دولار، بفارق يقارب 3.75 ضعف عن القيمة الحقيقية.
- هذا يعني أن أي مستخدم يسجّل معاملات بعملات متعددة على نفس الصندوق سيرى رصيداً نقدياً خاطئاً تماماً في أهم رقم بالتطبيق (بطاقة "إجمالي رصيد الصناديق" في لوحة التحكم و`/wallets`).

**الإصلاح المُنفَّذ:**
- `Wallet::balance()`/`totalIncome()`/`totalExpenses()` أصبحت تفلتر بـ`where('currency', $this->currency)` — معاملات بعملة مختلفة عن عملة الصندوق لم تعد تُحتسَب ضمن الرصيد (نفس نمط إصلاح BUG-04).
- إضافة `withValidator()` في `StoreTransactionRequest` و`UpdateTransactionRequest` يمنع الآن حفظ أي معاملة إذا كانت عملتها لا تطابق عملة الصندوق المُختار، برسالة توضّح السبب (مثال: "عملة المعاملة (SAR) لا تطابق عملة الصندوق ... (USD)").
- إضافة `Wallet::hasForeignCurrencyTransactions()` + بانر تحذير في `/wallets/{id}` وبطاقة الصندوق يوضّح المبالغ المستبعدة من الرصيد لكل عملة (بيانات تاريخية سبق تسجيلها قبل هذا الإصلاح، مثل معاملة الاختبار 3,000 SAR).

**ملاحظة:** أي معاملة كانت قد سُجِّلت سابقاً (قبل هذا الإصلاح) بعملة مختلفة عن عملة صندوقها ستبقى موجودة في قاعدة البيانات لكنها الآن تُستبعَد من الرصيد المعروض تلقائياً، وتظهر بدلاً من ذلك في بانر التحذير الجديد بصفحة الصندوق — لا حذف بيانات، فقط تصحيح للعرض.

**الملفات المعدَّلة:** `app/Models/Wallet.php`، `app/Http/Requests/Transactions/StoreTransactionRequest.php`، `app/Http/Requests/Transactions/UpdateTransactionRequest.php`، `resources/views/wallets/_card.blade.php`، `resources/views/wallets/show.blade.php`

---

### GAP-11 — تعديل عملة مشروع له معاملات موجودة بدون أي تحذير
**الأولوية:** High
**الاكتشاف:** 2026-07-09
**الحالة:** ✅ مُصلَح — 2026-07-09

**الوصف:** صفحة `/projects/{id}/edit` كانت تسمح بتغيير حقل "العملة" بحرية كاملة حتى لو كان للمشروع معاملات مسجّلة بالعملة القديمة، بدون أي رسالة تنبيه أو طلب تأكيد. هذا يؤدي مباشرة لظهور BUG-04 أعلاه.

**الإصلاح المُنفَّذ:** إضافة تنبيه Alpine.js فوري (بدون حاجة لإعادة تحميل الصفحة) في `resources/views/projects/_form.blade.php` — عند اختيار عملة مختلفة عن عملة المشروع الحالية (`originalCurrency`) في مشروع له معاملات مسجّلة (`projectHasTransactions`)، يظهر بانر كهرماني يوضّح أن معاملاته القديمة لن تُحتسب ضمن الملخص الأساسي بعد الحفظ وستظهر في جدول "عملات أخرى" بصفحة تفاصيل المشروع بدلاً من ذلك. لا يمنع الحفظ (يبقى قراراً للمستخدم) لكنه يجعل الأثر مرئياً قبل الحفظ.

**الملفات المعدَّلة:** `resources/views/projects/_form.blade.php`

---

### GAP-12 — زر "إضافة معاملة" من صفحة المشروع لا يُحضّر المشروع مسبقاً في نموذج المعاملة
**الأولوية:** Medium
**الاكتشاف:** 2026-07-09
**الحالة:** ✅ مُصلَح — 2026-07-09

**الوصف:** الضغط على "إضافة معاملة" في `/projects/{id}` كان يوجّه إلى `/transactions?project={id}` (قائمة معاملات مُفلترة فقط، وليست نموذج إضافة)، وزر "معاملة جديدة" في تلك الصفحة بدوره كان يذهب إلى `/transactions/create` **بدون** تمرير `project` — فحقل "المشروع" في النموذج يبدأ بقيمة "بدون مشروع" رغم أن المستخدم جاء تحديداً من صفحة مشروع محدد.

**ملاحظة مهمة اكتُشفت أثناء الإصلاح:** `TransactionController::create()` كان بالفعل يدعم `?project={id}` لتحديد المشروع مسبقاً في القائمة المنسدلة (`$preProject = $request->query('project')`) — الثغرة الفعلية كانت فقط أن الروابط لم تكن تستخدم هذه الآلية الموجودة أصلاً، وليست نقصاً في الفورم نفسه.

**الإصلاح المُنفَّذ:**
- `resources/views/projects/show.blade.php` — رابط "إضافة معاملة" أصبح يذهب مباشرة إلى `route('transactions.create')?project={id}` بدل `route('transactions.index')?project={id}`.
- `resources/views/transactions/index.blade.php` — زر "معاملة جديدة" وحالة "لا توجد معاملات" الفارغة أصبحا يُمرّران فلتر `project` الحالي (إن وُجد في الرابط) إلى `/transactions/create`، بحيث لو وصل المستخدم لقائمة المعاملات المُفلترة بمشروع معيّن أولاً ثم ضغط لإضافة معاملة، يبقى المشروع محدداً.

**الملفات المعدَّلة:** `resources/views/projects/show.blade.php`، `resources/views/transactions/index.blade.php`

---

### BUG-06 — لوحة التحكم الرئيسية (`/dashboard`) تعرض بيانات قديمة حتى 30 دقيقة بعد أي تعديل
**الخطورة:** Critical — يشمل كل أرقام لوحة التحكم (لا يقتصر على المشاريع)
**الاكتشاف:** 2026-07-09 (المستخدم لاحظ أن بطاقة "المشاريع النشطة" ما زالت تعرض 5 بعد تحويل مشروع إلى "مكتمل"، بينما صفحة `/projects` عرضت 4 بشكل صحيح)
**الحالة:** ✅ مُصلَح — 2026-07-09

**الوصف:**
`DashboardService::getData()` يُخزِّن كل بيانات لوحة التحكم (KPIs، الرسم البياني، آخر المعاملات، المشاريع النشطة، الديون المستحقة، ملخص الصناديق، الفواتير المعلّقة) في Cache واحد لمدة 30 دقيقة (`Cache::remember("dashboard_v2:{$userId}", 1800, ...)`). الدالة `DashboardService::clearCache()` موجودة ومكتوبة بشكل صحيح، **لكن لا يوجد أي كود في التطبيق بأكمله يستدعيها** — لا عند تعديل مشروع، ولا معاملة، ولا فاتورة، ولا صندوق، ولا دين. أي تعديل في أي مكان بالتطبيق كان يبقى غير منعكس على لوحة التحكم حتى تنتهي مدة الـ30 دقيقة تلقائياً.

**الأثر (تم التحقق منه حياً):** تحويل مشروع لحالة "مكتمل" أبقى بطاقة "المشاريع النشطة" ولوحة "المشاريع النشطة" السفلية على القيمة القديمة، بينما صفحة `/projects` (تُحسَب مباشرة بدون Cache) عرضت الرقم الصحيح فوراً — تناقض بين صفحتين لنفس البيانات.

**الإصلاح المُنفَّذ:**
- `app/Support/Traits/BelongsToUser.php` — إضافة مستمعين لأحداث `saved` و`deleted` في `bootBelongsToUser()` يستدعيان `DashboardService::clearCache($model->user_id)` تلقائياً. هذا الـ Trait مُستخدَم في 10 Models تشمل كل ما يغذّي لوحة التحكم: `Project`, `Transaction`, `Wallet`, `Debt`, `WalletTransfer`, `RecurringTransaction`, `Budget`, `Category`, `Client`, `TeamMember` — تغيير واحد يغطيها جميعاً.
- `app/Models/Invoice.php` — إضافة نفس المنطق صراحةً داخل `boot()` الموجود مسبقاً، لأن Invoice **لا** تستخدم `BelongsToUser` (تُدير `user_id` يدوياً).
- تم التحقق حياً: تغيير حالة مشروع حقيقي من "نشط" إلى "مكتمل" ثم فتح `/dashboard` أظهر الرقم الصحيح فوراً بدون انتظار.

**ملاحظة:** لم يُفحَص كل نقطة إنشاء/تعديل بشكل منفصل (الاعتماد كان على أحداث Eloquent Model المركزية بدل تتبع كل Controller/Action) — هذا يعني التغطية شاملة تلقائياً لأي كود مستقبلي يستخدم هذه الـ Models طالما يمر عبر `save()`/`update()`/`delete()` القياسية.

**الملفات المعدَّلة:** `app/Support/Traits/BelongsToUser.php`، `app/Models/Invoice.php`

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
