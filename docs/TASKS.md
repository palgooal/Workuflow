# ✅ خطة المهام الكاملة — Workuflow SaaS Financial Platform

> وثيقة تتبع المهام — Laravel 12 / PHP 8.2  
> آخر تحديث: مايو 2026

---

## 📊 ملخص المشروع

| البيان | القيمة |
|--------|--------|
| إجمالي المراحل | 15 مرحلة |
| إجمالي المهام | 54 مهمة |
| الحالة الحالية | 🟡 قيد التطوير — Phase 5 |
| المكتمل حتى الآن | Phase 1 → 4 ✅ |

---

## 🗺️ خريطة المراحل

```
Phase 1  → الأساس (DB + Enums + Models)          ✅ مكتمل
Phase 2  → المصادقة                               ✅ مكتمل
Phase 3  → Layout + Components                    ✅ مكتمل
Phase 4  → المشاريع                               ✅ مكتمل
Phase 5  → الفئات                                 🔄 جارٍ
Phase 6  → المعاملات ⭐ (المحرك الأساسي)          ⬜ pending
Phase 7  → لوحة التحكم                            ⬜ pending
Phase 8  → الديون                                 ⬜ pending
Phase 9  → التقارير                               ⬜ pending
Phase 10 → الإشعارات                              ⬜ pending
Phase 11 → الاشتراكات                             ⬜ pending
Phase 12 → الإعدادات                              ⬜ pending
Phase 13 → الأمان والجودة                         ⬜ pending
Phase 14 → الإنتاج والـ API                       ⬜ pending
Phase 15 → لوحة الإدارة (Laravel Filament)        ⬜ pending
```

---

## 🔐 Phase 1 — إعداد المشروع وقاعدة البيانات ✅

> الهدف: بناء الأساس التقني الكامل قبل أي موديول.

---

### ✅ المهمة 1.1 — تثبيت Laravel 12 وإعداد البيئة

**الحالة:** `completed`

**المنجز:**
- [x] تثبيت Laravel 12 + PHP 8.2 (اختيار مقصود للاستقرار)
- [x] إعداد `.env` (DB, Mail, Queue, Cache)
- [x] تثبيت Tailwind CSS v4 + Alpine.js
- [x] تثبيت Laravel Breeze (Blade)
- [x] إعداد ULID للـ Models عبر `HasUlids`
- [x] تثبيت `spatie/laravel-permission`
- [x] تثبيت `laravel/telescope` (dev only)

---

### ✅ المهمة 1.2 — إنشاء جميع Migrations

**الحالة:** `completed`

**المنجز:**
- [x] `users` — إضافة: currency, timezone, subscription_plan
- [x] `projects` — مع color, currency, type, is_active
- [x] `categories` — مع type enum, icon, color
- [x] `transactions` — مع indexes مركّبة على (user_id, transaction_date)
- [x] `debts` — مع remaining_amount, status
- [x] `budgets` — مع period enum, month, year
- [x] `recurring_transactions` — مع frequency enum, next_due_date
- [x] `subscriptions` — مع provider data

---

### ✅ المهمة 1.3 — إنشاء Enums و Traits الأساسية

**الحالة:** `completed`

**المنجز:**
- [x] `TransactionType` — income / expense / transfer
- [x] `DebtType` — borrowed / lent
- [x] `DebtStatus` — active / partially_paid / paid
- [x] `SubscriptionPlan` — free / pro / business
- [x] `ProjectType` — personal / business
- [x] `RecurringFrequency` — daily / weekly / monthly / yearly
- [x] `BelongsToUser` Trait — Global Scope للعزل التلقائي
- [x] `MoneyFormatter` Helper

---

### ✅ المهمة 1.4 — إنشاء Models مع العلاقات

**الحالة:** `completed`

**المنجز:**
- [x] `User` — hasMany all modules, canCreateMoreProjects()
- [x] `Project` — HasUlids, SoftDeletes, BelongsToUser, netProfit()
- [x] `Category` — HasUlids, BelongsToUser
- [x] `Transaction` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Debt` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Budget` — HasUlids, BelongsToUser, usagePercentage()
- [x] `RecurringTransaction` — HasUlids, BelongsToUser
- [x] `Subscription` — HasUlids

---

## 🔐 Phase 2 — موديول المصادقة (Authentication) ✅

