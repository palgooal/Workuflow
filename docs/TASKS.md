# ✅ خطة المهام الكاملة — SaaS Financial Clarity Platform

> وثيقة تتبع المهام — Laravel 12 / PHP 8.3+  
> آخر تحديث: مايو 2026

---

## 📊 ملخص المشروع

| البيان | القيمة |
|--------|--------|
| إجمالي المراحل | 14 مرحلة |
| إجمالي المهام | 52 مهمة |
| الحالة الحالية | 🟡 قيد التخطيط |
| الأولوية الأولى | Phase 1 — إعداد المشروع |

---

## 🗺️ خريطة المراحل

```
Phase 1  → الأساس (DB + Enums + Models)
Phase 2  → المصادقة
Phase 3  → Layout + Components
Phase 4  → المشاريع
Phase 5  → الفئات
Phase 6  → المعاملات ⭐ (المحرك الأساسي)
Phase 7  → لوحة التحكم
Phase 8  → الديون
Phase 9  → التقارير
Phase 10 → الإشعارات
Phase 11 → الاشتراكات
Phase 12 → الإعدادات
Phase 13 → الأمان والجودة
Phase 14 → الإنتاج والـ API
```

---

## 🔐 Phase 1 — إعداد المشروع وقاعدة البيانات

> الهدف: بناء الأساس التقني الكامل قبل أي موديول.

---

### ✅ المهمة 1.1 — تثبيت Laravel 12 وإعداد البيئة

**الحالة:** `pending`

**المطلوب:**
- [ ] تثبيت Laravel 12 + PHP 8.3
- [ ] إعداد `.env` (DB, Mail, Queue, Cache)
- [ ] تثبيت Tailwind CSS + Alpine.js
- [ ] تثبيت Laravel Breeze (Blade)
- [ ] إعداد ULID للـ Models
- [ ] تثبيت `spatie/laravel-permission`
- [ ] تثبيت `laravel/telescope` (dev only)

**الحزم المطلوبة:**
```bash
composer require laravel/breeze spatie/laravel-permission
composer require --dev laravel/telescope
npm install -D tailwindcss alpinejs
```

---

### ✅ المهمة 1.2 — إنشاء جميع Migrations

**الحالة:** `pending`

**الجداول المطلوبة:**
- [ ] `users` — إضافة: currency, timezone, subscription_plan
- [ ] `projects` — مع color, currency, is_active
- [ ] `categories` — مع type enum, icon, color
- [ ] `transactions` — مع indexes مركّبة على (user_id, transaction_date)
- [ ] `debts` — مع remaining_amount, status
- [ ] `subscriptions` — مع provider data
- [ ] `notifications` — Laravel built-in

**قواعد:**
- Soft Deletes على: projects, transactions, debts
- ULID كـ Primary Key لجميع الجداول
- Indexes: `(user_id, transaction_date)`, `(project_id, type)`

---

### ✅ المهمة 1.3 — إنشاء Enums و Traits الأساسية

**الحالة:** `pending`

**Enums:**
- [ ] `TransactionType` — income / expense / transfer
- [ ] `DebtType` — borrowed / lent
- [ ] `DebtStatus` — active / partially_paid / paid
- [ ] `SubscriptionPlan` — free / pro / business

**Traits:**
- [ ] `BelongsToUser` — Global Scope للعزل التلقائي
- [ ] `HasUlid` — ULID primary key

**Helpers:**
- [ ] `MoneyFormatter` — تنسيق الأرقام بالعملة

---

### ✅ المهمة 1.4 — إنشاء Models مع العلاقات

**الحالة:** `pending`

| Model | العلاقات |
|-------|---------|
| `User` | hasMany: projects, transactions, debts, categories |
| `Project` | belongsTo: user \| hasMany: transactions, debts |
| `Category` | belongsTo: user \| hasMany: transactions |
| `Transaction` | belongsTo: user, project, category |
| `Debt` | belongsTo: user, project |
| `Subscription` | belongsTo: user |

