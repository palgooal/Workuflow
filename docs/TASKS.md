# ✅ خطة المهام الكاملة — دراهم SaaS Financial Platform

> وثيقة تتبع المهام — Laravel 12 / PHP 8.2  
> آخر تحديث: 25 مايو 2026 — Sprint 6 مكتمل بالكامل (S6.1→S6.3) + Sprint 7 مكتمل (S7.1→S7.5)

---

## 📊 ملخص المشروع

| البيان | القيمة |
|--------|--------|
| إجمالي المراحل | 17 مرحلة + Marketing + Admin+ + UX |
| الحالة الحالية | ✅ على الهواء — workuflow.palgoals.com |
| اختبارات Pest | 54/54 ✅ |
| PHP | 8.2 / Laravel 12 |
| مرجع CRM | `docs/CLIENTS-CRM-SPEC-V2.md` (2164 سطر) |

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
Phase 11 → الاشتراكات والفوترة (Billing)               ✅ مكتمل (هيكل جاهز)
Phase 12 → الإعدادات (Settings)                        ✅ مكتمل
Phase 13 → الأمان والجودة (Tests)                      ✅ مكتمل — 54/54
Phase 14 → الإنتاج — cPanel Shared Hosting             ✅ مكتمل — على الهواء
Phase 15 → لوحة الإدارة (Laravel Filament)             ✅ مكتمل
Marketing→ الصفحة التسويقية (Landing Page)             ✅ مكتمل
Admin+   → تطوير Admin المتقدم                         ✅ مكتمل (A.1→A.4)
UX+      → تحسينات تجربة المستخدم                      ✅ مكتمل (U.1→U.3)
Phase 16 → تحسينات مايو 2026 (16.1→16.12)             ✅ مكتمل
Phase 17 → نظام CRM المتقدم (8 Sprints / 38 مهمة)     🔄 قيد التطوير
```

---

## 🔐 Phase 1 — إعداد المشروع وقاعدة البيانات ✅

### ✅ 1.1 — تثبيت Laravel 12 وإعداد البيئة
- [x] تثبيت Laravel 12 + PHP 8.2
- [x] إعداد `.env` (DB, Mail, Queue, Cache)
- [x] تثبيت Tailwind CSS v4 + Alpine.js
- [x] تثبيت Laravel Breeze (Blade)
- [x] إعداد ULID للـ Models عبر `HasUlids`
- [x] تثبيت `spatie/laravel-permission`
- [x] تثبيت `laravel/telescope` (dev only)

### ✅ 1.2 — إنشاء جميع Migrations
- [x] `users` — currency, timezone, subscription_plan, status, payment_customer_id
- [x] `projects` — color, currency, type (ProjectType), is_active
- [x] `categories` — type enum, icon, color
- [x] `transactions` — indexes مركّبة (user_id, transaction_date)
- [x] `debts` — remaining_amount, status
- [x] `budgets` — period enum, month, year
- [x] `recurring_transactions` — frequency enum, next_due_date, end_date
- [x] `subscriptions` — provider_subscription_id, payment_provider
- [x] `model_has_roles` / `roles` — spatie/permission

### ✅ 1.3 — Enums و Traits الأساسية
- [x] `TransactionType` — income / expense / transfer
- [x] `DebtType` — borrowed / lent
- [x] `DebtStatus` — active / partially_paid / paid
- [x] `SubscriptionPlan` — free / pro / business (مع maxProjects, maxTransactionsPerMonth)
- [x] `ProjectType` — personal / business
- [x] `RecurringFrequency` — daily / weekly / monthly / yearly
- [x] `UserStatus` — active / suspended
- [x] `BelongsToUser` Trait — Global Scope للعزل التلقائي
- [x] `MoneyFormatter` Helper

### ✅ 1.4 — Models مع العلاقات
- [x] `User` — HasRoles, FilamentUser, isActive(), isSuspended(), canAccessPanel()
- [x] `Project` — HasUlids, SoftDeletes, BelongsToUser, netProfit()
- [x] `Category` — HasUlids, BelongsToUser
- [x] `Transaction` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Debt` — HasUlids, SoftDeletes, BelongsToUser
- [x] `Budget` — HasUlids, BelongsToUser, usagePercentage()
- [x] `RecurringTransaction` — HasUlids, BelongsToUser
- [x] `Subscription` — HasUlids, scopes: active()

---

## 🔐 Phase 2 — موديول المصادقة ✅

### ✅ 2.1 — صفحة التسجيل المخصصة
- [x] `StoreRegisterRequest` + `RegisterUserAction` (User + 12 فئة افتراضية)
- [x] تصميم Blade عربي RTL + Tajawal Font

### ✅ 2.2 — تسجيل الدخول وإعادة تعيين كلمة المرور
- [x] Remember Me + Forgot Password
- [x] `EnsureUserIsActive` Middleware — يمنع الموقوفين من الدخول

---

## 🏗️ Phase 3 — Layout وBlade Components ✅

### ✅ 3.1 — App Layout الرئيسي
- [x] Sidebar + Topbar + Flash Messages (RTL)

### ✅ 3.2 — Blade Components المشتركة
- [x] `stats-card` / `badge` / `empty-state` / `modal` / `progress-bar` / `nav-item`

---

## 📁 Phase 4 — موديول المشاريع ✅
- [x] `ProjectData` DTO + Actions (Create/Update/Delete)
- [x] `ProjectFinancialService` + `ProjectPolicy`
- [x] Views: index (بطاقات) + show + create + edit
- [x] فصل شخصي / تجاري (`ProjectType`)

## 💰 Phase 4.5 — الميزانية (Budget) ✅
- [x] `BudgetTrackerService` + Actions + Policy
- [x] Progress bars ملوّنة (أخضر/برتقالي/أحمر)

## 🏷️ Phase 5 — الفئات (Categories) ✅
- [x] CRUD كامل + 12 فئة افتراضية عند التسجيل

## 🔁 Phase 5.5 — الالتزامات المتكررة ✅
- [x] `ProcessRecurringTransactions` Command — Scheduler يومي 01:00
- [x] `RecurringFrequency::nextDate()` لكل أنواع التكرار

## 💸 Phase 6 — المعاملات ✅
- [x] `TransactionService` + `BalanceCalculatorService`
- [x] جدول + فلترة متقدمة + بحث + Pagination + تصدير CSV

## 📊 Phase 7 — لوحة التحكم ✅
- [x] 4 KPIs + Chart.js (6 أشهر) + آخر المعاملات + تنبيهات الديون
- [x] Cache TTL 30 دقيقة

## 💳 Phase 8 — الديون ✅
- [x] `RecordPartialPaymentAction` + `MarkDebtAsPaidAction`
- [x] `SendDebtAlerts` Command — Scheduler يومي 08:00

## 📈 Phase 9 — التقارير ✅
- [x] `MonthlyReportService` / `ProfitLossService` / `CashFlowService`
- [x] `TopCategoriesService` / `ProjectComparisonService`
- [x] فلاتر + Chart.js + تصدير CSV

## 🔔 Phase 10 — الإشعارات ✅
- [x] `DebtDueSoonNotification` + `WeeklyFinancialSummaryNotification`
- [x] Notification Bell + Dropdown + صفحة كاملة

## 💼 Phase 11 — الفوترة (Billing) ✅

> **ملاحظة:** هيكل جاهز بدون مزود دفع — يُضاف لاحقاً عبر `PaymentProviderInterface`

| الميزة | Free | Pro | Business |
|--------|------|-----|---------|
| المشاريع | 2 | 10 | غير محدود |
| المعاملات/شهر | 50 | 500 | غير محدود |
| التصدير | ❌ | ✅ | ✅ |
| API Access | ❌ | ❌ | ✅ |

- [x] `PaymentProviderInterface` + `SubscriptionService`
- [x] `config/billing.php` + `BillingController`
- [x] صفحة أسعار كاملة (Free / Pro / Business)

## ⚙️ Phase 12 — الإعدادات ✅
- [x] 5 تبويبات: الملف الشخصي / الأمان / الإشعارات / الاشتراك / حذف الحساب

## 🔒 Phase 13 — الأمان والجودة ✅
- [x] `BelongsToUser` Global Scope + Policies على جميع الموديولات
- [x] Eager Loading + Indexes مركّبة + Cache
- [x] **54/54 Pest Tests ✅**

---

## 🚀 Phase 14 — الإنتاج (cPanel Shared Hosting) ✅

**الموقع:** `https://workuflow.palgoals.com`

### ✅ ملفات الإعداد
- [x] `.env.production.example` — قالب بيئة الإنتاج الكامل
- [x] `docs/DEPLOY.md` — دليل نشر تفصيلي (10 خطوات)
- [x] `deploy.sh` — سكريبت نشر تلقائي
- [x] `TelescopeServiceProvider` — Telescope محمي بـ `super_admin` role

### ✅ إعدادات الإنتاج
- [x] `APP_ENV=production` / `APP_DEBUG=false`
- [x] `QUEUE_CONNECTION=database` (مناسب لـ Shared Hosting)
- [x] `SESSION_DRIVER=database` / `SESSION_ENCRYPT=true`
- [x] `LOG_CHANNEL=daily` / `LOG_LEVEL=error`
- [x] SSL مُفعَّل (HTTPS)

### ✅ Cron Jobs
```
* * * * * php artisan schedule:run              # Scheduler
*/2 * * * * php artisan queue:work --stop-when-empty --max-time=55 --tries=3  # Queue
```

### ✅ مشاكل تم حلها على السيرفر
- `exec()` مُعطَّل → `ln -s` يدوياً لـ Storage symlink
- Vite build غير موجود → `npm run build` محلياً ورفع `public/build/`
- `SESSION_DOMAIN` → تحديثها إلى `.palgoals.com`

---

## 🛡️ Phase 15 — لوحة الإدارة (Filament v3) ✅

### ✅ 15.1 — البنية الأساسية
- [x] Filament v3 — Panel عند `/admin`
- [x] `AdminSeeder` — role `super_admin` + مستخدم `admin@workuflow.com`
- [x] Navigation Groups: إدارة المستخدمين / البيانات المالية / النظام

### ✅ 15.2 — Resources
- [x] `UserResource` — CRUD + Actions متقدمة (A.2)
- [x] `TransactionResource` — قراءة فقط
- [x] `SubscriptionResource` — إدارة الاشتراكات يدوياً (A.1)

### ✅ 15.3 — Widgets (6 Widgets)
- [x] `StatsOverviewWidget` — إحصاءات عامة (sort: 1)
- [x] `UsersChartWidget` — نمو المستخدمين آخر 12 شهر (sort: 2)
- [x] `RevenueStatsWidget` — MRR / ARR / Churn Rate (sort: 3)
- [x] `RevenueChartWidget` — Donut توزيع الخطط (sort: 4)
- [x] `MrrTrendWidget` — خط نمو الإيرادات آخر 12 شهر (sort: 5)
- [x] `SystemHealthWidget` — Queue / Failed Jobs / Scheduler / Log (sort: 6) — polling 30s