> الهدف: نظام مصادقة SaaS احترافي آمن وقابل للتوسع.

---

### ✅ المهمة 2.1 — صفحة التسجيل المخصصة

**الحالة:** `completed`

**المنجز:**
- [x] نموذج التسجيل: name, email, password, currency, timezone
- [x] `StoreRegisterRequest` مع validation كامل بالعربية
- [x] `RegisterUserAction` — إنشاء User + 12 فئة افتراضية تلقائياً
- [x] تصميم Blade احترافي RTL بـ Tailwind + Tajawal Font
- [x] إضافة توقيت فلسطين (Asia/Jerusalem)

---

### ✅ المهمة 2.2 — تسجيل الدخول وإعادة تعيين كلمة المرور

**الحالة:** `completed`

**المنجز:**
- [x] صفحة تسجيل دخول عربية (Remember Me)
- [x] صفحة "نسيت كلمة المرور" + بريد إعادة التعيين
- [x] تأمين المسارات: `auth` + `verified` middleware
- [x] تصميم موحّد مع صفحة التسجيل

---

## 🏗️ Phase 3 — Layout وBlade Components الأساسية ✅

> الهدف: هيكل واجهة متكامل قبل بناء أي موديول.

---

### ✅ المهمة 3.1 — بناء App Layout الرئيسي

**الحالة:** `completed`

**المنجز:**
- [x] `layouts/app.blade.php` — Sidebar + Topbar + Content (RTL)
- [x] Sidebar: روابط التنقل، Alpine.js mobile toggle
- [x] Topbar: اسم الصفحة، Breadcrumb slot، إشعارات، قائمة مستخدم
- [x] Flash messages (success/error) مع auto-dismiss

---

### ✅ المهمة 3.2 — بناء Blade Components المشتركة

**الحالة:** `completed`

**المنجز:**
- [x] `stats-card` — قيمة + أيقونة + نسبة تغيير
- [x] `badge` — 7 ألوان
- [x] `empty-state` — مع action button اختياري
- [x] `modal` — Alpine.js events based
- [x] `progress-bar` — لون ديناميكي حسب النسبة
- [x] `nav-item` — active state

---

## 📁 Phase 4 — موديول المشاريع (Projects) ✅

> الهدف: عزل مالي كامل لكل مشروع مع واجهة بصرية واضحة.

---

### ✅ المهمة 4.1 — Actions وServices للمشاريع

**الحالة:** `completed`

**المنجز:**
- [x] `ProjectData` DTO
- [x] `CreateProjectAction`
- [x] `UpdateProjectAction`
- [x] `DeleteProjectAction` (SoftDelete)
- [x] `ProjectFinancialService` — getSummary(), getPortfolioSummary()
- [x] `ProjectPolicy` — view, create (مع حدود الخطة), update, delete
- [x] `StoreProjectRequest` + `UpdateProjectRequest` (Arabic validation)

---

### ✅ المهمة 4.2 — Controller وViews للمشاريع

**الحالة:** `completed`

**المنجز:**
- [x] `ProjectController` — CRUD كامل (Resource Controller)
- [x] `projects/index.blade.php` — شبكة بطاقات + Portfolio Summary
- [x] `projects/_card.blade.php` — بطاقة مع dropdown menu وملخص مالي مصغر
- [x] `projects/show.blade.php` — 4 KPIs + آخر المعاملات
- [x] `projects/create.blade.php` + `edit.blade.php`
- [x] `projects/_form.blade.php` — Partial مشترك مع color picker وtype selector
- [x] فصل المشاريع: تجارية 💼 / شخصية 🏠

---

### ✅ المهمة 4.3.1 — تمييز المشاريع (شخصي / تجاري)

**الحالة:** `completed`

**المنجز:**
- [x] `ProjectType` enum (personal / business) في Migration
- [x] اختيار نوع المشروع في نموذج الإنشاء/التعديل
- [x] عرض منفصل في index: تجارية ثم شخصية
- [x] `ProjectFinancialService` يدعم الفلترة بالنوع

---

## 🏷️ Phase 5 — موديول الفئات (Categories) 🔄

> الهدف: فئات مرنة مع فئات افتراضية ذكية عند إنشاء الحساب.

---

### ⬜ المهمة 5.1 — Actions وSeeder للفئات