**المطلوب في كل Model:**
- [ ] Typed properties
- [ ] Casts للـ Enums
- [ ] تطبيق الـ Traits
- [ ] `$fillable` محدد بدقة

---

## 🔐 Phase 2 — موديول المصادقة (Authentication)

> الهدف: نظام مصادقة SaaS احترافي آمن وقابل للتوسع.

---

### ✅ المهمة 2.1 — صفحة التسجيل المخصصة

**الحالة:** `pending`

**المطلوب:**
- [ ] نموذج التسجيل: name, email, password, currency, timezone
- [ ] `StoreRegisterRequest` مع validation كامل
- [ ] `RegisterUserAction` — إنشاء User + Categories افتراضية تلقائياً
- [ ] إرسال بريد التحقق (Queue)
- [ ] تصميم Blade احترافي بـ Tailwind

---

### ✅ المهمة 2.2 — تسجيل الدخول وإعادة تعيين كلمة المرور

**الحالة:** `pending`

**المطلوب:**
- [ ] صفحة تسجيل دخول (Remember Me)
- [ ] صفحة "نسيت كلمة المرور" + بريد إعادة التعيين
- [ ] تأمين المسارات: `auth` + `verified` middleware
- [ ] Rate limiting: 5 محاولات / دقيقة
- [ ] تصميم موحّد مع صفحة التسجيل

---

## 🏗️ Phase 3 — Layout وBlade Components الأساسية

> الهدف: هيكل واجهة متكامل قبل بناء أي موديول.

---

### ✅ المهمة 3.1 — بناء App Layout الرئيسي

**الحالة:** `pending`

**المطلوب:**
- [ ] `layouts/app.blade.php` — Sidebar + Topbar + Content
- [ ] Sidebar: روابط التنقل، اسم المستخدم، العملة الحالية
- [ ] Topbar: اسم الصفحة، زر الإشعارات، قائمة المستخدم
- [ ] Mobile-first responsive (hamburger menu)
- [ ] بنية جاهزة للـ Dark Mode
- [ ] Smooth transitions بـ Alpine.js

---

### ✅ المهمة 3.2 — بناء Blade Components المشتركة

**الحالة:** `pending`

| Component | الوصف |
|-----------|-------|
| `stats-card` | بطاقة إحصائية: عنوان، قيمة، أيقونة، نسبة تغيير |
| `transaction-row` | صف معاملة في الجداول |
| `project-card` | بطاقة مشروع مع رصيده |
| `alert` | رسائل نجاح/خطأ/تحذير |
| `empty-state` | حالة عدم وجود بيانات |
| `modal` | نافذة منبثقة بـ Alpine.js |
| `badge` | شارة ملوّنة للحالة والنوع |
| `dropdown` | قائمة منسدلة مرنة |

---

## 📁 Phase 4 — موديول المشاريع (Projects)

> الهدف: عزل مالي كامل لكل مشروع مع واجهة بصرية واضحة.

---

### ✅ المهمة 4.1 — Actions وServices للمشاريع

**الحالة:** `pending`

**المطلوب:**
- [ ] `ProjectData` DTO
- [ ] `CreateProjectAction`
- [ ] `UpdateProjectAction`
- [ ] `DeleteProjectAction` (التحقق من خلو المشروع من البيانات)
- [ ] `ProjectFinancialService` — حساب الرصيد، الدخل، المصروفات
- [ ] `ProjectPolicy` — view, create, update, delete
- [ ] `StoreProjectRequest` + `UpdateProjectRequest`

---

### ✅ المهمة 4.2 — Controller وViews للمشاريع

**الحالة:** `pending`

**المطلوب:**
- [ ] `ProjectController` — CRUD كامل
- [ ] `projects/index.blade.php` — شبكة بطاقات ملوّنة
- [ ] `projects/show.blade.php` — تفاصيل + معاملات المشروع
- [ ] `projects/create.blade.php` + `edit.blade.php`
- [ ] اختيار اللون والعملة لكل مشروع
- [ ] فلترة: نشط / مؤرشف