---

## 🌐 Marketing — الصفحة التسويقية ✅

- [x] `welcome.blade.php` — عربي RTL، CSS خالص
- [x] أقسام: Navbar ذكي / Hero / Pain Points / Features / How It Works / Stats / Testimonials / Pricing / CTA / Footer
- [x] Route `/` → `view('welcome')` بدل redirect

---

## 🛡️ Admin+ — تطوير لوحة الإدارة المتقدمة ✅

### ✅ A.1 — SubscriptionResource
- [x] Actions: تفعيل / تمديد (1→12 شهر) / إلغاء
- [x] Filters: بالخطة / الحالة / "تنتهي خلال 7 أيام"
- [x] Tabs: الكل / النشطة / تنتهي قريباً / الملغاة
- [x] `SubscriptionService::extendPlan()` — دالة جديدة

### ✅ A.2 — UserResource Actions
- [x] `UserStatus` Enum (active / suspended)
- [x] Migration: عمود `status` على جدول users
- [x] `EnsureUserIsActive` Middleware — يطرد الموقوفين تلقائياً
- [x] Actions: تعليق / تفعيل / إعادة ضبط الخطة / إرسال بريد / حذف البيانات
- [x] BulkActions: تعليق وتفعيل جماعي
- [x] Tabs: الكل / النشطون / الموقوفون

### ✅ A.3 — Revenue Widgets
- [x] `RevenueStatsWidget` — MRR / ARR / Active Subscriptions / Churn Rate
- [x] `RevenueChartWidget` — Donut Chart (Free/Pro/Business)
- [x] `MrrTrendWidget` — خط نمو الإيرادات آخر 12 شهر

### ✅ A.4 — SystemHealthWidget
- [x] Queue (مهام معلّقة)
- [x] Failed Jobs (آخر 24 ساعة)
- [x] Scheduler (آخر تشغيل)
- [x] Log File (حجم + آخر تعديل)
- [x] Auto-polling كل 30 ثانية

---

## ✨ UX+ — تحسينات تجربة المستخدم 🔄

### ✅ U.1 — بريد الترحيب عند التسجيل
**الحالة:** `completed`

**المنجز:**
- [x] `app/Mail/WelcomeEmail.php` — Mailable مع موضوع عربي
- [x] `app/Jobs/SendWelcomeEmailJob.php` — Queue Job بـ 3 محاولات + logging عند الفشل
- [x] `resources/views/emails/welcome.blade.php` — Template HTML عربي RTL كامل (Header / تحية / CTA / 4 ميزات / روابط سريعة / Footer)
- [x] `RegisterUserAction` — يُرسل البريد بعد 5 ثوانٍ من التسجيل (delay)

---

### ✅ U.2 — تصدير التقارير PDF / Excel
**الحالة:** `completed`

**المنجز:**
- [x] `app/Exports/TransactionsExport.php` — Excel مع RTL + تلوين دخل/مصروف + zebra striping + صف مجموع
- [x] `resources/views/reports/exports/pdf.blade.php` — Template عربي احترافي (Header ملوّن + ملخص KPIs + جدول معاملات)
- [x] `app/Http/Controllers/ReportExportController.php` — PDF بـ mPDF + Excel بـ maatwebsite
- [x] Routes: `reports.export.pdf` / `reports.export.excel`
- [x] أزرار تصدير في صفحة التقارير مع رسالة Upgrade للخطة المجانية
- [x] **مكتبة PDF:** `mpdf/mpdf` (دعم كامل للعربية) بدلاً من dompdf

> ⚠️ **ملاحظة:** استُبدلت `barryvdh/laravel-dompdf` بـ `mpdf/mpdf` لأن dompdf لا يدعم تشكيل الخط العربي.

---

### ✅ U.3 — Onboarding للمستخدم الجديد
**الحالة:** `completed`

**المنجز:**
- [x] Migration: عمود `onboarding_dismissed_at` على جدول users
- [x] `app/Services/OnboardingService.php` — يحسب الخطوات من البيانات الفعلية (لا تخزين إضافي)
- [x] `resources/views/components/onboarding-widget.blade.php` — Component بـ Alpine.js: Progress Bar + بطاقات 2×2 + زر إغلاق فوري
- [x] `app/Http/Controllers/OnboardingController.php` — `dismiss()` يحفظ الوقت ويعود للصفحة
- [x] Route: `POST /onboarding/dismiss`
- [x] `DashboardController` — يحسب بيانات Onboarding ويمررها للـ View
- [x] `dashboard.blade.php` — يعرض الـ Widget في الأعلى للمستخدمين الجدد فقط

**الخطوات الأربع:**
1. إنشاء مشروع → 2. إضافة معاملة → 3. ضبط ميزانية → 4. استعراض التقارير

---

---

## 🆕 Phase 16 — تحسينات مايو 2026

### ✅ 16.1 — إضافة عملة الشيكل الإسرائيلي (ILS)
**الحالة:** `completed`

**المنجز:**
- [x] `app/Support/Helpers/MoneyFormatter.php` — إضافة `'ILS' => '₪ ' . $formatted`
- [x] `app/Http/Controllers/SettingsController.php` — إضافة `'ILS' => 'شيكل إسرائيلي ₪'` للقائمة
- [x] `app/Http/Controllers/ProjectController.php` — إضافة ILS للعملات في create/edit
- [x] `app/Http/Requests/Transactions/StoreTransactionRequest.php` — إضافة ILS لقائمة `in:`
- [x] `app/Http/Requests/Transactions/UpdateTransactionRequest.php` — نفسه
- [x] `app/Http/Requests/Projects/StoreProjectRequest.php` — نفسه
- [x] `app/Http/Requests/Projects/UpdateProjectRequest.php` — نفسه

---

### ✅ 16.2 — موديول العملاء (Clients)
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_15_100001_create_clients_table.php` — (id, user_id, name, phone, email, company, notes, is_active, softDeletes)
- [x] `app/Models/Client.php` — BelongsToUser, SoftDeletes, scopeActive, hasMany projects
- [x] `app/Policies/ClientPolicy.php` — التحقق من الملكية على update/delete
- [x] `app/Http/Controllers/ClientController.php` — CRUD كامل (index, create, store, edit, update, destroy)
- [x] `app/Filament/Resources/ClientResource.php` — لوحة Admin
- [x] Views: `clients/index`, `create`, `edit`, `_form` — تصميم بطاقات مع بيانات العميل
- [x] Sidebar: قسم "الأعمال" مع رابط العملاء (أيقونة users)
- [x] Routes: `Route::resource('clients', ...)` في `routes/web.php`

---

### ✅ 16.3 — موديول الخدمات (Services)
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_15_100002_create_services_table.php` — (id, user_id nullable, name, name_ar, icon, color, is_global, is_active)
- [x] `app/Models/Service.php` — scopeForUser (global + owned), belongsToMany projects
- [x] `app/Filament/Resources/ServiceResource.php` — لوحة Admin (حذف محمي للـ global)
- [x] `database/seeders/ServicesSeeder.php` — 12 خدمة افتراضية عالمية (تصميم هوية، سيو، تسويق رقمي، موشن جرافيك...)
- [x] `app/Http/Controllers/ServiceController.php` — store (مخصص للمستخدم) + destroy
- [x] Routes: `Route::resource('services', ...)->only(['index','store','destroy'])`

**الخدمات الافتراضية الـ 12:**
تصميم هوية البراند، تصميم شعار، استراتيجية البراند، تحسين محركات البحث، تسويق رقمي، موشن جرافيك، إدارة وسائل التواصل، تصميم مواقع، تصميم UI/UX، تصوير، إنتاج فيديو، كتابة محتوى

---

### ✅ 16.4 — ربط الخدمات بالمشاريع (project_service pivot)
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_15_100003_add_client_id_to_projects_table.php` — عمود `client_id` nullable على projects
- [x] Migration: `2026_05_15_100004_create_project_service_table.php` — pivot (project_id string ULID, service_id, client_id nullable, amount, type enum, notes)
- [x] `app/Models/Project.php` — إضافة `client_id` للـ fillable + علاقة `client()` و `services()`
- [x] `app/Modules/Projects/DTOs/ProjectData.php` — إضافة `client_id`
- [x] `app/Modules/Projects/Actions/CreateProjectAction.php` — حفظ client_id
- [x] `app/Modules/Projects/Actions/UpdateProjectAction.php` — تحديث client_id وmync الخدمات
- [x] `app/Http/Controllers/ProjectController.php` — تمرير clients وservices للـ views + sync pivot
- [x] `resources/views/projects/_form.blade.php` — Alpine.js: إضافة/حذف خدمات ديناميكياً مع المبلغ والنوع

> **ملاحظة تقنية:** project_id في جدول pivot من نوع `string` (وليس `foreignId`) لأن المشاريع تستخدم ULID.

---

### ✅ 16.5 — حقل "جهة الدفع" (Payee) على المعاملات
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_16_000001_add_payee_to_transactions_table.php` — عمود `payee` nullable string بعد `description`
- [x] `app/Models/Transaction.php` — إضافة `'payee'` للـ fillable
- [x] `resources/views/transactions/_form.blade.php` — حقل يظهر فقط عند اختيار "مصروف" بـ `x-show="selectedType === 'expense'"` + أيقونة مبنى
- [x] `app/Http/Requests/Transactions/StoreTransactionRequest.php` — `'payee' => ['nullable','string','max:255']`
- [x] `app/Http/Requests/Transactions/UpdateTransactionRequest.php` — نفسه
- [x] `app/Modules/Transactions/DTOs/TransactionData.php` — إضافة `public readonly ?string $payee = null`
- [x] `app/Modules/Transactions/Actions/CreateTransactionAction.php` — تمرير `'payee' => $data->payee`
- [x] `app/Modules/Transactions/Actions/UpdateTransactionAction.php` — نفسه

**الاستخدام:** يظهر الحقل فقط للمصروفات ويمكن تسجيل جهة الدفع (مورد، شركة، فريلانسر...).

---

### ✅ 16.6 — واتساب في بطاقات العملاء والفريق
**الحالة:** `completed`

**المنجز:**
- [x] `resources/views/clients/index.blade.php` — زر واتساب أسفل كل بطاقة عميل (يظهر فقط إذا وُجد رقم الهاتف)
- [x] `resources/views/team/index.blade.php` — زر واتساب في بطاقة كل عضو فريق
- [x] `preg_replace('/[^0-9]/', '', $phone)` لتنظيف الرقم قبل إرساله لـ `wa.me`

---