**الحالة:** `pending`

**المطلوب:**
- [ ] `CategoryData` DTO
- [ ] `CreateCategoryAction` / `UpdateCategoryAction` / `DeleteCategoryAction`
- [ ] `CategoryPolicy`
- [ ] `StoreCategoryRequest` + `UpdateCategoryRequest`
- [ ] ملاحظة: الفئات الافتراضية تُنشأ بالفعل في `RegisterUserAction`

---

### ⬜ المهمة 5.2 — Controller وViews للفئات

**الحالة:** `pending`

**المطلوب:**
- [ ] `CategoryController` — CRUD كامل
- [ ] `categories/index.blade.php` — قائمة مقسّمة: دخل / مصروف
- [ ] إنشاء/تعديل inline بـ Alpine.js (بدون صفحة منفصلة)
- [ ] عرض الأيقونة واللون لكل فئة

---

## 💰 Phase 4.5 — موديول الميزانية (Budget)

> الهدف: سقف مصروفات ذكي مع تنبيهات تلقائية.

---

### ⬜ المهمة 4.5.1 — جدول الميزانية وActions

**الحالة:** `pending`

**المطلوب:**
- [ ] `BudgetData` DTO
- [ ] `CreateBudgetAction` / `UpdateBudgetAction` / `DeleteBudgetAction`
- [ ] `BudgetTrackerService` — نسبة الاستهلاك، تجاوز 80%، تجاوز 100%
- [ ] `BudgetPolicy`
- [ ] `StoreBudgetRequest`

---

### ⬜ المهمة 4.5.2 — واجهة الميزانية

**الحالة:** `pending`

**المطلوب:**
- [ ] `budget/index.blade.php` مع progress bars ملوّنة
- [ ] إضافة/تعديل ميزانية inline بـ Alpine.js
- [ ] Widget ملخص في لوحة التحكم

---

## 🔁 Phase 5.5 — الالتزامات الشهرية الثابتة (Recurring)

> الهدف: تسجيل تلقائي للمدفوعات المتكررة.

---

### ⬜ المهمة 5.5.1 — جدول Recurring وActions

**الحالة:** `pending`

**المطلوب:**
- [ ] `RecurringTransactionData` DTO
- [ ] `CreateRecurringAction` / `UpdateRecurringAction` / `ToggleRecurringAction`
- [ ] `ProcessRecurringTransactionsCommand` (Scheduler يومي)
- [ ] `RecurringPolicy`
- [ ] `StoreRecurringRequest`

---

### ⬜ المهمة 5.5.2 — واجهة الالتزامات المتكررة

**الحالة:** `pending`

**المطلوب:**
- [ ] `recurring/index.blade.php` مع Badges الحالة
- [ ] `recurring/create.blade.php`
- [ ] Widget "الالتزامات القادمة" في Dashboard

---

## 💸 Phase 6 — موديول المعاملات (Transactions) ⭐

> الهدف: المحرك الأساسي للمنصة.

---

### ⬜ المهمة 6.1 — Actions وServices للمعاملات

**الحالة:** `pending`

**المطلوب:**
- [ ] `TransactionData` DTO
- [ ] `CreateTransactionAction` / `UpdateTransactionAction` / `DeleteTransactionAction`
- [ ] `TransactionService` — تجميع، فلترة، بحث
- [ ] `BalanceCalculatorService`
- [ ] `TransactionPolicy`
- [ ] `StoreTransactionRequest` + `UpdateTransactionRequest`

---

### ⬜ المهمة 6.2 — Controller وViews للمعاملات

**الحالة:** `pending`

**المطلوب:**
- [ ] `TransactionController` — CRUD كامل
- [ ] `transactions/index.blade.php` — جدول + فلترة متقدمة + بحث + Pagination
- [ ] `transactions/create.blade.php` — نموذج ذكي ديناميكي
- [ ] `transactions/edit.blade.php`
- [ ] تصدير CSV

---

## 📊 Phase 7 — لوحة التحكم (Dashboard)

> الهدف: صورة مالية فورية وواضحة.

---

### ⬜ المهمة 7.1 — DashboardService وبيانات KPIs

**الحالة:** `pending`