---

## 🏷️ Phase 4.3 — فصل المصروف الشخصي عن التجاري

> الهدف: حل مشكلة "خلط المصروف الشخصي مع التجاري" بفصل واضح على مستوى المشاريع.

---

### ✅ المهمة 4.3.1 — تمييز المشاريع والمعاملات (شخصي / تجاري)

**الحالة:** `pending`

**المطلوب:**
- [ ] Migration: إضافة `type` enum (personal / business) لجدول `projects`
- [ ] تحديث `ProjectData` DTO و `StoreProjectRequest`
- [ ] تحديث واجهة إنشاء المشروع: اختيار نوع المشروع
- [ ] لوحة التحكم: فلتر (الكل / تجاري فقط / شخصي فقط)
- [ ] التقارير: فصل واضح بين الأرباح الشخصية والتجارية
- [ ] `ProjectFinancialService`: دعم الفلترة بالنوع

---

## 🏷️ Phase 5 — موديول الفئات (Categories)

> الهدف: فئات مرنة مع فئات افتراضية ذكية عند إنشاء الحساب.

---

### ✅ المهمة 5.1 — Actions وSeeder للفئات

**الحالة:** `pending`

**المطلوب:**
- [ ] `CategoryData` DTO
- [ ] `CreateCategoryAction` / `UpdateCategoryAction` / `DeleteCategoryAction`
- [ ] `DefaultCategoriesSeeder`:
  - دخل: راتب، مبيعات، مشاريع، استثمارات، هدايا
  - مصروفات: إيجار، مواصلات، طعام، اشتراكات، تسويق
- [ ] `CategoryPolicy`
- [ ] `StoreCategoryRequest` + `UpdateCategoryRequest`

---

### ✅ المهمة 5.2 — Controller وViews للفئات

**الحالة:** `pending`

**المطلوب:**
- [ ] `CategoryController` — CRUD كامل
- [ ] `categories/index.blade.php` — قائمة مقسّمة: دخل / مصروف
- [ ] إنشاء/تعديل inline بـ Alpine.js (بدون صفحة منفصلة)
- [ ] عرض الأيقونة واللون لكل فئة

---

## 💰 Phase 4.5 — موديول الميزانية (Budget)

> الهدف: حل مشكلة "عدم وجود ميزانية واضحة" — سقف مصروفات ذكي مع تنبيهات تلقائية.

---

### ✅ المهمة 4.5.1 — جدول الميزانية وActions

**الحالة:** `pending`

**Migration — جدول `budgets`:**

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK | المالك |
| project_id | FK nullable | المشروع |
| category_id | FK nullable | الفئة |
| amount | decimal(15,2) | الحد الأقصى للميزانية |
| period | enum | monthly / yearly |
| month | tinyint nullable | الشهر (1-12) |
| year | smallint | السنة |

**المطلوب:**
- [ ] `BudgetData` DTO
- [ ] `CreateBudgetAction` / `UpdateBudgetAction` / `DeleteBudgetAction`
- [ ] `BudgetTrackerService`:
  - نسبة الاستهلاك: (مصروف فعلي ÷ الميزانية × 100)
  - تحديد الفئات التي تجاوزت 80%
  - تحديد الفئات التي تجاوزت 100%
- [ ] `BudgetPolicy`
- [ ] `StoreBudgetRequest`

---

### ✅ المهمة 4.5.2 — واجهة الميزانية

**الحالة:** `pending`

**المطلوب:**
- [ ] `budget/index.blade.php`:
  - شريط تقدم لكل فئة: 🟢 أقل 80% | 🟠 80-100% | 🔴 تجاوز
  - المبلغ المنفق / الحد المسموح / المتبقي
