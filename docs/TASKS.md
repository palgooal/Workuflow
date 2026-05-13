# ✅ خطة المهام الكاملة — Workuflow SaaS Financial Platform

> وثيقة تتبع المهام — Laravel 12 / PHP 8.2  
> آخر تحديث: مايو 2026

---

## 📊 ملخص المشروع

| البيان | القيمة |
|--------|--------|
| إجمالي المراحل | 15 مرحلة + Marketing |
| إجمالي المهام | 56 مهمة |
| الحالة الحالية | ✅ Phases 1–15 + Landing Page مكتملة |
| اختبارات Pest | 53/53 ✅ |

---

## 🗺️ خريطة المراحل

```
Phase 1  → الأساس (DB + Enums + Models)               ✅ مكتمل
Phase 2  → المصادقة                                    ✅ مكتمل
Phase 3  → Layout + Components                         ✅ مكتمل
Phase 4  → المشاريع                                    ✅ مكتمل
Phase 4.3→ فصل الشخصي/التجاري                         ✅ مكتمل
Phase 4.5→ الميزانية (Budget)                          ✅ مكتمل
Phase 5  → الفئات (Categories)                        ✅ مكتمل
Phase 5.5→ الالتزامات المتكررة (Recurring)             ✅ مكتمل
Phase 6  → المعاملات ⭐ (المحرك الأساسي)               ✅ مكتمل
Phase 7  → لوحة التحكم (Dashboard)                    ✅ مكتمل
Phase 8  → الديون (Debts)                              ✅ مكتمل
Phase 9  → التقارير (Reports)                          ✅ مكتمل
Phase 10 → الإشعارات (Notifications)                   ✅ مكتمل
Phase 11 → الاشتراكات والفوترة (Billing)               ✅ مكتمل (بدون مزود دفع — مُعلَّق)
Phase 12 → الإعدادات (Settings)                        ✅ مكتمل
Phase 13 → الأمان والجودة (Tests)                      ✅ مكتمل — 53/53
Phase 14 → الإنتاج والـ API                            ⬜ مستقبلي
Phase 15 → لوحة الإدارة (Laravel Filament)             ✅ مكتمل
Marketing→ الصفحة التسويقية (Landing Page)             ✅ مكتمل
Admin+   → تطوير Admin المتقدم (مقترح)                 ⬜ مخطّط
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
- [x] `users` — إضافة: currency, timezone, subscription_plan, payment_customer_id
- [x] `projects` — مع color, currency, type (ProjectType), is_active
- [x] `categories` — مع type enum, icon, color
- [x] `transactions` — مع indexes مركّبة على (user_id, transaction_date)
- [x] `debts` — مع remaining_amount, status
- [x] `budgets` — مع period enum, month, year
- [x] `recurring_transactions` — مع frequency enum, next_due_date, end_date
- [x] `subscriptions` — مع provider_subscription_id, payment_provider
- [x] `model_has_roles` / `roles` — spatie/permission

---

### ✅ المهمة 1.3 — إنشاء Enums و Traits الأساسية

**الحالة:** `completed`

**المنجز:**
- [x] `TransactionType` — income / expense / transfer
- [x] `DebtType` — borrowed / lent
- [x] `DebtStatus` — active / partially_paid / paid
- [x] `SubscriptionPlan` — free / pro / business
- [x] `ProjectType` — personal / business
- [x] `RecurringFrequency` — daily / weekly / monthly / yearly (مع `nextDate(Carbon $from): Carbon`)
- [x] `BelongsToUser` Trait — Global Scope للعزل التلقائي
- [x] `MoneyFormatter` Helper

---

### ✅ المهمة 1.4 — إنشاء Models مع العلاقات

**الحالة:** `completed`

**المنجز:**
- [x] `User` — HasRoles, FilamentUser, canAccessPanel(), currentPlan(), payment_customer_id
- [x] `Project` — HasUlids, SoftDeletes, BelongsToUser, netProfit()
- [x] `Category` — HasUlids, BelongsToUser
- [x] `Transaction` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Debt` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Budget` — HasUlids, BelongsToUser, usagePercentage()
- [x] `RecurringTransaction` — HasUlids, BelongsToUser
- [x] `Subscription` — HasUlids, scopes: active()

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
- [x] Sidebar: روابط التنقل لجميع الموديولات، Alpine.js mobile toggle
- [x] Topbar: اسم الصفحة، Breadcrumb slot، إشعارات، قائمة مستخدم
- [x] Flash messages (success/error/info) مع auto-dismiss

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
- [x] `ProjectData` DTO (fromRequest)
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

---

### ✅ المهمة 4.3.1 — تمييز المشاريع (شخصي / تجاري)

**الحالة:** `completed`

**المنجز:**
- [x] `ProjectType` enum (personal / business) في Migration
- [x] اختيار نوع المشروع في نموذج الإنشاء/التعديل
- [x] عرض منفصل في index: تجارية 💼 ثم شخصية 🏠
- [x] `ProjectFinancialService` يدعم الفلترة بالنوع

---

## 💰 Phase 4.5 — موديول الميزانية (Budget) ✅

> الهدف: سقف مصروفات ذكي مع تنبيهات تلقائية.

---

### ✅ المهمة 4.5.1 — جدول الميزانية وActions

**الحالة:** `completed`

**المنجز:**
- [x] `BudgetData` DTO (fromRequest)
- [x] `CreateBudgetAction` / `UpdateBudgetAction` / `DeleteBudgetAction`
- [x] `BudgetTrackerService` — نسبة الاستهلاك، تجاوز 80%، تجاوز 100%
- [x] `BudgetPolicy`
- [x] `StoreBudgetRequest`

---

### ✅ المهمة 4.5.2 — واجهة الميزانية

**الحالة:** `completed`

**المنجز:**
- [x] `budget/index.blade.php` مع progress bars ملوّنة (أخضر/برتقالي/أحمر)
- [x] إضافة/تعديل ميزانية inline بـ Alpine.js
- [x] تسجيل `BudgetPolicy` في `AppServiceProvider`

---

## 🏷️ Phase 5 — موديول الفئات (Categories) ✅

> الهدف: فئات مرنة مع فئات افتراضية ذكية عند إنشاء الحساب.

---

### ✅ المهمة 5.1 — Actions وSeeder للفئات

**الحالة:** `completed`

**المنجز:**
- [x] `CategoryData` DTO
- [x] `CreateCategoryAction` / `UpdateCategoryAction` / `DeleteCategoryAction`
- [x] `CategoryPolicy`
- [x] `StoreCategoryRequest` + `UpdateCategoryRequest`
- [x] الفئات الافتراضية تُنشأ في `RegisterUserAction` (12 فئة)

---

### ✅ المهمة 5.2 — Controller وViews للفئات

**الحالة:** `completed`

**المنجز:**
- [x] `CategoryController` — CRUD كامل
- [x] `categories/index.blade.php` — قائمة مقسّمة: دخل / مصروف
- [x] إنشاء/تعديل inline بـ Alpine.js

---

## 🔁 Phase 5.5 — الالتزامات الشهرية الثابتة (Recurring) ✅

> الهدف: تسجيل تلقائي للمدفوعات المتكررة.

---

### ✅ المهمة 5.5.1 — جدول Recurring وActions

**الحالة:** `completed`

**المنجز:**
- [x] `RecurringData` DTO (fromRequest)
- [x] `CreateRecurringAction` / `UpdateRecurringAction` / `ToggleRecurringAction`
- [x] `ProcessRecurringAction` — ينشئ Transaction + يُحدّث next_due_date + يُعطّل عند انتهاء end_date
- [x] `RecurringService` — getAll(), processDueForUser(), processDueForAll()
- [x] `ProcessRecurringTransactions` Command — Scheduler يومي 01:00
- [x] `RecurringPolicy`
- [x] `StoreRecurringRequest`
- [x] تسجيل `RecurringPolicy` في `AppServiceProvider`

---

### ✅ المهمة 5.5.2 — واجهة الالتزامات المتكررة

**الحالة:** `completed`

**المنجز:**
- [x] `recurring/index.blade.php` مع Badges الحالة
- [x] `recurring/create.blade.php` + `recurring/edit.blade.php`
- [x] `recurring.toggle` + `recurring.process-now` routes
- [x] `RecurringController` — CRUD + toggle + processNow

---

## 💸 Phase 6 — موديول المعاملات (Transactions) ✅

> الهدف: المحرك الأساسي للمنصة.

---

### ✅ المهمة 6.1 — Actions وServices للمعاملات

**الحالة:** `completed`

**المنجز:**
- [x] `TransactionData` DTO (fromRequest)
- [x] `CreateTransactionAction` / `UpdateTransactionAction` / `DeleteTransactionAction`
- [x] `TransactionService` — تجميع، فلترة، بحث
- [x] `BalanceCalculatorService`
- [x] `TransactionPolicy`
- [x] `StoreTransactionRequest` + `UpdateTransactionRequest`

---

### ✅ المهمة 6.2 — Controller وViews للمعاملات

**الحالة:** `completed`

**المنجز:**
- [x] `TransactionController` — CRUD كامل
- [x] `transactions/index.blade.php` — جدول + فلترة متقدمة + بحث + Pagination
- [x] `transactions/create.blade.php` — نموذج ذكي ديناميكي
- [x] `transactions/edit.blade.php`
- [x] تصدير CSV

---

## 📊 Phase 7 — لوحة التحكم (Dashboard) ✅

> الهدف: صورة مالية فورية وواضحة.

---

### ✅ المهمة 7.1 — DashboardService وبيانات KPIs

**الحالة:** `completed`

**المنجز:**
- [x] دخل/مصروفات/ربح (الشهر الحالي vs السابق + %)
- [x] عدد المشاريع النشطة
- [x] الديون المستحقة خلال 7 أيام
- [x] آخر 5 معاملات
- [x] بيانات الرسم البياني (6 أشهر)
- [x] Cache TTL 30 دقيقة

---

### ✅ المهمة 7.2 — واجهة Dashboard الاحترافية

**الحالة:** `completed`

**المنجز:**
- [x] 4 بطاقات KPI رئيسية
- [x] رسم بياني Chart.js
- [x] جدول آخر المعاملات
- [x] قائمة المشاريع النشطة
- [x] تنبيهات الديون القريبة

---

## 💳 Phase 8 — موديول الديون والالتزامات (Debts) ✅

---

### ✅ المهمة 8.1 — Actions وServices للديون

**الحالة:** `completed`

**المنجز:**
- [x] `DebtData` DTO (fromRequest)
- [x] `CreateDebtAction` / `RecordPartialPaymentAction` / `MarkDebtAsPaidAction`
- [x] `DebtTrackerService`
- [x] `DebtPolicy`
- [x] `StoreDebtRequest`

---

### ✅ المهمة 8.2 — Controller وViews للديون

**الحالة:** `completed`

**المنجز:**
- [x] `DebtController` — CRUD + `recordPayment`
- [x] `debts/index.blade.php` — تبويبان (عليّ / لي) + شريط تقدم السداد
- [x] Modal تسجيل الدفعة الجزئية
- [x] `SendDebtAlerts` Command — Scheduler يومي 08:00

---

## 📈 Phase 9 — التقارير والتحليلات (Reports) ✅

---

### ✅ المهمة 9.1 — Services التقارير

**الحالة:** `completed`

**المنجز:**
- [x] `MonthlyReportService` / `ProfitLossService` / `CashFlowService`
- [x] `TopCategoriesService` / `ProjectComparisonService`
- [x] Cache لجميع التقارير

---

### ✅ المهمة 9.2 — واجهة التقارير التفاعلية

**الحالة:** `completed`

**المنجز:**
- [x] فلتر الفترة + فلتر المشروع
- [x] Chart.js رسوم بيانية متعددة
- [x] تصدير PDF + CSV

---

## 🔔 Phase 10 — موديول الإشعارات (Notifications) ✅

---

### ✅ المهمة 10.1 — NotificationService وQueue Jobs

**الحالة:** `completed`

**المنجز:**
- [x] `DebtDueSoonNotification`
- [x] `WeeklyFinancialSummaryNotification`
- [x] `SendDebtAlerts` Command (يومي 08:00)
- [x] Queue-based

---

### ✅ المهمة 10.2 — واجهة الإشعارات

**الحالة:** `completed`

**المنجز:**
- [x] Notification Bell + Dropdown + صفحة كاملة

---

## 💼 Phase 11 — الاشتراكات والفوترة (Billing) ✅

> **ملاحظة هامة:** تم بناء الهيكل الكامل بدون ربط مزود دفع بعينه.  
> سيُضاف المزود (مثل Tap أو Paddle أو غيره) في نهاية المشروع عبر تنفيذ `PaymentProviderInterface`.

---

### ✅ المهمة 11.1 — SubscriptionService (بدون مزود دفع)

**الحالة:** `completed`

**حدود الخطط:**

| الميزة | Free | Pro | Business |
|--------|------|-----|---------|
| المشاريع | 2 | 10 | غير محدود |
| المعاملات/شهر | 50 | 500 | غير محدود |
| التصدير | ❌ | ✅ | ✅ |
| التقارير المتقدمة | ❌ | ✅ | ✅ |
| API Access | ❌ | ❌ | ✅ |

**المنجز:**
- [x] `PaymentProviderInterface` — contract للمزود المستقبلي (createCheckoutUrl, createPortalUrl, parseWebhook)
- [x] `SubscriptionService` — activatePlan(), cancelPlan(), getCurrentSubscription(), getPlanPrices(), isPaymentProviderConfigured()
- [x] `config/billing.php` — provider, plans, credentials (كلها فارغة حتى يُضاف المزود)
- [x] Migration: `payment_customer_id` (generic, ليس stripe-specific)
- [x] `BillingController` — index, checkout, success, portal, webhook (جميعها مع TODO comments)

---

### ✅ المهمة 11.2 — صفحة الأسعار وإدارة الاشتراك

**الحالة:** `completed`

**المنجز:**
- [x] `billing/index.blade.php` — صفحة أسعار كاملة (Free / Pro / Business)
- [x] تحذير amber عندما `$providerReady === false`
- [x] `billing/success.blade.php`
- [x] Routes: billing.index, billing.checkout, billing.success, billing.portal, billing.webhook

---

## ⚙️ Phase 12 — الإعدادات (Settings) ✅

---

### ✅ المهمة 12.1 — صفحة الإعدادات الكاملة

**الحالة:** `completed`

**التبويبات:** الملف الشخصي / الأمان / الإشعارات / الاشتراك / حذف الحساب

---

## 🔒 Phase 13 — الأمان والأداء والجودة ✅

---

### ✅ المهمة 13.1 — مراجعة الأمان والصلاحيات

**الحالة:** `completed`

**المنجز:**
- [x] `BelongsToUser` Global Scope يحمي جميع البيانات تلقائياً
- [x] Policies على جميع الموديولات
- [x] CSRF protection على جميع النماذج
- [x] Rate limiting

---

### ✅ المهمة 13.2 — تحسين الأداء والاستعلامات

**الحالة:** `completed`

**المنجز:**
- [x] Eager Loading على جميع العلاقات
- [x] Indexes مركّبة على الجداول الرئيسية
- [x] Cache للتقارير

---

### ✅ المهمة 13.3 — Feature Tests الأساسية

**الحالة:** `completed`

**المنجز:**
- [x] 53 اختبار Pest — جميعها ✅ (53/53)
- [x] تغطية: Auth, Projects, Categories, Transactions, Debts, Budget, Recurring, Billing

---

## 🚀 Phase 14 — الإعداد للإنتاج والـ API

---

### ⬜ المهمة 14.1 — إعداد بيئة الإنتاج

**الحالة:** `pending`

**المطلوب:**
- [ ] إعداد `.env.production`
- [ ] Horizon للـ Queue
- [ ] Telescope حماية في الإنتاج
- [ ] Logging و Error tracking

---

### ⬜ المهمة 14.2 — تحضير REST API

**الحالة:** `pending`

**المطلوب:**
- [ ] Laravel Sanctum للـ API tokens
- [ ] API Resources لجميع الموديولات
- [ ] API Rate limiting
- [ ] API Documentation

---

## 🛡️ Phase 15 — لوحة الإدارة (Laravel Filament) ✅

> الهدف: لوحة Admin احترافية لإدارة المستخدمين والاشتراكات والإحصاءات الكلية.

---

### ✅ المهمة 15.1 — تثبيت Filament وإعداد البيئة

**الحالة:** `completed`

**المنجز:**
- [x] تثبيت `filament/filament` v3
- [x] `AdminPanelProvider` — Panel عند `/admin`، لون Indigo، brandName 'Workuflow Admin'
- [x] `FilamentUser` interface في `User` model
- [x] `canAccessPanel()` — يتحقق من role `super_admin` عبر `spatie/laravel-permission`
- [x] `AdminSeeder` — ينشئ role `super_admin` + مستخدم `admin@workuflow.com` / `Admin@123`
- [x] Navigation Groups: 'إدارة المستخدمين', 'البيانات المالية', 'النظام'

---

### ✅ المهمة 15.2 — Resources إدارة المستخدمين والاشتراكات

**الحالة:** `completed`

**المنجز:**
- [x] `UserResource` — CRUD كامل: name, email, password, subscription_plan, currency, timezone, roles
  - `getNavigationBadge()` يعرض عدد المستخدمين
  - `BadgeColumn` للـ subscription_plan مع ترميز لوني
- [x] `TransactionResource` — قراءة فقط (read-only لجميع مستخدمي المنصة)
  - `modifyQueryUsing()` مع `withoutGlobalScopes()`
  - `canCreate(): bool { return false; }`
- [x] `StatsOverviewWidget` — 4 إحصاءات كلية: مستخدمون، مشتركون، دخل الشهر، مصروفات الشهر
- [x] `UsersChartWidget` — رسم بياني: مستخدمون جدد آخر 12 شهراً

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
| 4.5.1 | جدول الميزانية وActions | ✅ |
| 4.5.2 | واجهة الميزانية | ✅ |
| 5.1 | Actions وSeeder للفئات | ✅ |
| 5.2 | Controller وViews للفئات | ✅ |
| 5.5.1 | جدول Recurring وActions | ✅ |
| 5.5.2 | واجهة الالتزامات المتكررة | ✅ |
| 6.1 | Actions وServices للمعاملات | ✅ |
| 6.2 | Controller وViews للمعاملات | ✅ |
| 7.1 | DashboardService وبيانات KPIs | ✅ |
| 7.2 | واجهة Dashboard الاحترافية | ✅ |
| 8.1 | Actions وServices للديون | ✅ |
| 8.2 | Controller وViews للديون | ✅ |
| 9.1 | Services التقارير | ✅ |
| 9.2 | واجهة التقارير التفاعلية | ✅ |
| 10.1 | NotificationService وQueue Jobs | ✅ |
| 10.2 | واجهة الإشعارات | ✅ |
| 11.1 | SubscriptionService (بدون مزود) | ✅ |
| 11.2 | صفحة الأسعار وإدارة الاشتراك | ✅ |
| 12.1 | صفحة الإعدادات الكاملة | ✅ |
| 13.1 | مراجعة الأمان والصلاحيات | ✅ |
| 13.2 | تحسين الأداء والاستعلامات | ✅ |
| 13.3 | Feature Tests — 53/53 ✅ | ✅ |
| 14.1 | إعداد بيئة الإنتاج | ⬜ |
| 14.2 | تحضير REST API | ⬜ |
| 15.1 | تثبيت Filament وإعداد البيئة | ✅ |
| 15.2 | Resources إدارة المستخدمين | ✅ |

---

## 🏁 ترتيب التطوير الفعلي

```
✅ 1.1 → 1.2 → 1.3 → 1.4        (الأساس)
✅ 2.1 → 2.2                      (المصادقة)
✅ 3.1 → 3.2                      (الهيكل البصري)
✅ 4.1 → 4.2 → 4.3.1             (المشاريع)
✅ 4.5.1 → 4.5.2                  (الميزانية)
✅ 5.1 → 5.2                      (الفئات)
✅ 5.5.1 → 5.5.2                  (المتكررة)
✅ 6.1 → 6.2                      (المعاملات — القلب النابض)
✅ 7.1 → 7.2                      (لوحة التحكم)
✅ 8.1 → 8.2                      (الديون)
✅ 9.1 → 9.2                      (التقارير)
✅ 10.1 → 10.2                    (الإشعارات)
✅ 11.1 → 11.2                    (الفوترة — هيكل جاهز)
✅ 12.1                            (الإعدادات)
✅ 13.1 → 13.2 → 13.3             (الجودة)
✅ 15.1 → 15.2                    (Filament Admin)
⬜ 14.1 → 14.2                    (الإنتاج والـ API — مستقبلي)
⬜ مزود الدفع                     (يُضاف في نهاية المشروع)
```

---

## 🌐 Marketing — الصفحة التسويقية ✅

> الهدف: صفحة Landing Page احترافية عربية RTL تُسوّق المنصة للزوار الجدد.

---

### ✅ المهمة M.1 — بناء Landing Page

**الحالة:** `completed`

**المنجز:**
- [x] `resources/views/welcome.blade.php` — صفحة كاملة بـ CSS خالص (بدون Tailwind compile)
- [x] Navbar ذكي: يعرض "لوحة التحكم" للمستخدم المسجّل، و"ابدأ مجاناً" للزائر
- [x] Hero section مع معاينة Dashboard مبنية بـ CSS (بطاقات KPI + رسم بياني)
- [x] Pain Points — ٦ مشاكل يعانيها المستخدم المستهدف
- [x] Features — ٨ مميزات رئيسية مع أيقونات
- [x] How It Works — ٤ خطوات بصرية
- [x] Stats — أرقام المنصة (١٢٠٠ مستخدم، ٥٠٠٠ مشروع...)
- [x] Testimonials — ٣ آراء مستخدمين
- [x] Pricing — ٣ خطط (Free / Pro / Business) مرتبطة بـ `billing.index`
- [x] CTA section + Footer كامل مع روابط حقيقية
- [x] تحديث Route `/` في `routes/web.php` — يعرض welcome بدل redirect للـ login
- [x] تصميم RTL كامل بخط Tajawal + ألوان Indigo

**المسار:** `http://workuflow.test/`