### ✅ 16.7 — قيمة العقد وميزانية التكاليف للمشاريع
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_16_000002_add_financials_to_projects_table.php` — عمودا `contract_value` و `expense_budget` (decimal 12,2 nullable)
- [x] `app/Models/Project.php` — إضافتهما للـ fillable والـ casts
- [x] `app/Modules/Projects/DTOs/ProjectData.php` — `?float $contract_value` و `?float $expense_budget`
- [x] `app/Modules/Projects/Actions/CreateProjectAction.php` و `UpdateProjectAction.php` — حفظ الحقلين
- [x] `app/Http/Requests/Projects/StoreProjectRequest.php` و `UpdateProjectRequest.php` — validation (nullable, numeric, min:0, max:999999999)
- [x] `app/Modules/Projects/Services/ProjectFinancialService.php` — حساب `contract_collected%`، `budget_used%`، `budget_overrun`، `contract_remaining`، `budget_remaining`
- [x] `resources/views/projects/_form.blade.php` — حقلا الإدخال
- [x] `resources/views/projects/show.blade.php` — بطاقتا Progress Bar (أزرق/أخضر للعقد، برتقالي/أحمر للميزانية)

---

### ✅ 16.8 — إعادة تصميم قسم الخدمات + الإضافة السريعة
**الحالة:** `completed`

**المنجز:**
- [x] تصميم جديد لقسم الخدمات: بطاقات مع Radio toggle للنوع (دخل/مصروف) بدل القائمة المنسدلة القديمة
- [x] حقل عضو الفريق وتكلفته مباشرةً داخل كل بطاقة خدمة
- [x] لوحة "إضافة خدمة جديدة" سريعة: مدخل الاسم + زر حفظ بدون مغادرة الصفحة (Alpine.js + fetch API)
- [x] `app/Http/Controllers/ServiceController.php::quickStore()` — endpoint JSON POST `services/quick` يُنشئ الخدمة ويعيد `{id, name_ar}`
- [x] `routes/web.php` — `Route::post('services/quick', ...)` باسم `services.quick-store`
- [x] التحديث الفوري لقائمة الخدمات في Alpine.js state بعد الإضافة

---

### ✅ 16.9 — Tooltips على المقاييس المالية في صفحة المشروع
**الحالة:** `completed`

**المنجز:**
- [x] `resources/views/components/stats-card.blade.php` — إضافة prop اختياري `$tooltip`
- [x] عند توفير tooltip تظهر دائرة `?` صغيرة مع فقاعة شرح تظهر عند hover (Alpine.js)
- [x] تطبيقه على جميع البطاقات المالية الست: إجمالي الدخل، إجمالي المصروف، صافي الربح، قيمة العقد، ميزانية التكاليف

---

### ✅ 16.10 — موديول الفريق (Team Members)
**الحالة:** `completed`

**المنجز:**
- [x] Migration: `2026_05_17_000001_create_team_members_table.php` — (id ULID, user_id, name, type enum[employee/freelancer], specialty, phone, email, default_rate, notes, is_active, timestamps, softDeletes)
- [x] Migration: `2026_05_17_000002_add_team_to_project_service_table.php` — إضافة `team_member_id`, `team_cost`, `team_cost_paid` لجدول `project_service`
- [x] `app/Models/TeamMember.php` — HasUlids, SoftDeletes, BelongsToUser، `typeLabel()`, `typeBadgeColor()`, `scopeActive()`
- [x] `app/Http/Controllers/TeamMemberController.php` — CRUD كامل (index, create, store, edit, update, destroy)
- [x] Views: `resources/views/team/index.blade.php` — شبكة بطاقات مع شارة النوع، بيانات التواصل، زر واتساب
- [x] Views: `resources/views/team/create.blade.php` و `edit.blade.php` و `_form.blade.php` — نموذج Alpine.js مع Radio للنوع
- [x] `resources/views/layouts/app.blade.php` — رابط "الفريق" في قسم الأعمال بالشريط الجانبي
- [x] `routes/web.php` — `Route::resource('team', TeamMemberController::class)` + `Route::post('projects/{project}/pay-team/{serviceId}', ...)`
- [x] `app/Http/Controllers/ProjectController.php` — تمرير `$teamMembers` لـ create/edit، sync `team_member_id`/`team_cost`/`team_cost_paid` في pivot، دالة `payTeamMember()` تُنشئ Transaction مصروف وتُحدِّث الـ pivot
- [x] `resources/views/projects/_form.blade.php` — قائمة منسدلة لاختيار عضو الفريق وحقل تكلفته داخل كل خدمة
- [x] `resources/views/projects/show.blade.php` — قسم "الفريق المعين على المشروع" مع أسماء الأعضاء والتكاليف وزر "تسجيل دفعة"
- [x] `app/Support/Enums/ProjectType.php` — إصلاح `icon()` من نص Heroicon (`'briefcase'`) إلى Emoji (`'💼'`)

**الآلية:** عند الضغط على "تسجيل دفعة" → يُنشئ تلقائياً معاملة مصروف للمشروع باسم عضو الفريق، ويُسجِّل `team_cost_paid = true` في pivot.

---

### ✅ 16.11 — نظام الشروحات (Help Center)
**الحالة:** `completed`

**المنجز:**
- [x] `app/Http/Controllers/HelpController.php` — Controller بسيط يعرض صفحة المساعدة
- [x] `resources/views/help/index.blade.php` — صفحة شاملة بـ 10 تبويبات (Alpine.js): البداية السريعة، المشاريع، المعاملات، العملاء، الفريق، الديون، الميزانية، الالتزامات الثابتة، التقارير، نصائح وحيل
- [x] `resources/views/components/tooltip.blade.php` — مكوّن Tooltip عام يقبل `text`، `position` (top/bottom/left/right)، `width` — يستخدم Alpine.js hover
- [x] `resources/views/components/onboarding-modal.blade.php` — Modal ترحيب متعدد الخطوات (5 خطوات): يظهر تلقائياً للمستخدم الجديد، يتحقق من `onboarding_dismissed_at`، يُغلق بـ fetch POST + يحفظ الحالة
- [x] `resources/views/components/help-section.blade.php` — مكوّن Blade لعنوان قسم
- [x] `resources/views/components/help-card.blade.php` — بطاقة شرح بعنوان ومحتوى
- [x] `resources/views/components/help-step.blade.php` — خطوة مرقمة بشكل بصري
- [x] `resources/views/components/help-tip.blade.php` — مربع نصيحة باللون الأصفر
- [x] `routes/web.php` — إضافة `Route::get('/help', ...)` باسم `help.index`
- [x] `resources/views/layouts/app.blade.php` — إضافة قسم "الدعم" مع رابط "مركز المساعدة" في الشريط الجانبي، وتضمين `<x-onboarding-modal />` قبل نهاية الصفحة

**آلية الـ Onboarding Modal (النهائية):**
- يظهر تلقائياً لأي مستخدم لم يُسجَّل فيه `onboarding_dismissed_at` وتاريخ إنشاء حسابه أقل من 7 أيام
- 5 خطوات: ترحيب → المشاريع → المعاملات → العملاء/الفريق → جاهز!
- شريط تقدم علوي + نقاط تنقل سفلية
- **آلية الإغلاق الموثوقة (ثنائية):**
  1. `localStorage.setItem('onboarding_dismissed', '1')` — حفظ فوري في المتصفح لمنع ظهور الـ Modal عند التنقل حتى لو لم يصل الطلب للسيرفر
  2. `navigator.sendBeacon()` — يضمن وصول POST لـ `onboarding.dismiss` حتى عند مغادرة الصفحة
  3. `init()` يتحقق من `localStorage` في كل صفحة → يمنع ظهور الـ Modal مباشرةً
- الخطوة الأخيرة تقود مباشرةً لإنشاء المشروع الأول

---

### ✅ 16.12 — إصلاح وميض الـ Onboarding Modal (x-cloak)
**الحالة:** `completed`  
**التاريخ:** مايو 2026

**المشكلة:** الـ Onboarding Modal كان يظهر لوميض سريع عند التنقل بين الصفحات حتى بعد إغلاقه.

**السبب الجذري:** Alpine.js يحتاج CSS rule مخصصة لتفعيل `x-cloak`:
```css
[x-cloak] { display: none !important; }
```
بدون هذه القاعدة، يظهر العنصر في DOM قبل أن يُهيّئ Alpine ويُطبّق `x-show="open"` (مع `open: false`)، فينتج وميض.

**الإصلاح:**
- [x] `resources/views/layouts/app.blade.php` — إضافة `[x-cloak] { display: none !important; }` داخل `<style>` في الـ `<head>` مباشرةً (قبل أي JavaScript)

**النتيجة:** الـ Modal مخفي بـ CSS من اللحظة الأولى — Alpine يُهيّئ في الخلفية — لا وميض على الإطلاق.

---

### ⚠️ إجراءات مطلوبة على السيرفر
```bash
# تشغيل على السيرفر بعد رفع الملفات
php artisan migrate --force
php artisan db:seed --class=ServicesSeeder --force
php artisan optimize:clear && php artisan optimize
```

---

---

## 🧩 Phase 17 — نظام CRM المتقدم (Advanced Client Relationship Management)

> **المرجع الهندسي:** `docs/CLIENTS-CRM-SPEC-V2.md` — وثيقة معمارية من مستوى CTO (2164 سطر)  
> **المرجع التفصيلي:** `docs/CLIENTS-CRM-SPEC.md` — المواصفات الكاملة V1 (1687 سطر)  
> **المنهجية:** Domain-Driven Design — Service + Action + Event Architecture  
> **الجدول الزمني:** 8 Sprints / 38 مهمة — Sprint 1 → Sprint 8 (تسلسل إجباري)

### 🔗 خريطة تبعيات Sprints

```
Sprint 1 (Foundation)
    └─► Sprint 2 (Services)
            ├─► Sprint 3 (API Layer)
            │       ├─► Sprint 7 (Frontend)
            │       └─► Sprint 8 (Portal)
            ├─► Sprint 4 (Import/Export)
            ├─► Sprint 5 (Intelligence)
            └─► Sprint 6 (Automation)
```

> ⚠️ **قاعدة ذهبية:** لا تبدأ Sprint جديدة قبل إكمال Sprint السابقة بالكامل.

---

### 🏗️ Sprint 1 — البنية الأساسية (Foundation) `#90–#97`

> **الهدف:** بناء schema قاعدة البيانات V2 وطبقة البيانات الكاملة — كل ما يعتمد عليه كل شيء آخر.

#### [x] #90 — S1.1: Migrations — مخطط قاعدة البيانات V2
**الأولوية:** 🔴 حرج | **الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026

**✅ المُنجز — 12 migration ملف:**