**المطلوب:**
- [ ] دخل/مصروفات/ربح (الشهر الحالي vs السابق + %)
- [ ] عدد المشاريع النشطة
- [ ] الديون المستحقة خلال 7 أيام
- [ ] آخر 5 معاملات
- [ ] بيانات الرسم البياني (6 أشهر)
- [ ] Cache TTL 30 دقيقة

---

### ⬜ المهمة 7.2 — واجهة Dashboard الاحترافية

**الحالة:** `pending`

**المطلوب:**
- [ ] 4 بطاقات KPI رئيسية
- [ ] رسم بياني Chart.js
- [ ] جدول آخر المعاملات
- [ ] قائمة المشاريع النشطة
- [ ] تنبيهات الديون القريبة

---

## 💳 Phase 8 — موديول الديون والالتزامات (Debts)

---

### ⬜ المهمة 8.1 — Actions وServices للديون

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtData` DTO
- [ ] `CreateDebtAction` / `RecordPartialPaymentAction` / `MarkDebtAsPaidAction`
- [ ] `DebtTrackerService`
- [ ] `DebtPolicy`
- [ ] `StoreDebtRequest`

---

### ⬜ المهمة 8.2 — Controller وViews للديون

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtController` — CRUD + `recordPayment`
- [ ] `debts/index.blade.php` — تبويبان + شريط تقدم السداد
- [ ] Modal تسجيل الدفعة الجزئية

---

## 📈 Phase 9 — التقارير والتحليلات (Reports)

---

### ⬜ المهمة 9.1 — Services التقارير

**الحالة:** `pending`

**المطلوب:**
- [ ] `MonthlyReportService` / `ProfitLossService` / `CashFlowService`
- [ ] `TopCategoriesService` / `ProjectComparisonService`
- [ ] Cache لجميع التقارير

---

### ⬜ المهمة 9.2 — واجهة التقارير التفاعلية

**الحالة:** `pending`

**المطلوب:**
- [ ] فلتر الفترة + فلتر المشروع
- [ ] Chart.js رسوم بيانية متعددة
- [ ] تصدير PDF + CSV

---

## 🔔 Phase 10 — موديول الإشعارات (Notifications)

---

