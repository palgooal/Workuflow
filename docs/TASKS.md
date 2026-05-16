# ✅ خطة المهام الكاملة — Workuflow SaaS Financial Platform

> وثيقة تتبع المهام — Laravel 12 / PHP 8.2  
> آخر تحديث: مايو 2026

---

## 📊 ملخص المشروع

| البيان | القيمة |
|--------|--------|
| إجمالي المراحل | 15 مرحلة + Marketing + Admin+ + UX |
| الحالة الحالية | ✅ على الهواء — workuflow.palgoals.com |
| اختبارات Pest | 54/54 ✅ |
| PHP | 8.2 / Laravel 12 |

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

### ⚠️ إجراءات مطلوبة على السيرفر
```bash
# تشغيل على السيرفر بعد رفع الملفات
php artisan migrate --force
php artisan db:seed --class=ServicesSeeder --force
php artisan optimize:clear && php artisan optimize
```

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
| — | ربط مزود الدفع | ⬜ مستقبلي |
| — | REST API | ⬜ مستقبلي |

---

## 🔜 المهام المتبقية

| المهمة | الأولوية | الوصف |
|--------|----------|-------|
| عرض Payee في صفحة المعاملات | 🟠 مفيد | إظهار جهة الدفع في قائمة/تفاصيل المعاملة |
| Payee في تصدير PDF | 🟡 لاحقاً | إضافة عمود جهة الدفع في تقرير PDF للمصروفات |
| ربط مزود الدفع | 🟡 عند الجاهزية | تنفيذ `PaymentProviderInterface` |
| REST API | 🟡 لاحقاً | Sanctum + API Resources |

---

*وثيقة حية — آخر تحديث: مايو 2026*