| الملف | الجدول | الحالة |
|-------|--------|--------|
| `2026_05_24_001000_upgrade_clients_table_crm_v2.php` | clients (ALTER — إضافة 10 أعمدة CRM) | ✅ |
| `2026_05_24_002000_create_client_tags_table.php` | client_tags | ✅ |
| `2026_05_24_003000_create_client_tag_assignments_table.php` | client_tag_assignments | ✅ |
| `2026_05_24_004000_create_client_activities_table.php` | client_activities | ✅ |
| `2026_05_24_005000_create_client_health_scores_table.php` | client_health_scores | ✅ |
| `2026_05_24_006000_create_client_follow_ups_table.php` | client_follow_ups | ✅ |
| `2026_05_24_007000_create_client_field_definitions_table.php` | client_field_definitions | ✅ |
| `2026_05_24_008000_create_client_field_values_table.php` | client_field_values | ✅ |
| `2026_05_24_009000_create_client_attachments_table.php` | client_attachments | ✅ |
| `2026_05_24_010000_create_client_portal_tokens_table.php` | client_portal_tokens | ✅ |
| `2026_05_24_011000_create_saved_segments_table.php` | saved_segments | ✅ |
| `2026_05_24_012000_create_client_import_logs_table.php` | client_import_logs | ✅ |

**قرارات معمارية مُطبَّقة:**
- VARCHAR بدل ENUM في جميع أعمدة الحالة (C-03 Fix — zero-downtime)
- `public_id ULID` مُضاف على clients للروابط الخارجية
- `client_activities` بجدول عادي + indexes مُركَّبة (بدل Partitioning — shared hosting)
- `client_portal_tokens.token` يخزن SHA-256 hash فقط (C-04 Fix)
- `client_import_logs.idempotency_key` UNIQUE لمنع الاستيراد المكرر
- `client_follow_ups` و `client_attachments` و `saved_segments` تستخدم ULID primary key

---

#### [x] #91 — S1.2: Enums — طبقة Type Safety
**الأولوية:** 🔴 حرج | **الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026

**المُنجز — 8 Enums في `app/Modules/CRM/Enums/`:**

| الملف | القيم | الدوال |
|-------|-------|--------|
| `ClientStatus` | active, inactive, prospect, archived | label, color, badgeClass, isVisible, canTransitionTo |
| `ActivityType` | 14 نوع نشاط | label, icon, color, isHighPriority |
| `ClientSource` | direct, referral, social_media, website, import, other | label, icon |
| `FollowUpStatus` | pending, completed, overdue, cancelled | label, badgeColor, badgeClass, icon, isTerminal, resolveActual |
| `PortalPermission` | view_invoices, download_invoices, make_payments, view_files | label, description, defaults |
| `ImportStatus` | pending, processing, completed, failed, partial | label, progressColor, badgeClass, icon, isTerminal |
| `TagType` | system, custom | label, isDeletable, isEditable |
| `HealthScoreGrade` | excellent, good, fair, poor | label, color, badgeClass, icon, fromScore, minScore |

---

#### [x] #92 — S1.3: Models — طبقة البيانات + Casts + Scopes
**الأولوية:** 🔴 حرج | **الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026

**المُنجز — 12 Model:**

| الملف | الموقع | المميزات |
|-------|--------|---------|
| `Client` | `app/Models/Client.php` (محدَّث) | 247 lines، 12 relation، 7 scopes، 4 accessors، backward compatible |
| `ClientTag` | `app/Modules/CRM/Models/` | scopeForUser (system+custom)، isDeletable guard |
| `ClientTagAssignment` | `app/Modules/CRM/Models/` | Pivot Model، assigned_at auto-fill |
| `ClientActivity` | `app/Modules/CRM/Models/` | timestamps=false، scopeHighPriority، scopeRecent |
| `ClientHealthScore` | `app/Modules/CRM/Models/` | grade()، factorBreakdown() |
| `ClientFollowUp` | `app/Modules/CRM/Models/` | HasUlids، actualStatus()، daysUntilDue()، 4 scopes |
| `ClientFieldDefinition` | `app/Modules/CRM/Models/` | isAvailableForPlan()، scopeOrdered |
| `ClientFieldValue` | `app/Modules/CRM/Models/` | castValue() بحسب نوع الحقل |
| `ClientAttachment` | `app/Modules/CRM/Models/` | HasUlids، url()، humanSize()، isImage()، isPdf() |
| `ClientPortalToken` | `app/Modules/CRM/Models/` | hidden=['token']، findByPlaintext()، hasPermission() |
| `SavedSegment` | `app/Modules/CRM/Models/` | HasUlids، refreshCount()، buildQuery() |
| `ClientImportLog` | `app/Modules/CRM/Models/` | HasUlids، successRate()، summary() |

**قرارات معمارية:**
- `Client` يبقى في `app/Models/` للتوافق مع الكود القديم — يستورد CRM Enums والـ Models الجديدة
- `ClientPortalToken.token` مخفي في JSON (`$hidden`) — لا يُرسل أبداً للواجهة
- `ClientActivity.timestamps = false` — يستخدم `occurred_at` فقط لتوفير المساحة
- `ClientFollowUp.actualStatus()` يكشف المتأخرة ديناميكياً بدون تحديث DB

---

#### [x] #93 — S1.4: System Tags Seeder — الوسوم الأساسية ✅
**الأولوية:** 🔴 حرج | **التقدير:** 1 ساعة | **مكتمل:** مايو 2026

**8 وسوم نظام ثابتة (غير قابلة للحذف):**

| الوسم | اللون | الأيقونة | الأولوية |
|-------|-------|---------|---------|
| VIP | `#10B981` (أخضر زمردي) | ⭐ | 1 |
| Late Payer | `#EF4444` (أحمر) | ⚠️ | 2 |
| Hesitant | `#F59E0B` (أصفر ذهبي) | 🤔 | 3 |
| New Client | `#3B82F6` (أزرق) | 🆕 | 4 |
| Inactive | `#6B7280` (رمادي) | 💤 | 5 |
| High Value | `#8B5CF6` (بنفسجي) | 💎 | 6 |
| Referred | `#EC4899` (وردي) | 🤝 | 7 |
| Pending Review | `#F97316` (برتقالي) | 🔍 | 8 |

- [x] `database/seeders/SystemClientTagsSeeder.php` — `type = TagType::System`, `user_id = null` (global)
- [x] تسجيل في `DatabaseSeeder` — Idempotent عبر `updateOrCreate` على أساس `slug`

**الملفات المنشأة:**
- `database/seeders/SystemClientTagsSeeder.php` — يقرأ من `config('crm.system_tags')`، updateOrCreate لكل وسم، يطبع إحصائيات التشغيل
- `database/seeders/DatabaseSeeder.php` — تمت إضافة `SystemClientTagsSeeder::class`

---

#### [x] #94 — S1.5: ClientPolicy — التحكم في الصلاحيات ✅
**الأولوية:** 🔴 حرج | **التقدير:** 1 ساعة | **مكتمل:** مايو 2026

- [x] `viewAny` — المستخدم المسجل دخوله
- [x] `view` — المالك فقط (`$client->user_id === $user->id`)
- [x] `create` — فحص حد الخطة (Free: 10 | Pro: 500 | Business: ∞)
- [x] `update` / `delete` / `restore` / `forceDelete` / `archive` — المالك فقط
- [x] `managePortal` — Business فقط (`can_portal`)
- [x] `importClients` / `exportClients` — Pro+ (`can_import` / `can_export`)
- [x] `manageCustomFields` — Pro+ (`max_custom_fields > 0`)
- [x] `viewAnalytics` — Pro+ (`can_health_score || can_segments`)

**الملفات المنشأة في `app/Modules/CRM/Policies/`:**
- `ClientPolicy.php` — 12 gate بالكامل مع helper `planLimits()` من config/crm.php
- `ClientTagPolicy.php` — يحمي وسوم النظام من الحذف/التعديل
- `ClientFollowUpPolicy.php` — owner isolation
- `ClientImportLogPolicy.php` — Pro+ فقط
- `SavedSegmentPolicy.php` — Pro+ فقط (`can_segments`)
- `ClientPortalTokenPolicy.php` — Business فقط (`can_portal`)

**إصلاح جانبي:** `CRMServiceProvider` — تصحيح namespace من `App\Modules\CRM\Models\Client` إلى `App\Models\Client`

---

#### [x] #95 — S1.6: Form Requests — Validation Layer ✅
**الأولوية:** 🔴 حرج | **التقدير:** 2 ساعات | **مكتمل:** مايو 2026

**الملفات المنشأة في `app/Modules/CRM/Requests/`:**

| الملف | الوظيفة الرئيسية |
|-------|----------------|
| `StoreClientRequest` | name/email unique per user / tag_ids / authorize via create policy |
| `UpdateClientRequest` | same + `ignore($clientId)` في unique + `sometimes` |
| `StoreTagRequest` | name max:50 / color HEX regex / slug alpha_dash unique per user |
| `BulkTagRequest` | client_ids (max:500) / tag_ids / action: assign|remove |
| `StoreFollowUpRequest` | client_id exists per user / due_at after:now / reminder_at before:due_at |
| `ImportClientRequest` | file mimes:xlsx,csv / max KB من config / column_map array |
| `StorePortalTokenRequest` | permissions من PortalPermission::values() / expires_at before:+1year / prepareForValidation يضع defaults |

---

#### [x] #96 — S1.7: DTOs — Data Transfer Objects ✅
**الأولوية:** 🔴 حرج | **التقدير:** 2 ساعات | **مكتمل:** مايو 2026

**الملفات المنشأة في `app/Modules/CRM/DTOs/` — كلها `final readonly class`:**

| الملف | Factory Methods | مميزات |
|-------|----------------|--------|
| `CreateClientDTO` | `fromRequest()` + `fromImportRow()` | `toArray()` جاهز لـ Model::create() |
| `UpdateClientDTO` | `fromRequest(request, client)` | `toChangedArray()` فقط الحقول المتغيرة + `isEmpty()` |
| `ClientFiltersDTO` | `fromRequest(Request)` | `hasFilters()` — perPage max:100 — sort whitelist |
| `CreateTagDTO` | `fromRequest()` | slug auto-generated من الاسم + `toArray()` |
| `CreateFollowUpDTO` | `fromRequest()` | Carbon parsing لـ due_at/reminder_at + `toArray()` |
| `BulkTagDTO` | `fromRequest()` | `isAssign()` / `isRemove()` / `clientCount()` / `tagCount()` |
| `ImportClientsDTO` | `fromRequest()` | يحفظ الملف في disk local مؤقتاً + idempotency_key hash |

---

#### [x] #97 — S1.8: ClientQueryBuilder — محرك البحث والفلترة ✅
**الأولوية:** 🔴 حرج | **التقدير:** 3 ساعات | **مكتمل:** مايو 2026