- [ ] إضافة وتعديل ميزانية inline بـ Alpine.js
- [ ] Widget ملخص الميزانية في لوحة التحكم
- [ ] تنبيه ذكي عند تجاوز 80% من أي ميزانية

---

## 🔁 Phase 5.5 — الالتزامات الشهرية الثابتة (Recurring)

> الهدف: حل مشكلة "نسيان الالتزامات والفواتير" — تسجيل تلقائي للمدفوعات المتكررة.

---

### ✅ المهمة 5.5.1 — جدول Recurring وActions

**الحالة:** `pending`

**Migration — جدول `recurring_transactions`:**

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK | المالك |
| project_id | FK nullable | المشروع |
| category_id | FK nullable | الفئة |
| type | enum | income / expense |
| amount | decimal(15,2) | المبلغ |
| description | string | الوصف |
| frequency | enum | daily / weekly / monthly / yearly |
| start_date | date | تاريخ البداية |
| next_due_date | date | تاريخ الاستحقاق القادم |
| end_date | date nullable | تاريخ الانتهاء |
| is_active | boolean | نشط / متوقف |

**المطلوب:**
- [ ] `RecurringTransactionData` DTO
- [ ] `CreateRecurringAction` / `UpdateRecurringAction` / `ToggleRecurringAction`
- [ ] `ProcessRecurringTransactionsCommand`:
  - يعمل يومياً عبر Scheduler
  - ينشئ Transaction فعلية عند حلول الموعد
  - يُحدّث `next_due_date` تلقائياً
- [ ] `RecurringPolicy`
- [ ] `StoreRecurringRequest`

---

### ✅ المهمة 5.5.2 — واجهة الالتزامات المتكررة

**الحالة:** `pending`

**المطلوب:**
- [ ] `recurring/index.blade.php`:
  - قائمة الالتزامات: الاسم، المبلغ، التكرار، الاستحقاق القادم
  - Badge: نشط 🟢 / متوقف ⚪
  - زر تفعيل/إيقاف مباشر
- [ ] `recurring/create.blade.php`:
  - اختيار التكرار (يومي / أسبوعي / شهري / سنوي)
  - تاريخ البداية والانتهاء
- [ ] Widget "الالتزامات القادمة هذا الشهر" في Dashboard
- [ ] تنبيه قبل 3 أيام من كل استحقاق

---

## 💸 Phase 6 — موديول المعاملات (Transactions) ⭐

> الهدف: المحرك الأساسي للمنصة — دقيق، سريع، قابل للتوسع.

---

### ✅ المهمة 6.1 — Actions وServices للمعاملات

**الحالة:** `pending`

**المطلوب:**
- [ ] `TransactionData` DTO
- [ ] `CreateTransactionAction`
- [ ] `UpdateTransactionAction`
- [ ] `DeleteTransactionAction`
- [ ] `TransactionService` — تجميع، فلترة، بحث
- [ ] `BalanceCalculatorService` — حساب الأرصدة
- [ ] `TransactionPolicy`
- [ ] `StoreTransactionRequest` + `UpdateTransactionRequest`

---

### ✅ المهمة 6.2 — Controller وViews للمعاملات

**الحالة:** `pending`

**المطلوب:**
- [ ] `TransactionController` — CRUD كامل
- [ ] `transactions/index.blade.php`:
  - جدول مع فلترة متقدمة (نوع، تاريخ، مشروع، فئة)
  - بحث سريع
  - Pagination
- [ ] `transactions/create.blade.php`:
  - نموذج ذكي: نوع المعاملة يغير الفئات المتاحة ديناميكياً
- [ ] `transactions/edit.blade.php`
- [ ] تصدير CSV

---

## 📊 Phase 7 — لوحة التحكم (Dashboard)

> الهدف: واجهة Stripe-inspired تعطي صورة مالية فورية وواضحة.

---

### ✅ المهمة 7.1 — DashboardService وبيانات KPIs

