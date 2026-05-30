# CHANGELOG — دراهم SaaS Financial Platform

> All notable changes to this project will be documented in this file.  
> Format: [Semantic Versioning](https://semver.org) — `MAJOR.MINOR.PATCH`  
> آخر تحديث: 29 مايو 2026

---

## [Unreleased]
> الميزات المخططة التالية

### Planned
- أسعار الصرف اليدوية (Multi-Currency conversion)
- إرسال الفاتورة/العرض بالبريد الإلكتروني
- التوقيع الرقمي لعروض الأسعار
- `quote_activities` audit trail
- REST API كامل (Laravel Sanctum)
- CRM Sprint 8 — Client Portal (Business Plan)

---

## [2.6.0] — 2026-05-30

### Added — إعدادات النظام من لوحة الإدارة
- **`settings` table** — مخزن key-value لإعدادات النظام مع Cache تلقائي
- **`Setting` Model** — `get()`, `set()`, `group()`, `setGroup()` مع Cache invalidation
- **`email_templates` table** — قوالب HTML قابلة للتعديل مع دعم المتغيرات
- **`EmailTemplate` Model** — `render(key, vars)` يستبدل المتغيرات ويُرجع subject + body
- **`MailSettings` Filament Page** — إعدادات SMTP كاملة + اختبار إرسال من الأدمن
- **`EmailTemplateResource`** — إدارة قوالب البريد (تعديل + معاينة + تجربة إرسال)
- **`CustomResetPasswordNotification`** — يستخدم قالب `password_reset` من DB
- **`emails/template.blade.php`** — قالب HTML موحَّد لجميع رسائل النظام
- **`docs/SETTINGS-ADMIN.md`** — توثيق كامل للموديول

### Changed
- `AppServiceProvider::boot()` — يطبّق إعدادات البريد من DB على runtime
- `User::sendPasswordResetNotification()` — يستخدم `CustomResetPasswordNotification`
- `WelcomeEmail` — يستخدم قالب `welcome` من DB إذا وُجد

### Fixed
- 500 على `/forgot-password` — `MAIL_SCHEME=smtps` لـ port 465
- البريد يصل كـ Spam — تصحيح `MAIL_FROM_NAME` و `MAIL_FROM_ADDRESS`
- `invoices/edit.blade.php` ParseError — نفس إصلاح `@json()` مع مصفوفة متداخلة

---

## [2.5.0] — 2026-05-29

### Added
- **Multi-Currency Display** — عرض الملخص المالي مجمّعاً حسب العملة بدلاً من الدمج المُضلَّل
  - `TransactionService::getSummary()` يُرجع `by_currency` + `multi_currency`
  - `ProjectFinancialService::getSummary()` per-currency مع الحفاظ على مقارنة العقد/الميزانية بعملة المشروع
  - `ProjectFinancialService::getPortfolioSummary()` per-currency عبر جميع المشاريع
  - بانر تحذيري أصفر في جميع صفحات الملخص المالي عند وجود عملات متعددة
- **Admin Impersonation** — دخول الأدمن بهوية أي مستخدم
  - `ImpersonateController::impersonate()` + `leave()`
  - حماية: super_admin فقط، لا انتحال super_admin آخر
  - شريط أصفر في أعلى Layout يظهر أثناء الانتحال مع زر "العودة للأدمن"
  - زر "دخول كمستخدم" في Filament UserResource
- **إحصائيات العميل الحية** — حساب `total_revenue`/`total_paid` مباشرة من الفواتير لا من قيم DB قديمة
- **`docs/MULTI-CURRENCY.md`** — توثيق قرار دعم العملات المتعددة
- **`docs/CHANGELOG.md`** (هذا الملف)

### Fixed
- `Duplicate entry 'INV-0001'` — إصلاح `invoices.number` unique عالمي → `(user_id, number)`
- `Duplicate entry 'QUO-0001'` — إصلاح `quotes.number` unique عالمي → `(user_id, number)`
- `Invoice::generateNumber()` و `Quote::generateNumber()` — إصلاح الدالة لعدّ per-user مع withTrashed()
- `ClientController::show()` — إحصائيات العميل تعرض صفر (لم تُحدَّث في DB)
- `InvoiceController::markPaid()` — يُحدِّث الآن `total_paid/total_revenue/last_payment_at` للعميل

### Changed
- `transactions/index.blade.php` — ملخص per-currency (جدول متعدد / بطاقات أحادية)
- `projects/index.blade.php` — portfolio summary per-currency
- `projects/show.blade.php` — KPIs per-currency مع عمود هامش الربح
- `crm/clients/show.blade.php` — KPI cards per-currency (إيراد + مدفوع + مستحق)

---

## [2.4.0] — 2026-05-28

### Added — Phase 19: نظام عروض الأسعار (Quotes)
- **`quotes` + `quote_items` tables** — SoftDeletes، indexes مركّبة
- **`Quote` Model** — ULID auto-generate، token عشوائي 48 حرف، ترقيم QUO-XXXX per-user، `recalculate()`, `isExpired()`, `portalUrl()`
- **`QuoteItem` Model** — casts للأرقام العشرية، sort_order
- **`QuoteStatus` Enum** — 7 حالات: draft|sent|viewed|accepted|rejected|expired|converted
- **`QuoteController`** — CRUD كامل + markSent + convertToInvoice (مع خيار إنشاء مشروع)
- **بوابة عميل عامة** — `/q/{token}` بدون Auth — portal/accept/reject مع تسجيل `client_ip`
- **الانتقال التلقائي** Sent→Viewed عند أول فتح للرابط + `viewed_at`
- **Modal تحويل لفاتورة** — مع خيار `create_project` يُنشئ Project ويربطه بالعرض والفاتورة
- **تبويب "عروض الأسعار"** في ملف العميل CRM
- **قسم "عروض الأسعار"** في صفحة المشروع
- **`docs/QUOTES.md`** — توثيق كامل

### Fixed
- `ParseError: Unclosed '[' does not match ')'` في `quotes/create.blade.php` — نقل default items إلى `@php` block

---

## [2.3.0] — 2026-05-27

### Added — Phase 18: نظام الفواتير (Invoices)
- **`invoices` + `invoice_items` tables**
- **`Invoice` Model** — ULID، ترقيم INV-XXXX، `recalculate()`, `isOverdue()`
- **`InvoiceStatus` Enum** — draft|sent|paid|overdue|cancelled
- **`InvoiceController`** — CRUD + markSent + markPaid + cancel
- **تسجيل معاملة دخل تلقائياً** عند `markPaid()`
- **إنشاء فاتورة تلقائياً** عند إنشاء مشروع جديد (income services فقط)
- **`docs/INVOICES.md`** — توثيق كامل

### Fixed
- `SQLSTATE 3780` — `invoices.project_id` كان `unsignedBigInteger`، صُحِّح إلى `char(26)`
- `SQLSTATE 1364` — `transactions.project_id` كان NOT NULL في DB، مايجريشن تصحيح
- `SQLSTATE 1048` — إرسال `null` صراحةً يُلغي DEFAULT NULL

---

## [2.2.0] — 2026-05-26

### Added — Phase 17: CRM المتقدم (Sprint 0→7)
- **Sprint 0** — Module CRM + Config + Routes
- **Sprint 1** — 12 Migration + 8 Enums + 12 Model + Seeder + Policy + Requests + DTOs + QueryBuilder
- **Sprint 2** — Client CRUD Actions + FollowUpService + ClientTagService
- **Sprint 3** — ClientController + 6 Sub-Controllers
- **Sprint 4** — ClientsImport + ClientsExport (xlsx/csv)
- **Sprint 5** — ClientHealthScoreService (V2 Recency Bias) + Artisan Commands
- **Sprint 6** — AutomationRule Migration + Engine + Actions + Controller + Views
- **Sprint 7** — Client List View + Client Profile 360° + Tag Manager + Follow-ups Dashboard
- **Admin Impersonation** — ImpersonateController + Filament زر

---

## [2.1.0] — 2026-05-20

### Added — Phase 16: تحسينات مايو 2026
- نظام متابعات (Follow-ups) مع لوحة كاملة
- نظام أتمتة (Automation Rules)
- استيراد/تصدير العملاء (xlsx)
- واجهة Segments وShared Reports
- Migration: إصلاح `transactions.project_id` nullable

---

## [2.0.0] — 2026-05-01

### Added — Phase 15: لوحة الإدارة Filament
- UserResource — CRUD + Suspend/Activate/ResetPlan/SendEmail/DeleteData
- SubscriptionResource — عرض الاشتراكات
- Revenue Widget — MRR, ARR, Churn
- SystemHealth Widget — Queue/Failed Jobs/Scheduler/Log
- `canAccessPanel()` في User Model

---

## [1.5.0] — 2026-04-15

### Added — Phase 14: الإنتاج
- نشر على cPanel Shared Hosting — workuflow.palgoals.com
- Landing Page تسويقية عربية RTL
- Onboarding للمستخدم الجديد
- بريد الترحيب (WelcomeEmail + Queue Job)
- تصدير التقارير PDF/Excel

---

## [1.4.0] — 2026-04-01

### Added — Phase 13-12
- 54 اختبار Pest — جميعها ✅
- صفحة الإعدادات الكاملة
- Rate Limiting + Security Headers

---

## [1.3.0] — 2026-03-15

### Added — Phase 8-11
- الديون والالتزامات (Debts) مع سجل سداد جزئي
- التقارير والتحليلات (P&L، Cash Flow)
- نظام الإشعارات (Notifications)
- الاشتراكات والفوترة (Billing — هيكل جاهز)

---

## [1.2.0] — 2026-03-01

### Added — Phase 5-7
- الفئات (Categories) مع 12 فئة افتراضية
- الالتزامات المتكررة (Recurring) مع Scheduler يومي
- لوحة التحكم (Dashboard) مع KPIs ورسوم بيانية

---

## [1.1.0] — 2026-02-15

### Added — Phase 4 + 4.3 + 4.5
- إدارة المشاريع (Projects) مع عزل مالي
- فصل الشخصي/التجاري (ProjectType)
- الميزانية (Budget) مع Progress Bars

---

## [1.0.0] — 2026-02-01

### Added — Phase 1-3
- البنية التحتية: Laravel 12 + PHP 8.2 + MySQL
- نظام المصادقة (Breeze + spatie/permission)
- Layout رئيسي RTL + Blade Components
- Multi-tenant عبر `BelongsToUser` Global Scope
- المعاملات (Transactions) — core module
- ULID كمفاتيح مسار لجميع Models