**الملف:** `app/Modules/CRM/Builders/ClientQueryBuilder.php` — 255 سطر، 14 method

| الـ Method | الوصف |
|-----------|------|
| `__construct(int $userId)` | scope guard ثابت: `user_id` دائماً مُطبَّق |
| `applyFilters(ClientFiltersDTO)` | يُطبّق جميع الفلاتر دفعةً واحدة |
| `search(string $term)` | LIKE على name + email + company + phone (لا FULLTEXT) |
| `byTags(array $tagIds)` | AND — العميل يمتلك جميع الوسوم |
| `byTagsAny(array $tagIds)` | OR — يكفي وسم واحد |
| `byHealthGrade(HealthScoreGrade)` | whereBetween min/max للدرجة |
| `withFollowUpsDue()` | متابعات مستحقة أو متأخرة |
| `withRelations(array)` | eager loading (افتراضي: tags + latestHealthScore) |
| `withPendingFollowUpsCount()` | withCount بدون N+1 |
| `cursorPaginate(int)` | **C-05** — cursor بدل offset |
| `get()` / `count()` | تنفيذ مباشر |
| `toExportQuery()` | Builder خام للتصدير |
| `getQuery()` | للاستخدام في SavedSegment |

**القرارات المعمارية:**
- NULLS LAST للحقول nullable (health_score, last_contact_at, total_revenue)
- ترتيب ثانوي ثابت على `id` لضمان حتمية cursor
- `addcslashes()` للحماية من LIKE injection

---

### ⚙️ Sprint 2 — طبقة الخدمات (Services Layer) `#98–#104`

> **المتطلبات:** إكمال Sprint 1 بالكامل | **التقدير الإجمالي:** 12 ساعة

#### [ ] #98 — S2.1: ClientService — منطق الأعمال الأساسي
**التقدير:** 3 ساعات

```php
// app/Modules/CRM/Services/ClientService.php
class ClientService
{
    public function create(CreateClientDTO $dto): Client  // + fire ClientCreated event
    public function update(Client $client, UpdateClientDTO $dto): Client  // + fire ClientUpdated
    public function archive(Client $client): void  // soft archive (is_archived = true)
    public function restore(Client $client): void
    public function delete(Client $client): void  // SoftDelete
    public function updateStatus(Client $client, ClientStatus $status): void
    public function recalculateAggregates(Client $client): void  // fallback يدوي
}
```

- [ ] **C-02 Fix:** تحديث aggregates بـ atomic increment لا بـ subquery:
  `Client::where('id', $id)->update(['total_paid' => DB::raw("total_paid + {$amount}")])`
- [ ] كل عملية تُطلق Event مناسب (لا Observer مباشر)

---

#### [ ] #99 — S2.2: TagService — إدارة الوسوم
**التقدير:** 2 ساعة

- [ ] `create(CreateTagDTO $dto, int $userId): ClientTag`
- [ ] `assign(Client $client, array $tagIds, int $assignedBy): void` — bulk assign مع check تجاوز الحد
- [ ] `remove(Client $client, array $tagIds): void` — حماية system tags من الحذف
- [ ] `bulkAssign(BulkTagDTO $dto): array` — تقرير نتائج (success/failed per client)
- [ ] `suggestTags(Client $client): array` — يستدعي `SmartTagSuggestionService` (Sprint 5)
- [ ] حد الوسوم حسب الخطة: Free=3, Pro=10, Business=غير محدود

---

#### [ ] #100 — S2.3: FollowUpService — تتبع المتابعات
**التقدير:** 2 ساعة

- [ ] `schedule(CreateFollowUpDTO $dto): ClientFollowUp` + log activity
- [ ] `complete(ClientFollowUp $followUp, ?string $notes): void` + log activity
- [ ] `cancel(ClientFollowUp $followUp): void`
- [ ] `getDueFollowUps(int $userId, Carbon $date = null): Collection` — للـ Command اليومي
- [ ] `sendReminders(): int` — يُرسل إشعارات للمتابعات المستحقة + returns count

---

#### [ ] #101 — S2.4: ClientHealthScoreService — مؤشر صحة العميل
**التقدير:** 3 ساعات

**خوارزمية 5 عوامل موزونة (من V2):**

| العامل | الوزن | الحساب |
|--------|-------|--------|
| معدل الدفع | 35% | paid_on_time / total_invoices |
| تكرار العمل | 25% | invoice_count خلال 12 شهر |
| قيمة الإيراد | 20% | total_revenue مقارنةً بمتوسط المستخدم |
| انتظام التواصل | 10% | آخر تواصل ≤ 30 يوم |
| معدل الاستجابة | 10% | سرعة قبول العروض |

- [ ] **Recency Bias:** آخر 3 أشهر × 70% + السنة الكاملة × 30%
- [ ] تخزين النتيجة في `client_health_scores` مع `factors JSON` لشرح السبب
- [ ] `calculate(Client $client): HealthScoreSnapshot`
- [ ] `recalculateForUser(int $userId): void` — للـ Command الليلي

---

#### [ ] #102 — S2.5: Events + Listeners — نظام الأحداث
**التقدير:** 2 ساعة

**Events (في `app/Modules/CRM/Events/`):**
- [ ] `ClientCreated(Client $client)`
- [ ] `ClientUpdated(Client $client, array $changes)`
- [ ] `ClientStatusChanged(Client $client, ClientStatus $old, ClientStatus $new)`
- [ ] `ClientTagAssigned(Client $client, ClientTag $tag, int $assignedBy)`
- [ ] `ClientTagRemoved(Client $client, ClientTag $tag)`
- [ ] `FollowUpScheduled(ClientFollowUp $followUp)`
- [ ] `FollowUpCompleted(ClientFollowUp $followUp)`
- [ ] `ClientImportCompleted(ClientImportLog $log)`

**Listeners (مع `$afterCommit = true` — C-01 Fix من V2):**
```php
// app/Modules/CRM/Listeners/LogClientActivityListener.php
class LogClientActivityListener implements ShouldQueue
{
    public bool $afterCommit = true;  // ← حرج: لتجنب logging داخل transaction
    public string $queue = 'crm-default';
    
    public function handle(ClientCreated $event): void
    {
        LogClientActivityAction::run($event->client, ActivityType::StatusChanged, [
            'description' => 'تم إنشاء العميل',
        ]);
    }
}
```
- [ ] تسجيل كل Events في `EventServiceProvider`

---

#### [ ] #103 — S2.6: LogClientActivityAction — مسجّل النشاط
**التقدير:** 1 ساعة

- [ ] `run(Client $client, ActivityType $type, array $metadata, ?int $actorId = null): ClientActivity`
- [ ] حفظ في `client_activities` مع `occurred_at = now()`
- [ ] **لا تستدعِ مباشرةً من Observers** — استدعِ فقط من Listeners مع `$afterCommit = true`
- [ ] يدعم metadata مفتوحة (JSON) للمرونة

---

### 🔌 Sprint 3 — طبقة API والـ Controllers `#104–#110`

> **المتطلبات:** إكمال Sprint 2 | **التقدير الإجمالي:** 10 ساعات

#### [ ] #104 — S3.1: ClientController — المتحكم الأساسي
**التقدير:** 3 ساعات

- [ ] `index(ClientFiltersRequest $request)` — يستخدم `ClientQueryBuilder` + cursor pagination
- [ ] `show(Client $client)` — `ClientProfileResource` + eager load relations
- [ ] `store(StoreClientRequest $request)` — `CreateClientDTO::fromRequest()` → `ClientService::create()`
- [ ] `update(UpdateClientRequest $request, Client $client)` — `UpdateClientDTO` → `ClientService::update()`
- [ ] `destroy(Client $client)` — `ClientService::delete()`
- [ ] `archive(Client $client)` — `ClientService::archive()`
- [ ] `restore(Client $client)` — `ClientService::restore()`
- [ ] `timeline(Client $client)` — activity log مع cursor pagination
- [ ] `stats(Client $client)` — aggregates + health score + last activities

---

#### [ ] #105 — S3.2: TagController — إدارة الوسوم
**التقدير:** 2 ساعة

- [ ] `index()` — كل وسوم المستخدم + system tags مع عدد العملاء لكل وسم
- [ ] `store(StoreTagRequest)` — `TagService::create()`
- [ ] `update(StoreTagRequest, ClientTag)` — حماية system tags
- [ ] `destroy(ClientTag)` — حذف مع تحذير إذا كان مستخدماً
- [ ] `assign(BulkTagRequest)` — `TagService::bulkAssign()`
- [ ] `suggestions(Client)` — `TagService::suggestTags()`

---

#### [ ] #106 — S3.3: SegmentController — الشرائح المحفوظة
**التقدير:** 2 ساعة

- [ ] `index()` — كل Segments مع client_count
- [ ] `store(StoreSegmentRequest)` — حفظ filters JSON
- [ ] `preview(Request)` — تشغيل الفلاتر وعرض النتائج بدون حفظ
- [ ] `execute(SavedSegment)` — تنفيذ فعلي + تحديث `client_count + last_executed_at`
- [ ] `destroy(SavedSegment)`
- [ ] `pin(SavedSegment)` — تثبيت في الشريط الجانبي

---

#### [ ] #107 — S3.4: API Resources — طبقة التحويل
**التقدير:** 1 ساعة

- [ ] `ClientListResource` — البيانات المختصرة للقائمة (name, email, company, health_score, tags[], last_contact_at, status badge)
- [ ] `ClientProfileResource` — البيانات الكاملة (كل الحقول + relations + aggregates + latest activities)
- [ ] `ClientActivityResource` — النشاط مع icon + description + actor + occurred_at
- [ ] `ClientTagResource` — name, color, icon, type, client_count
- [ ] `FollowUpResource` — title, due_at, status_badge, days_until_due
- [ ] `HealthScoreResource` — score, grade, color, factors[], scored_at

---

#### [ ] #108 — S3.5: FollowUpController — متحكم المتابعات
**التقدير:** 1 ساعة

- [ ] `index(Request)` — قائمة بالفلاتر (client_id, status, due_date_range)
- [ ] `store(StoreFollowUpRequest)` → `FollowUpService::schedule()`
- [ ] `complete(ClientFollowUp, Request)` → `FollowUpService::complete()`
- [ ] `cancel(ClientFollowUp)` → `FollowUpService::cancel()`
- [ ] `upcoming(Request)` — المتابعات خلال 7 أيام (للـ Dashboard widget)

---

#### [ ] #109 — S3.6: CustomFieldController — الحقول المخصصة
**التقدير:** 1 ساعة