**الحالة:** `pending`

**البيانات المطلوبة:**
- [ ] إجمالي الدخل (الشهر الحالي vs السابق + نسبة التغيير)
- [ ] إجمالي المصروفات
- [ ] صافي الربح/الخسارة
- [ ] عدد المشاريع النشطة
- [ ] الديون المستحقة خلال 7 أيام
- [ ] آخر 5 معاملات
- [ ] بيانات الرسم البياني (6 أشهر)
- [ ] Cache بـ TTL 30 دقيقة

---

### ✅ المهمة 7.2 — واجهة Dashboard الاحترافية

**الحالة:** `pending`

**المطلوب:**
- [ ] 4 بطاقات KPI رئيسية
- [ ] رسم بياني: التدفق النقدي (Chart.js)
- [ ] جدول آخر المعاملات
- [ ] قائمة المشاريع النشطة مع الرصيد
- [ ] تنبيهات الديون القريبة
- [ ] Responsive كامل للجوال

---

## 💳 Phase 8 — موديول الديون والالتزامات (Debts)

> الهدف: تتبع دقيق للديون مع سجل مدفوعات جزئية.

---

### ✅ المهمة 8.1 — Actions وServices للديون

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtData` DTO
- [ ] `CreateDebtAction`
- [ ] `RecordPartialPaymentAction` — تحديث `remaining_amount`
- [ ] `MarkDebtAsPaidAction`
- [ ] `DebtTrackerService` — إجمالي، المستحقة قريباً، حسب النوع
- [ ] `DebtPolicy`
- [ ] `StoreDebtRequest` + `UpdateDebtRequest`

---

### ✅ المهمة 8.2 — Controller وViews للديون

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtController` — CRUD + `recordPayment`
- [ ] `debts/index.blade.php`:
  - تبويبان: ديون عليك / ديون لك
  - شريط تقدم السداد لكل دين
  - Badge ملوّن لحالة الدين
- [ ] `debts/create.blade.php`
- [ ] Modal تسجيل الدفعة الجزئية

---

## 📈 Phase 9 — التقارير والتحليلات (Reports)

> الهدف: رؤية مالية عميقة بواجهة بسيطة وتفاعلية.

---

### ✅ المهمة 9.1 — Services التقارير

**الحالة:** `pending`

**المطلوب:**
- [ ] `MonthlyReportService` — دخل، مصروفات، ربح لكل شهر
- [ ] `ProfitLossService` — مقارنة فترات زمنية
- [ ] `CashFlowService` — التدفق النقدي الأسبوعي/الشهري
- [ ] `TopCategoriesService` — أكثر فئات الإنفاق
- [ ] `ProjectComparisonService` — مقارنة أداء المشاريع
- [ ] Cache لجميع التقارير

---

### ✅ المهمة 9.2 — واجهة التقارير التفاعلية

**الحالة:** `pending`

**المطلوب:**
- [ ] فلتر الفترة الزمنية (شهر / ربع سنة / سنة / مخصص)
- [ ] فلتر المشروع
- [ ] رسم بياني: الدخل vs المصروفات
- [ ] جدول الأرباح والخسائر الشهري
- [ ] Pie Chart أكثر الفئات إنفاقاً
- [ ] مقارنة المشاريع
- [ ] تصدير PDF + CSV

---

## 🔔 Phase 10 — موديول الإشعارات (Notifications)

> الهدف: إشعارات ذكية عبر التطبيق والبريد بدون تأثير على الأداء.

---

### ✅ المهمة 10.1 — NotificationService وQueue Jobs

**الحالة:** `pending`

**المطلوب:**
- [ ] `DebtDueSoonNotification`
- [ ] `WeeklyFinancialSummaryNotification`
- [ ] Scheduled Commands:
  - `CheckDebtsDueSoon` — يعمل يومياً
  - `SendWeeklySummary` — يعمل كل الأحد