---

## 🛡️ Admin+ — خطة تطوير لوحة الإدارة المتقدمة ⬜

> مبني على ما تم في Phase 15 — إضافات مقترحة قبل أو بعد الإنتاج.

---

### ✅ المهمة A.1 — SubscriptionResource

**الحالة:** `completed`

**المنجز:**
- [x] `SubscriptionResource` — عرض جميع اشتراكات المنصة مع معلومات المستخدم
- [x] Action: **تفعيل خطة** — form لاختيار الخطة ← يستدعي `SubscriptionService::activatePlan()`
- [x] Action: **تمديد** — form لاختيار عدد الأشهر (1/2/3/6/12) ← يستدعي `SubscriptionService::extendPlan()`
- [x] Action: **إلغاء اشتراك** — تأكيد ← يستدعي `SubscriptionService::cancelPlan()`
- [x] Filters: بالخطة، الحالة، "تنتهي خلال 7 أيام"
- [x] Tabs: الكل / النشطة / تنتهي قريباً / الملغاة (مع badges)
- [x] `afterCreate()` + `afterSave()` — مزامنة `subscription_plan` في جدول `users`
- [x] `SubscriptionService::extendPlan()` — دالة جديدة تُمدّد `ends_at`
- [x] Navigation Badge يعرض عدد الاشتراكات النشطة (أخضر)