- [ ] `index()` — تعريفات الحقول للمستخدم (مرتبة بـ display_order)
- [ ] `store(StoreFieldDefinitionRequest)` — check Business plan
- [ ] `update(StoreFieldDefinitionRequest, ClientFieldDefinition)`
- [ ] `destroy(ClientFieldDefinition)` — حذف + حذف القيم المرتبطة
- [ ] `reorder(Request)` — تحديث display_order بـ PATCH
- [ ] `saveValue(Client, ClientFieldDefinition, Request)` — upsert على `client_field_values`

---

### 📤 Sprint 4 — الاستيراد والتصدير (Import/Export) `#110–#114`

> **المتطلبات:** إكمال Sprint 2 | **الحزمة:** `maatwebsite/excel`

#### [x] #110 — S4.1: ClientImport — استيراد Excel/CSV
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 4 ساعات

- [ ] `app/Modules/CRM/Imports/ClientsImport.php` — implements `ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading`
- [ ] Heading map: `['الاسم' => 'name', 'البريد' => 'email', 'الهاتف' => 'phone', 'الشركة' => 'company', 'المصدر' => 'source']`
- [ ] `batchSize()` = 500 rows | `chunkSize()` = 1000 rows
- [ ] `rules()` — validation على كل صف + تجميع الأخطاء
- [ ] `upsert` mode: إذا `update_existing = true` → `updateOrCreate(['email' => ...], [...])`
- [ ] **Idempotency:** `X-Idempotency-Key` header → مخزن في `client_import_logs.idempotency_key` — إذا موجود مسبقاً → return cached result
- [ ] `ImportClientsJob` — Queue Job يشغّل الاستيراد + يُحدِّث log + يُطلق `ClientImportCompleted`
- [ ] قالب Excel للتنزيل مع headers عربية + صف مثال

---

#### [x] #111 — S4.2: ClientExport — تصدير Excel/CSV
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 2 ساعة

- [ ] `app/Modules/CRM/Exports/ClientsExport.php` — implements `FromQuery, WithHeadings, WithMapping, WithStyles, ShouldQueue`
- [ ] `fromQuery()` — يستخدم `ClientQueryBuilder::toExportQuery()` (تطبيق نفس الفلاتر الحالية)
- [ ] `headings()` — عربية: الاسم / البريد الإلكتروني / الهاتف / الشركة / الحالة / مؤشر الصحة / إجمالي الإيراد / الوسوم / آخر تواصل
- [ ] RTL styling + header row ملوّن + عمود الإيراد بتنسيق عملة
- [ ] تصدير CSV كـ fallback خفيف للخطة المجانية (بدون styling)

---

#### [x] #112 — S4.3: ImportController — متحكم الاستيراد
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 1 ساعة

- [ ] `template()` — تنزيل قالب Excel
- [ ] `store(ImportClientRequest)` — رفع الملف + إنشاء `ClientImportLog` + dispatch `ImportClientsJob`
- [ ] `show(ClientImportLog)` — حالة الاستيراد الحالية (polling-friendly JSON)
- [ ] `history()` — آخر 20 عملية استيراد

---

#### [x] #113 — S4.4: ExportController — متحكم التصدير
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 1 ساعة

- [ ] `download(Request)` — يطبق نفس فلاتر ClientQueryBuilder على التصدير
- [ ] `scheduleExport(Request)` — تصدير async مع Queue (للملفات الكبيرة) + notification عند الانتهاء

---

### 🧠 Sprint 5 — الذكاء والتحليلات (Intelligence) `#114–#118`

> **المتطلبات:** إكمال Sprint 2 | **المستوى:** AI L1 (Rule-based) + Statistical

#### [x] #114 — S5.1: SmartTagSuggestionService — اقتراح الوسوم الذكي
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 3 ساعات

**L1 — قواعد محددة مسبقاً (Rule-based):**

```php
// app/Modules/CRM/Services/SmartTagSuggestionService.php
class SmartTagSuggestionService
{
    private const RULES = [
        'VIP'         => ['min_revenue' => 5000, 'min_invoices' => 5],
        'Late Payer'  => ['overdue_rate' => 0.3],  // 30%+ فواتير متأخرة
        'Hesitant'    => ['acceptance_rate_max' => 0.4],  // رفض 60%+ العروض
        'Inactive'    => ['days_since_contact' => 90],
        'High Value'  => ['min_revenue' => 10000],
        'New Client'  => ['days_since_created_max' => 30],
    ];
    
    public function suggest(Client $client): array  // [{tag, confidence, reason}]
    public function suggestBulk(Collection $clients): array
    public function applyAutoRules(int $userId): int  // returns applied count
}
```

- [ ] كل اقتراح يُعيد: `{tag_slug, confidence: 0.0-1.0, reason: string}`
- [ ] `applyAutoRules()` — Command ليلي يطبق الوسوم عالية الثقة (≥0.85) تلقائياً

---

#### [x] #115 — S5.2: RecalculateHealthScoresCommand — أمر ليلي
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 1 ساعة

- [ ] `php artisan crm:recalculate-health-scores {--user=}` — يعيد حساب لكل العملاء أو مستخدم محدد
- [ ] Chunk processing (200 client/batch) لتجنب memory overflow
- [ ] تسجيل في Scheduler: `$schedule->command('crm:recalculate-health-scores')->dailyAt('02:00')`
- [ ] Logging: عدد العملاء المعالجين + متوسط الوقت

---

#### [x] #116 — S5.3: ClientSegmentEngine — محرك الشرائح الديناميكية
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 2 ساعة

- [ ] `evaluate(SavedSegment $segment): Builder` — تحويل filters JSON إلى Query Builder
- [ ] دعم operators: `equals, not_equals, contains, greater_than, less_than, between, in, not_in, is_empty, is_not_empty`
- [ ] دعم fields: `status, health_score, total_revenue, last_contact_at, created_at, tag_ids, source, has_overdue_followup`
- [ ] `RefreshSegmentCountsCommand` — `php artisan crm:refresh-segments` — تشغيل يومي

---

#### [x] #117 — S5.4: AggregatesReconciliationCommand — مطابقة ليلية
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 2 ساعة

- [ ] `php artisan crm:reconcile-aggregates {--user=} {--date=}` — إعادة حساب `total_revenue, total_paid, invoice_count` من المصدر
- [ ] يُقارن القيمة الحالية بالمحسوبة من الفواتير → يُصحح أي تباين
- [ ] يُسجّل كل تصحيح في log مع القيمة القديمة والجديدة
- [ ] يُشغَّل يومياً `03:00` لضمان دقة aggregates بعد الـ atomic increments

---

### 🤖 Sprint 6 — الأتمتة (Automation Engine) `#118–#121`

> **المتطلبات:** إكمال Sprint 2 و Sprint 5

#### [x] #118 — S6.1: AutomationRuleEngine — محرك القواعد ✅
**التقدير:** 4 ساعات

```php
// app/Modules/CRM/Services/AutomationRuleEngine.php
class AutomationRuleEngine
{
    public function evaluate(Client $client, string $trigger, array $context = []): int
    public function evaluateForAllClients(int $userId, string $trigger): int
    // Dispatches ExecuteAutomationAction::dispatch(...)->onQueue('automations')
}
```

- [x] **Triggers:** client_created, status_changed, tag_assigned, health_score_below, follow_up_overdue, days_since_contact, invoice_paid, invoice_overdue
- [x] **Conditions:** field comparisons مع AND/OR logic (nested)
- [x] **Actions:** تُنفَّذ async عبر Jobs — `ExecuteAutomationAction` على queue 'automations'
- [x] **Migration:** `automation_rules` (user_id, name, trigger, conditions json, actions json, is_active, priority, run_count, last_run_at, softDeletes)
- [x] **Model:** `AutomationRule` — SoftDeletes، JSON casts، `recordRun()`، scopes: active/forTrigger/forUser

---

#### [x] #119 — S6.2: AutomationActions — تنفيذ الإجراءات ✅
**التقدير:** 2 ساعة

- [x] `AssignTagAutomationAction` — `TagService::assign()`، params: `{tag_slug}`
- [x] `CreateFollowUpAutomationAction` — `FollowUpService::create()` مع dedup check، params: `{message, days_from_now, type}`
- [x] `SendNotificationAutomationAction` — `$user->notify(AutomationNotification)`، params: `{message, icon}`
- [x] `UpdateStatusAutomationAction` — `ClientService::update(UpdateClientDTO)`، params: `{status}` مع `canTransitionTo()` guard
- [x] `LogNoteAutomationAction` — `LogClientActivityAction::execute()` مع `ActivityType::NoteAdded`، params: `{note}`
- [x] `BaseAutomationAction` — abstract، factory `make(string $type)`، `canExecute()` guard
- [x] `AutomationNotification` — Laravel DB channel، toDatabase: message/client_id/client_name/type/icon
- [x] `ExecuteAutomationAction` Job — `tries=3`، `timeout=60`، `uniqueId()` للـ deduplication

---

#### [x] #120 — S6.3: AutomationConditionEvaluator — تقييم الشروط ✅
**التقدير:** 2 ساعة

- [x] `evaluate(Client $client, mixed $conditions): bool`
- [x] دعم nested AND/OR: `{operator: 'AND', conditions: [...]}`
- [x] Field resolvers: health_score، tag_ids، overdue_follow_ups، payment_rate، date fields
- [x] Per-cycle cache (`$this->cache`) — يُعاد ضبطه لكل `evaluate()` جديد

---

### 🎨 Sprint 7 — واجهة المستخدم (Frontend) `#121–#126`

> **المتطلبات:** إكمال Sprint 3 | **Stack:** Blade + Alpine.js + Tailwind CSS

#### [ ] #121 — S7.1: Client List View — صفحة قائمة العملاء
**التقدير:** 4 ساعات

**التصميم:**
- [ ] Header: عنوان + إحصاءات سريعة (إجمالي / نشط / VIP / تستحق متابعة)
- [ ] شريط أدوات: بحث فوري (debounced 300ms) + فلاتر منسدلة (الحالة / الوسوم / مؤشر الصحة)
- [ ] عرض مزدوج: Grid (بطاقات) + List (جدول) — toggle محفوظ في localStorage
- [ ] كل بطاقة: avatar (أحرف الاسم ملوّنة) + اسم + شركة + وسوم + health score badge + آخر تواصل + أزرار سريعة (واتساب / متابعة / تعديل)
- [ ] Infinite scroll بـ `IntersectionObserver` + cursor pagination
- [ ] Bulk select: checkbox + toolbar (تعيين وسم / تغيير حالة / تصدير / حذف)
- [ ] Empty state ذكي: مختلف لـ (لا عملاء / لا نتائج بحث / فلتر فارغ)

---

#### [ ] #122 — S7.2: Client Profile View — صفحة ملف العميل
**التقدير:** 4 ساعات