- [ ] إرسال عبر Queue (لا sync إطلاقاً)
- [ ] تفضيلات الإشعارات per user

---

### ✅ المهمة 10.2 — واجهة الإشعارات

**الحالة:** `pending`

**المطلوب:**
- [ ] Notification Bell في Topbar مع عدد غير المقروءة
- [ ] Dropdown لآخر الإشعارات
- [ ] صفحة كل الإشعارات + "تحديد الكل كمقروء"
- [ ] تصميم تفاعلي بـ Alpine.js

---

## 💼 Phase 11 — الاشتراكات والفوترة (Billing)

> الهدف: نظام اشتراكات مرن مع Stripe جاهز للتوسع.

---

### ✅ المهمة 11.1 — SubscriptionService وتكامل Stripe

**الحالة:** `pending`

**المطلوب:**
- [ ] تثبيت Laravel Cashier (Stripe)
- [ ] `SubscriptionService` — upgrade, downgrade, cancel
- [ ] `EnsureSubscriptionActive` Middleware
- [ ] `CheckFeatureLimit` Middleware (حدود الخطة)
- [ ] Stripe Webhooks: payment_succeeded, subscription_cancelled
- [ ] تعريف حدود كل خطة:

| الميزة | Free | Pro | Business |
|--------|------|-----|---------|
| المشاريع | 2 | 10 | غير محدود |
| المعاملات/شهر | 50 | 500 | غير محدود |
| التصدير | ❌ | ✅ | ✅ |
| التقارير المتقدمة | ❌ | ✅ | ✅ |
| API Access | ❌ | ❌ | ✅ |

---

### ✅ المهمة 11.2 — صفحة الأسعار وإدارة الاشتراك

**الحالة:** `pending`

**المطلوب:**
- [ ] صفحة Pricing (مقارنة الخطط)
- [ ] صفحة إدارة الاشتراك (الخطة الحالية، تاريخ التجديد)
- [ ] Stripe Checkout integration
- [ ] صفحة الفواتير السابقة
- [ ] تصميم مقنع للتحويل

---

## ⚙️ Phase 12 — الإعدادات (Settings)

> الهدف: تحكم كامل للمستخدم في حسابه وتفضيلاته.

---

### ✅ المهمة 12.1 — صفحة الإعدادات الكاملة

**الحالة:** `pending`

**التبويبات:**
- [ ] **الملف الشخصي:** اسم، بريد، عملة، منطقة زمنية
- [ ] **الأمان:** تغيير كلمة المرور
- [ ] **الإشعارات:** تفعيل/إيقاف كل نوع
- [ ] **الاشتراك:** الخطة الحالية + رابط للترقية
- [ ] **حذف الحساب:** مع تأكيد بكتابة البريد
- [ ] `UpdateProfileAction` + `UpdatePasswordAction`

---

## 🔒 Phase 13 — الأمان والأداء والجودة

> الهدف: منصة آمنة، سريعة، ومختبرة قبل الإطلاق.

---

### ✅ المهمة 13.1 — مراجعة الأمان والصلاحيات

**الحالة:** `pending`

**المطلوب:**
- [ ] مراجعة جميع Policies
- [ ] التأكد من `BelongsToUser` Scope على كل استعلام
- [ ] حماية Mass Assignment (`$fillable` / `$guarded`)
- [ ] CSRF protection على جميع النماذج
- [ ] Rate limiting على النماذج الحساسة
- [ ] Sanitize جميع المدخلات
- [ ] مراجعة Middleware chain الكاملة

---

### ✅ المهمة 13.2 — تحسين الأداء والاستعلامات

**الحالة:** `pending`

**المطلوب:**
- [ ] مراجعة N+1 queries بـ Laravel Debugbar
- [ ] إضافة Eager Loading حيثما يلزم
- [ ] مراجعة وتحسين Database Indexes
- [ ] استراتيجية Cache واضحة ومتسقة
- [ ] تحسين حجم الصفحات (Pagination)
- [ ] Optimize Blade views (cache مشروط)