---

### ⬜ المهمة A.2 — توسيع UserResource بـ Actions

**الحالة:** `pending`

**المطلوب:**
- [ ] Action: **تعليق الحساب** — يضيف `suspended_at` أو يُغيّر حالة
- [ ] Action: **إعادة تعيين الخطة** — ترجيع للـ Free plan
- [ ] Action: **إرسال بريد مخصص** — Notification مباشر لمستخدم واحد
- [ ] Action: **حذف البيانات** — حذف بيانات مع إبقاء الحساب (GDPR)
- [ ] Filter إضافي: فلترة بالمنطقة الزمنية والعملة

---

### ⬜ المهمة A.3 — Revenue Dashboard Widget

**الحالة:** `pending`

**المطلوب:**
- [ ] `RevenueWidget` — MRR (Monthly Recurring Revenue)
- [ ] ARR (Annual Recurring Revenue) المتوقع
- [ ] Churn Rate — نسبة إلغاء الاشتراكات الشهرية
- [ ] Donut Chart: توزيع المستخدمين على الخطط (Free / Pro / Business)

---

### ⬜ المهمة A.4 — SystemHealthWidget

**الحالة:** `pending`

**المطلوب:**
- [ ] حالة Queue Jobs (pending / failed)
- [ ] عدد Failed Jobs مع رابط للتفاصيل
- [ ] آخر تشغيل لـ `recurring:process` و `debts:send-alerts`
- [ ] حجم Cache المستخدم

---

## 🔜 المهام المتبقية

| المهمة | الأولوية | الوصف |
|--------|----------|-------|
| A.1 — SubscriptionResource | ✅ مكتمل | إدارة اشتراكات يدوية من Admin |
| A.2 — UserResource Actions | 🔴 قريباً | تعليق، إعادة تعيين، إرسال بريد |
| ربط مزود الدفع | 🔴 عند الجاهزية | تنفيذ `PaymentProviderInterface` (Tap / Paddle / غيره) |
| A.3 — Revenue Widget | 🟠 مهم | MRR, ARR, Churn في Admin |
| A.4 — SystemHealth Widget | 🟡 لاحقاً | صحة Queue و Scheduler |
| Phase 14 — إنتاج | 🟡 لاحقاً | Horizon, Telescope Config, API |

---

*وثيقة حية — آخر تحديث: مايو 2026*