**الأقسام:**
- [ ] Header: اسم + شركة + حالة + health score gauge + أزرار (تعديل / أرشفة / بوابة / إضافة وسم)
- [ ] KPIs: إجمالي الإيراد / إجمالي المدفوع / عدد الفواتير / آخر دفعة
- [ ] تبويبات Alpine.js:
  - **المعلومات:** بيانات التواصل + الحقول المخصصة + المصدر + ملاحظات
  - **النشاط:** timeline عمودي (icon + description + actor + timestamp) مع cursor pagination
  - **المتابعات:** قائمة مع فلاتر (معلقة / مكتملة / متأخرة) + إضافة سريعة
  - **الوسوم:** الوسوم الحالية + اقتراحات ذكية + إضافة/حذف
  - **الحقول المخصصة:** نموذج محرر inline
- [ ] Health Score card مفصّلة مع breakdown العوامل الخمسة

---

#### [ ] #123 — S7.3: Tag Management — إدارة الوسوم
**التقدير:** 2 ساعة

- [ ] صفحة `/clients/tags` — شبكة وسوم مع color picker + icon picker
- [ ] معاينة فورية للوسم (كيف سيبدو على البطاقة)
- [ ] مؤشر عدد العملاء لكل وسم + رابط سريع للعملاء المرتبطين
- [ ] حماية system tags: تعطيل حذف + تعطيل تغيير اللون (اللون محدد من النظام)
- [ ] Drag-and-drop لترتيب الوسوم (display_order) بـ Alpine.js + Sortable.js

---

#### [ ] #124 — S7.4: Follow-ups Dashboard — لوحة المتابعات
**التقدير:** 3 ساعات

- [ ] 3 أعمدة: اليوم / هذا الأسبوع / متأخرة (مرتبة بالأولوية)
- [ ] كل بطاقة: اسم العميل (رابط) + عنوان المتابعة + أيام المتبقية + أزرار (إكمال / تأجيل / إلغاء)
- [ ] Widget في الـ Dashboard الرئيسي: أعداد مختصرة (X اليوم / Y هذا الأسبوع / Z متأخرة)
- [ ] إضافة متابعة سريعة: نموذج modal بـ Alpine.js بدون مغادرة الصفحة
- [ ] فلتر بالعميل أو التاريخ أو الأولوية

---

#### [ ] #125 — S7.5: Segments UI + Health Score Panel
**التقدير:** 3 ساعات

**Segments UI:**
- [ ] Segment Builder — واجهة بصرية drag-and-drop لبناء الفلاتر
- [ ] Preview real-time (عدد العملاء المطابقين قبل الحفظ)
- [ ] الشرائح المثبتة في الشريط الجانبي تحت قسم العملاء

**Health Score Panel:**
- [ ] توزيع العملاء على 4 فئات (Excellent/Good/Fair/Poor) — Bar chart أو Donut
- [ ] قائمة أسوأ 10 عملاء (Poor) مع سبب انخفاض الدرجة
- [ ] قائمة أفضل 10 عملاء (VIP candidates) للترقية التلقائية

---

### 🚪 Sprint 8 — بوابة العميل (Client Portal) `#126–#128`

> **المتطلبات:** إكمال Sprint 3 | **الأمان:** حرج — راجع C-04 في V2

#### [x] #126 — S8.1: Portal Authentication — المصادقة بالرمز
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 4 ساعات

**C-04 Fix — حماية Brute Force (من V2):**
```php
// app/Modules/CRM/Controllers/ClientPortalController.php
public function authenticate(Request $request): RedirectResponse
{
    $hash = hash('sha256', $request->input('token'));
    $token = ClientPortalToken::where('token', $hash)
        ->where('expires_at', '>', now())
        ->first();
    
    if (!$token) {
        usleep(random_int(50000, 150000));  // ← artificial delay لمنع timing attacks
        RateLimiter::hit("portal:{$request->ip()}", 60 * 60);  // rate limit بـ IP
        return back()->withErrors(['token' => 'الرمز غير صحيح أو منتهي الصلاحية']);
    }
    
    // تسجيل دخول + تحديث last_used_at
    session(['client_portal_token' => $token->id]);
    return redirect()->route('portal.dashboard');
}
```

- [x] صفحة طلب الرابط (`/portal/access`) — المستخدم يُدخل بريده أو رقمه
- [x] المستخدم (صاحب الحساب) يُنشئ رمزاً من ملف العميل ويُرسله
- [x] Middleware `EnsurePortalAuthenticated` — يتحقق من الجلسة + صلاحية الرمز
- [x] Rate limiting: max 5 محاولات/ساعة بـ IP + `RateLimiter` facade
- [x] **خزّن hash فقط — لا تخزن الـ token plaintext أبداً** (C-04 fix)
- [x] الرمز يُعرض مرة واحدة عند الإنشاء ثم يختفي (مثل SSH key)

---

#### [x] #127 — S8.2: Portal Dashboard — لوحة العميل
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 3 ساعات

- [x] Layout منفصل `portal.blade.php` — تصميم بسيط باسم الشركة وشعارها (White-label)
- [x] Dashboard: ملخص المعاملات + إجمالي المستحق + آخر فاتورة
- [x] صفحة الفواتير: قائمة مع الحالة + نسبة السداد
- [x] صفحة الملف الشخصي: بيانات العميل للعرض فقط
- [x] Permission gates: كل قسم محمي بـ `hasPermission(PortalPermission)` check
- [x] عرض اسم العمل (Business name من إعدادات المستخدم) في Header

---

#### [x] #128 — S8.3: Portal Token Management — إدارة رموز البوابة
**الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026
**التقدير:** 2 ساعة

- [x] UI في ملف العميل: قائمة الرموز النشطة + إنشاء رمز جديد + إلغاء رمز
- [x] عند الإنشاء: اختيار الصلاحيات (checkboxes) + مدة الصلاحية
- [x] عرض الرمز مرة واحدة بعد الإنشاء (plaintext — يختفي بعد الإغلاق)
- [x] نسخ الرمز/الرابط للحافظة بزر واحد
- [x] عرض تاريخ آخر استخدام + IP الأخير (للمراجعة الأمنية)

---

### ✅ Sprint 0 — الإعداد والتهيئة `#89`

#### [x] #89 — S0: تهيئة Module CRM + Config
**الأولوية:** 🔴 يُنجز أولاً | **الحالة:** ✅ مكتمل | **التاريخ:** مايو 2026

**المُنجز:**
- [x] إنشاء هيكل المجلدات (16 مجلد):
  ```
  app/Modules/CRM/
  ├── Actions/      ├── Builders/     ├── Controllers/
  ├── DTOs/         ├── Enums/        ├── Events/
  ├── Exports/      ├── Imports/      ├── Jobs/
  ├── Listeners/    ├── Models/       ├── Policies/
  ├── Requests/     ├── Resources/    └── Services/
  ```
- [x] `config/crm.php` — 171 سطر: حدود الخطط الثلاث + system tags + health score weights + import config + portal security + cache TTLs + queue names
- [x] `app/Providers/CRMServiceProvider.php` — تسجيل Policies + Routes (crm + portal)
- [x] `routes/crm.php` — 115 سطر: كل مسارات CRM (CRUD + Tags + FollowUps + Segments + Import + Export + CustomFields + PortalTokens)
- [x] `routes/portal.php` — بوابة العميل المستقلة مع `portal.auth` middleware
- [x] `app/Http/Middleware/EnsurePortalAuthenticated.php` — C-04 Fix: hash-based validation + rate limiting + `last_used_at` lazy update
- [x] `bootstrap/providers.php` — تسجيل `CRMServiceProvider`
- [x] `bootstrap/app.php` — إضافة `portal.auth` alias للـ middleware

**ملاحظة تقنية:** استُخدم key-based cache invalidation بدلاً من Redis Cache Tags لضمان التوافق مع shared hosting (database/file cache driver).

---

## 📋 جدول التتبع السريع

| # | المهمة | الحالة |
|---|--------|--------|
| 1.1–1.4 | الأساس | ✅ |
| 2.1–2.2 | المصادقة | ✅ |
| 3.1–3.2 | Layout + Components | ✅ |
| 4–4.5 | المشاريع + الميزانية | ✅ |
| 5–5.5 | الفئات + المتكررة | ✅ |
| 6 | المعاملات | ✅ |
| 7 | Dashboard | ✅ |
| 8 | الديون | ✅ |
| 9 | التقارير | ✅ |
| 10 | الإشعارات | ✅ |
| 11 | الفوترة (هيكل) | ✅ |
| 12 | الإعدادات | ✅ |
| 13 | الأمان + 54 Tests | ✅ |
| 14 | الإنتاج — cPanel | ✅ |
| 15 | Filament Admin | ✅ |
| M.1 | Landing Page | ✅ |
| A.1 | SubscriptionResource | ✅ |
| A.2 | UserResource Actions | ✅ |
| A.3 | Revenue Widgets | ✅ |
| A.4 | SystemHealth Widget | ✅ |
| U.1 | بريد الترحيب | ⬜ |
| U.2 | تصدير PDF/Excel | ⬜ |
| U.3 | Onboarding | ✅ |
| 16.1 | عملة ILS | ✅ |
| 16.2 | موديول العملاء | ✅ |
| 16.3 | موديول الخدمات | ✅ |
| 16.4 | ربط الخدمات بالمشاريع | ✅ |
| 16.5 | حقل جهة الدفع (Payee) | ✅ |
| 16.6 | واتساب في بطاقات العملاء/الفريق | ✅ |
| 16.7 | قيمة العقد + ميزانية التكاليف | ✅ |
| 16.8 | إعادة تصميم الخدمات + إضافة سريعة | ✅ |
| 16.9 | Tooltips على المقاييس المالية | ✅ |
| 16.10 | موديول الفريق (Team Members) | ✅ |
| 16.11 | نظام الشروحات (Help Center) | ✅ |
| 16.12 | إصلاح وميض Onboarding Modal (x-cloak) | ✅ |
| — | ربط مزود الدفع | ⬜ مستقبلي |
| — | REST API | ⬜ مستقبلي |
| **Phase 17 — CRM Module** | | |
| #89 | S0: تهيئة Module + Config | ✅ |
| #90 | S1.1: Migrations V2 Schema (12 جدول) | ✅ |
| #91 | S1.2: Enums (8 Enums) | ✅ |
| #92 | S1.3: Models + Casts + Scopes (12 Model) | ✅ |
| #93 | S1.4: System Tags Seeder (8 وسوم) | ✅ |
| #94 | S1.5: ClientPolicy (12 صلاحية) | ✅ |
| #95 | S1.6: Form Requests (7 Requests) | ✅ |
| #96 | S1.7: DTOs (7 DTOs) | ✅ |
| #97 | S1.8: ClientQueryBuilder | ✅ |
| #98 | S2.1: Client CRUD Actions (4 Actions) | ✅ |
| #99 | S2.2: Tag Actions (Assign/Remove/Bulk) | ✅ |
| #100 | S2.3: LogClientActivityAction | ✅ |
| #101 | S2.4: Events (6) + Listeners (3) afterCommit | ✅ |
| #102 | S2.5: ClientService + CRMServiceProvider Events | ✅ |
| #103 | S2.6: FollowUpService + ClientTagService | ✅ |
| #104 | S3.1: ClientController (11 methods) + ClientTagController (7 methods) | ✅ |
| #105 | S3.2: TagController — موجود في ClientTagController | ✅ |
| #106 | S3.3: SegmentController + SavedSegmentService | ✅ |
| #107 | S3.4: API Resources (6 Resources) | ⬜ |
| #108 | S3.5: FollowUpController (5 methods) | ✅ |
| #109 | S3.6: CustomFieldController + ClientCustomFieldService | ✅ |
| #110 | S4.1: ClientsImport (ToCollection + WithChunkReading + Idempotency + upsert) | ✅ |
| #111 | S4.2: ClientsExport (Excel RTL + styled + CSV fallback) | ✅ |
| #112 | S4.3: ImportController (template + store + dispatch Job + history + show) | ✅ |
| #113 | S4.4: ExportController (CSV + xlsx + scheduleExport stub) | ✅ |
| #114 | S5.1: ClientHealthScoreService (Recency Bias) + SmartTagSuggestionService (6 rules) | ✅ |
| #115 | S5.2: RecalculateHealthScoresCommand (--apply-tags, --user, --chunk) | ✅ |
| #116 | S5.3: ClientSegmentEngine (11 operators, 12 fields) + RefreshSegmentCountsCommand | ✅ |
| #117 | S5.4: AggregatesReconciliationCommand (--dry-run, chunk processing) | ✅ |
| #118 | S6.1: AutomationRuleEngine + Migration + AutomationRule Model | ✅ |
| #119 | S6.2: 5 AutomationActions + BaseAutomationAction + AutomationNotification + ExecuteAutomationAction Job | ✅ |
| #120 | S6.3: AutomationConditionEvaluator (nested AND/OR, per-cycle cache) | ✅ |
| #121 | S7.1: Client List View — index + create + edit + show (4 views) | ✅ |
| #122 | S7.2: Client Profile View — 3 tabs (activity + followups + info) | ✅ |
| #123 | S7.3: Tag Management UI (صفحة كاملة + Sortable.js) | ✅ |
| #124 | S7.4: Follow-ups Dashboard (3 أعمدة + Modal إضافة سريعة) | ✅ |
| #125 | S7.5: Segments UI + Health Score Panel | ✅ |
| #126 | S8.1: Portal Auth + Token Security — ClientPortalController + Middleware + C-04 Fix | ✅ |
| #127 | S8.2: Portal Dashboard + Views (layout, auth, access, dashboard, invoices, profile) | ✅ |
| #128 | S8.3: Portal Token Management UI — crm/portal-tokens/index.blade.php | ✅ |