---

### ✅ المهمة 13.3 — كتابة Feature Tests الأساسية

**الحالة:** `pending`

**المطلوب:**
- [ ] `AuthTest` — تسجيل، دخول، تحقق من البريد
- [ ] `ProjectTest` — CRUD + عزل بيانات المستخدمين
- [ ] `TransactionTest` — إنشاء، تعديل، حساب الأرصدة
- [ ] `DebtTest` — إنشاء، سداد جزئي، إغلاق
- [ ] `PolicyTest` — التأكد أن كل مستخدم معزول تماماً
- [ ] Factories + Seeders للاختبار

---

## 🚀 Phase 14 — الإعداد للإنتاج والـ API

> الهدف: إطلاق آمن مع بنية API جاهزة للتطبيق المحمول.

---

### ✅ المهمة 14.1 — إعداد بيئة الإنتاج

**الحالة:** `pending`

**المطلوب:**
- [ ] إعداد `.env.production`
- [ ] Queue Worker بـ Supervisor
- [ ] Scheduler (Cron Job)
- [ ] Config + Route Caching
- [ ] Queue Driver: Redis
- [ ] Mail: Mailgun / SES
- [ ] Storage: S3 (جاهز للمستقبل)
- [ ] Error Tracking: Sentry

---

### ✅ المهمة 14.2 — تحضير REST API للمستقبل

**الحالة:** `pending`

**المطلوب:**
- [ ] `routes/api.php` منظّم بـ Versioning (`/api/v1/`)
- [ ] API Controllers منفصلة عن Web Controllers
- [ ] API Resources لكل Model
- [ ] Laravel Sanctum للمصادقة
- [ ] Rate Limiting للـ API
- [ ] توثيق API (Scribe / Swagger)

---

## 📋 قائمة التتبع السريع

### الحالة الإجمالية

| الرمز | المعنى |
|-------|--------|
| ⬜ | pending — لم يبدأ |
| 🔄 | in_progress — جارٍ |
| ✅ | completed — منتهي |
| 🔴 | blocked — محجوب |

### جدول التتبع

| # | المهمة | الحالة |
|---|--------|--------|
| 1.1 | تثبيت Laravel 12 وإعداد البيئة | ⬜ |
| 1.2 | إنشاء جميع Migrations | ⬜ |
| 1.3 | Enums و Traits الأساسية | ⬜ |
| 1.4 | Models مع العلاقات | ⬜ |
| 2.1 | صفحة التسجيل المخصصة | ⬜ |
| 2.2 | تسجيل الدخول وإعادة كلمة المرور | ⬜ |
| 3.1 | App Layout الرئيسي | ⬜ |
| 3.2 | Blade Components المشتركة | ⬜ |
| 4.1 | Actions وServices للمشاريع | ⬜ |
| 4.2 | Controller وViews للمشاريع | ⬜ |
| 4.3.1 | فصل المصروف الشخصي عن التجاري | ⬜ |
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

## 🏁 ترتيب البدء الموصى به

```
1.1 → 1.2 → 1.3 → 1.4   (الأساس أولاً)
        ↓
2.1 → 2.2                 (المصادقة)
        ↓
3.1 → 3.2                 (الهيكل البصري)
        ↓
5.1 → 5.2                 (الفئات أولاً — يحتاجها كل شيء)
        ↓
4.1 → 4.2                 (المشاريع)
        ↓
6.1 → 6.2                 (المعاملات — القلب النابض)
        ↓
7.1 → 7.2                 (لوحة التحكم)
        ↓
8 → 9 → 10 → 11 → 12     (بقية الموديولات)
        ↓
13.1 → 13.2 → 13.3        (الجودة والأمان)
        ↓
14.1 → 14.2               (الإطلاق)
```

---

*وثيقة حية — تُحدَّث مع إتمام كل مهمة*