### ⬜ المهمة 10.1 — NotificationService وQueue Jobs

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtDueSoonNotification` / `WeeklyFinancialSummaryNotification`
- [ ] Scheduled Commands يومياً/أسبوعياً
- [ ] Queue-based (لا sync)

---

### ⬜ المهمة 10.2 — واجهة الإشعارات

**الحالة:** `pending`

**المطلوب:**
- [ ] Notification Bell + Dropdown + صفحة كاملة

---

## 💼 Phase 11 — الاشتراكات والفوترة (Billing)

---

### ⬜ المهمة 11.1 — SubscriptionService وتكامل Stripe

**الحالة:** `pending`

**حدود الخطط:**

| الميزة | Free | Pro | Business |
|--------|------|-----|---------|
| المشاريع | 2 | 10 | غير محدود |
| المعاملات/شهر | 50 | 500 | غير محدود |
| التصدير | ❌ | ✅ | ✅ |
| التقارير المتقدمة | ❌ | ✅ | ✅ |
| API Access | ❌ | ❌ | ✅ |

---

### ⬜ المهمة 11.2 — صفحة الأسعار وإدارة الاشتراك

**الحالة:** `pending`

---

## ⚙️ Phase 12 — الإعدادات (Settings)

---

### ⬜ المهمة 12.1 — صفحة الإعدادات الكاملة

**الحالة:** `pending`

**التبويبات:** الملف الشخصي / الأمان / الإشعارات / الاشتراك / حذف الحساب

---

## 🔒 Phase 13 — الأمان والأداء والجودة

---

### ⬜ المهمة 13.1 / 13.2 / 13.3

**الحالة:** `pending`

---

## 🚀 Phase 14 — الإعداد للإنتاج والـ API

---

### ⬜ المهمة 14.1 / 14.2

**الحالة:** `pending`

---

## 📋 جدول التتبع السريع

| # | المهمة | الحالة |
|---|--------|--------|
| 1.1 | تثبيت Laravel 12 وإعداد البيئة | ✅ |
| 1.2 | إنشاء جميع Migrations | ✅ |
| 1.3 | Enums و Traits الأساسية | ✅ |
| 1.4 | Models مع العلاقات | ✅ |
| 2.1 | صفحة التسجيل المخصصة | ✅ |
| 2.2 | تسجيل الدخول وإعادة كلمة المرور | ✅ |
| 3.1 | App Layout الرئيسي | ✅ |
| 3.2 | Blade Components المشتركة | ✅ |
| 4.1 | Actions وServices للمشاريع | ✅ |
| 4.2 | Controller وViews للمشاريع | ✅ |
| 4.3.1 | فصل المصروف الشخصي عن التجاري | ✅ |
| 4.5.1 | جدول الميزانية وActions | ⬜ |
| 4.5.2 | واجهة الميزانية | ⬜ |
| 5.1 | Actions وSeeder للفئات | ⬜ |
| 5.2 | Controller وViews للفئات | ⬜ |
| 5.5.1 | جدول Recurring وActions | ⬜ |
| 5.5.2 | واجهة الالتزامات المتكررة | ⬜ |
| 6.1 | Actions وServices للمعاملات | ⬜ |
| 6.2 | Controller وViews للمعاملات | ⬜ |
| 7.1 | DashboardService وبيانات KPIs | ⬜ |
| 7.2 | واجهة Dashboard الاحترافية | ⬜ |
| 8.1 | Actions وServices للديون | ⬜ |
| 8.2 | Controller وViews للديون | ⬜ |
| 9.1 | Services التقارير | ⬜ |
| 9.2 | واجهة التقارير التفاعلية | ⬜ |
| 10.1 | NotificationService وQueue Jobs | ⬜ |
| 10.2 | واجهة الإشعارات | ⬜ |
| 11.1 | SubscriptionService وتكامل Stripe | ⬜ |
| 11.2 | صفحة الأسعار وإدارة الاشتراك | ⬜ |
| 12.1 | صفحة الإعدادات الكاملة | ⬜ |
| 13.1 | مراجعة الأمان والصلاحيات | ⬜ |
| 13.2 | تحسين الأداء والاستعلامات | ⬜ |
| 13.3 | Feature Tests الأساسية | ⬜ |
| 14.1 | إعداد بيئة الإنتاج | ⬜ |
| 14.2 | تحضير REST API | ⬜ |

---

## 🏁 ترتيب التطوير الفعلي

```
✅ 1.1 → 1.2 → 1.3 → 1.4   (الأساس)
✅ 2.1 → 2.2                 (المصادقة)
✅ 3.1 → 3.2                 (الهيكل البصري)
✅ 4.1 → 4.2 → 4.3.1        (المشاريع)
🔄 5.1 → 5.2                 (الفئات — تحتاجها المعاملات)
⬜ 6.1 → 6.2                 (المعاملات — القلب النابض)
⬜ 7.1 → 7.2                 (لوحة التحكم)
⬜ 8 → 9 → 10 → 11 → 12     (بقية الموديولات)
⬜ 13.1 → 13.2 → 13.3        (الجودة)
⬜ 14.1 → 14.2               (الإطلاق)
```

---

---

## 🛡️ Phase 15 — لوحة الإدارة (Laravel Filament)

> الهدف: لوحة Admin احترافية لإدارة المستخدمين والاشتراكات والإحصاءات الكلية.

---

### ⬜ المهمة 15.1 — تثبيت Filament وإعداد البيئة

**الحالة:** `pending`

**المطلوب:**
- [ ] تثبيت `filament/filament`
- [ ] إنشاء Admin User عبر `php artisan make:filament-user`
- [ ] حماية المسار `/admin` بـ Middleware منفصل
- [ ] تخصيص Brand (اسم + لوجو Workuflow)
- [ ] إعداد اللغة العربية للـ Panel

---

### ⬜ المهمة 15.2 — Resources إدارة المستخدمين والاشتراكات

**الحالة:** `pending`

**المطلوب:**
- [ ] `UserResource` — عرض، بحث، فلترة بالخطة
- [ ] `SubscriptionResource` — إدارة الاشتراكات، تغيير الخطة يدوياً
- [ ] إحصاءات سريعة: عدد المستخدمين، المشتركين الفعّالين، الإيراد
- [ ] Widget: مخطط نمو المستخدمين (آخر 30 يوم)
- [ ] إجراءات: تعليق حساب، إعادة تعيين خطة، حذف

---

*وثيقة حية — تُحدَّث مع إتمام كل مهمة*