---

## 🔜 المهام المتبقية والأولويات

### 🔴 Phase 17 CRM — التسلسل التنفيذي

```
الأسبوع 1: #89 (S0) → #90-#97 (Sprint 1 Foundation)
الأسبوع 2: #98-#103 (Sprint 2 Services)
الأسبوع 3: #104-#109 (Sprint 3 API)
الأسبوع 4: #110-#113 (Sprint 4 Import/Export) + #114-#117 (Sprint 5 Intelligence)
الأسبوع 5: #118-#120 (Sprint 6 Automation)
الأسبوع 6: #121-#125 (Sprint 7 Frontend)
الأسبوع 7: #126-#128 (Sprint 8 Portal)
```

### 🟠 تحسينات مؤجلة (Phase 16)

| المهمة | الأولوية | الوصف |
|--------|----------|-------|
| عرض Payee في صفحة المعاملات | 🟠 مفيد | إظهار جهة الدفع في قائمة/تفاصيل المعاملة |
| Payee في تصدير PDF | 🟡 لاحقاً | إضافة عمود جهة الدفع في تقرير PDF للمصروفات |
| ربط مزود الدفع | 🟡 عند الجاهزية | تنفيذ `PaymentProviderInterface` |
| REST API | 🟡 لاحقاً | Sanctum + API Resources |

---

## 📚 المراجع التقنية

| الملف | الوصف |
|-------|-------|
| `docs/CLIENTS-CRM-SPEC-V2.md` | المرجع الهندسي الأساسي للـ CRM — معمارية من مستوى CTO (2164 سطر) |
| `docs/CLIENTS-CRM-SPEC.md` | المواصفات التفصيلية V1 — الـ What والـ Why (1687 سطر) |
| `docs/TASKS.md` | هذا الملف — خطة التنفيذ والتتبع |
| `docs/DEPLOY.md` | دليل النشر على cPanel Shared Hosting |

### ⚠️ نقاط حرجة يجب مراعاتها (من V2 Architecture Review)

| الكود | الخطأ | الحل |
|-------|-------|------|
| C-01 | Activity logging داخل Transaction | `$afterCommit = true` على جميع Listeners |
| C-02 | Race condition في aggregates | `DB::raw("total_paid + X")` لا subquery |
| C-03 | ENUM columns تسبب downtime | `VARCHAR + CHECK constraint` |
| C-04 | Portal token مكشوف | خزّن `hash('sha256', $token)` فقط |
| C-05 | Offset pagination بطيء | `cursorPaginate()` لقوائم العملاء |
| C-06 | Cache بدون Tags | `Cache::tags(["client:{$id}"])` مع Redis |

---

---

### ✅ #145 — Navigation + فحص نهائي للـ Views + إكمال DTOs/Requests
**الحالة:** `completed` | **التاريخ:** مايو 2026

**المُنجز:**

**1. UpdateClientRequest — إضافة 5 حقول جديدة:**
- [x] `position` — string, nullable, max:100
- [x] `website` — url, nullable, max:255
- [x] `address` — string, nullable, max:255
- [x] `city` — string, nullable, max:100
- [x] `country` — string, nullable, max:2
- الملف: `app/Modules/CRM/Requests/UpdateClientRequest.php`

**2. UpdateClientDTO — إضافة 5 خصائص جديدة:**
- [x] إضافة: `?string $position`, `?string $website`, `?string $address`, `?string $city`, `?string $country`
- [x] تحديث `fromRequest()` لمعالجة الحقول الجديدة مع `$request->has()` + `$request->filled()` pattern
- [x] تحديث `toChangedArray()` لتضمين الحقول الجديدة إذا تغيّرت
- الملف: `app/Modules/CRM/DTOs/UpdateClientDTO.php`

**3. Navigation — إضافة روابط CRM وباقي الأقسام:**
- [x] `resources/views/layouts/navigation.blade.php`
- [x] روابط Desktop: لوحة التحكم | **العملاء** (clients.index) | المشاريع | المعاملات | التقارير
- [x] روابط Mobile (responsive): نفس الروابط بـ `<x-responsive-nav-link>`
- [x] Active state صحيح: `request()->routeIs('clients.*')` للـ CRM

**4. تحقق من Actions (CreateClientAction + UpdateClientAction):**
- [x] `CreateClientAction` يستخدم `$dto->toArray()` — يمرر الحقول الجديدة تلقائياً ✅
- [x] `UpdateClientAction` يستخدم `$dto->toChangedArray()` — يُحدِّث فقط ما تغيّر ✅

---

---

### ✅ #146 — S7.3: Tag Management UI
**الحالة:** `completed` | **التاريخ:** مايو 2026

**المُنجز:**

**1. `ClientTagController` — تحديثات:**
- [x] `index()` → يعيد Blade view عند طلب عادي، JSON عند AJAX (`wantsJson()`)
- [x] `withCount('clients')` لعرض عدد العملاء لكل وسم
- [x] إضافة `reorder()` method — PATCH بـ `order: [ids]` — يُحدِّث `priority` ويُصفّر الـ Cache

**2. `routes/crm.php`:**
- [x] إضافة `Route::patch('/reorder', ...)` باسم `clients.tags.reorder`

**3. `resources/views/crm/tags/index.blade.php`** (صفحة كاملة):
- [x] **نموذج إنشاء وسم** — `<input type="color">` + اسم + أيقونة Emoji + معاينة فورية في الوقت الحقيقي
- [x] **قائمة الوسوم المخصصة** — قابلة للتعديل inline + حذف مع تأكيد Modal
- [x] **Sortable.js** — Drag-and-drop بـ `.drag-handle` + حفظ تلقائي للترتيب via PATCH
- [x] **وسوم النظام** — عرض للقراءة فقط (لا تعديل/حذف)
- [x] **Toast notifications** — نجاح/خطأ لكل عملية
- [x] **Alpine.js components**: `tagManager()` (page-level) + `tagRow()` (per-row)
- [x] Optimistic UI: التعديلات تظهر فوراً قبل رد السيرفر

---

---

### ✅ #147 — S7.4: Follow-ups Dashboard
**الحالة:** `completed` | **التاريخ:** مايو 2026

**المُنجز:**

**1. `ClientFollowUpController` — تحديثات:**
- [x] `index()` → Blade view (3 queries مفصولة: overdue / today / thisWeek) أو JSON عند AJAX
- [x] `storeGeneral()` — `POST /clients/follow-ups/quick` — إنشاء متابعة من اللوحة العامة مع `client_id` في الـ body

**2. `routes/crm.php`:**
- [x] إضافة `Route::post('/quick', ...)` باسم `clients.follow-ups.quick-store`

**3. `resources/views/crm/follow-ups/index.blade.php`:**
- [x] شريط إحصاءات علوي: 3 بطاقات (متأخرة / اليوم / الأسبوع) بألوان مختلفة
- [x] Layout ثلاثي الأعمدة (responsive: 1 عمود → 3 أعمدة)
- [x] Empty state ذكي لكل عمود (رسائل مختلفة)
- [x] Modal إضافة سريعة (Alpine.js) بدون reload — AJAX إلى `follow-ups.quick-store`
- [x] Toast notifications

**4. `resources/views/components/crm-follow-up-card.blade.php`:**
- [x] شريط لوني جانبي حسب العمود (أحمر / برتقالي / أزرق)
- [x] أيقونة النوع (📞📧🤝✅📌) + أولوية ملونة + حساب الأيام المتبقية/المنقضية
- [x] زر "إتمام" + زر "إلغاء" (كلاهما AJAX مباشر)
- [x] زر "تأجيل" يفتح Modal الإضافة السريعة
- [x] حالة `done` بعد الإتمام/الإلغاء (Optimistic UI)

**5. `navigation.blade.php`:**
- [x] إضافة رابط "المتابعات" (Desktop + Mobile)

---

*وثيقة حية — آخر تحديث: مايو 2026 | Phase 17 CRM مضافة*
