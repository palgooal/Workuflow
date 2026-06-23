# خطة تنفيذ التسعير — دراهم SaaS
## PRICING-IMPLEMENTATION-PLAN.md

> **المرجع الاستراتيجي:** `docs/PRICING-STRATEGY-V1-FINAL.md`
> **التاريخ:** 23 يونيو 2026
> **الإصدار:** 1.0
> **الحالة:** ✅ جاهز للتنفيذ
> **الجمهور:** فريق الهندسة — Laravel / PHP

---

## جدول المحتويات

1. [الملخص التنفيذي](#1-الملخص-التنفيذي)
2. [تحليل أثر النظام](#2-تحليل-أثر-النظام)
3. [مصفوفة Feature Gates](#3-مصفوفة-feature-gates)
4. [تنفيذ حدود الاستخدام](#4-تنفيذ-حدود-الاستخدام)
5. [تنفيذ Founder Pricing](#5-تنفيذ-founder-pricing)
6. [معمارية الاشتراكات](#6-معمارية-الاشتراكات)
7. [تغييرات قاعدة البيانات](#7-تغييرات-قاعدة-البيانات)
8. [متطلبات لوحة الإدارة](#8-متطلبات-لوحة-الإدارة)
9. [متطلبات الموقع التسويقي](#9-متطلبات-الموقع-التسويقي)
10. [متطلبات منطقة الفوترة](#10-متطلبات-منطقة-الفوترة)
11. [تجربة الترقية](#11-تجربة-الترقية)
12. [المخاطر التقنية](#12-المخاطر-التقنية)
13. [خارطة التطوير](#13-خارطة-التطوير)
14. [قائمة التحقق النهائية](#14-قائمة-التحقق-النهائية)

---

## 1. الملخص التنفيذي

### ما الذي يجب تنفيذه؟

استراتيجية التسعير المعتمدة في V1-FINAL تتطلب تحويل النظام الحالي — الذي يملك هيكل خطط بدائي وبدون feature gates حقيقية — إلى منظومة اشتراكات كاملة الإنتاج.

### الوضع الحالي (As-Is)

| العنصر | الوضع الحالي | المطلوب |
|--------|-------------|---------|
| SubscriptionPlan Enum | free/pro/business ✅ | تحديث الحدود والقدرات |
| Subscription Model | حقول أساسية فقط | إضافة billing_cycle، is_founder، grace_period |
| Feature Gates | غائبة تقريباً | بناء نظام كامل |
| حدود الاستخدام | projects + transactions فقط | clients + invoices + quotes + storage + team |
| Founder Pricing | غير موجودة | بناء من الصفر |
| USD-first | غير مطبّق (SAR حالياً) | تحويل كامل |
| Annual/Monthly cycle | غير موجود | بناء من الصفر |
| صفحة التسعير التسويقية | CTAs معطّلة href="#" | إعادة بناء كاملة |
| Admin Panel | لا يوجد Billing management | إضافة Filament pages |

### النطاق الإجمالي

- **7 مجالات** تحتاج تغييرات في قاعدة البيانات
- **18 ميزة** تحتاج feature gates جديدة
- **6 حدود استخدام** تحتاج validation على 3 مستويات
- **6 مراحل تطوير** بترتيب تسلسلي محدد

### تعريفات أساسية

```
Starter  = ما كان يُعرف بـ "free" في الكود
Pro      = بدون تغيير
Business = بدون تغيير

billing_cycle: monthly | annual
is_founder: bool — هل هذا المشترك من أول 100؟
founder_seat_number: int — رقم المقعد (1-100)
grace_period_ends_at: datetime — يُمنح 7 أيام بعد انتهاء الدفع
```

---

## 2. تحليل أثر النظام

### 2.1 قاعدة البيانات

**التأثير: عالٍ**

جدول `subscriptions` يحتاج أعمدة جديدة:
- `billing_cycle` — monthly أو annual
- `is_founder` — boolean
- `founder_seat_number` — nullable int
- `grace_period_ends_at` — nullable datetime
- `cancelled_at` — nullable datetime
- `current_period_start` — datetime
- `current_period_end` — datetime

جداول جديدة مطلوبة:
- `founder_seats` — لتتبع المقاعد الـ 100 والمنح والإلغاء
- `plan_limit_overrides` — للـ admin overrides على مستوى المستخدم
- `subscription_audit_logs` — audit trail لكل تغيير في الاشتراك
- `pricing_configs` — لتخزين الأسعار الرسمية في DB بدل config فقط

جدول `users` يحتاج:
- تحديث enum `subscription_plan` ليعكس اسم "starter" بدلاً من "free"

---

### 2.2 Backend / SubscriptionPlan Enum

**التأثير: عالٍ**

`app/Support/Enums/SubscriptionPlan.php` — الحالة الراهنة:
- `maxProjects()` — يُرجع 2 للمجاني (V1 يقول 3)
- `maxTransactionsPerMonth()` — يُرجع 500 للـ Pro (V1 يقول 1,000)
- لا توجد: `maxClients()`, `maxInvoicesPerMonth()`, `maxQuotesPerMonth()`, `maxStorageMB()`, `maxTeamMembers()`
- لا توجد: `canSendEmail()`, `canUseWhatsAppAutomation()`, `canAccessClientPortal()`, `canUseCustomTemplates()`, `canUseRecurringInvoices()`, `canUseZATCA()`, `canAccessAPI()`, `hasAdvancedCRM()`, `canExportData()`, `canUseWallets()`, `canUseMultiCurrency()`, `isWhiteLabel()`

كل هذه الميزات يجب تعريفها في الـ Enum كـ methods.

---

### 2.3 Billing / SubscriptionService

**التأثير: عالٍ**

`app/Modules/Billing/Services/SubscriptionService.php` — يحتاج:
- دعم `billing_cycle` (monthly/annual) في `activatePlan()`
- `ends_at` يُحسب: شهر أو سنة حسب الـ cycle
- منطق Founder Pricing: التحقق، تعيين `is_founder=true`، تسجيل رقم المقعد
- `getPlanPrices()` — تحديث لـ USD-first مع دعم annual/monthly
- `getFounderSeatsRemaining()` — حساب المقاعد المتبقية
- `applyGracePeriod()` — 7 أيام بعد الانتهاء

---

### 2.4 Middleware / CheckSubscriptionLimits

**التأثير: عالٍ**

`app/Http/Middleware/CheckSubscriptionLimits.php` — الحالة الراهنة:
- يتحقق من: projects, transactions فقط
- يجب إضافة: clients, invoices, quotes, storage, team_members
- يجب تغيير طريقة الاستجابة: حالياً redirect()->back() — يجب دعم JSON responses للـ API

---

### 2.5 Feature Gates

**التأثير: عالٍ — مكوّن جديد كلياً**

لا يوجد حالياً أي نظام feature gates في الكود. يجب بناء:
- `app/Support/Services/FeatureGateService.php` — خدمة مركزية
- `app/Http/Middleware/RequireFeature.php` — middleware للـ routes
- `app/View/Components/FeatureGate.php` — Blade component للـ UI
- Blade directive: `@feature('feature_name')` / `@endfeature`

---

### 2.6 CRM / Clients

**التأثير: متوسط**

`ClientController` — يحتاج:
- تطبيق `CheckSubscriptionLimits` على `store()` لحد 5 عملاء في Starter
- `ClientPolicy` — تقييد بعض العمليات المتقدمة (Custom Fields، Segments) للـ Business

---

### 2.7 Projects

**التأثير: منخفض — middleware موجود جزئياً**

حد المشاريع في Middleware موجود لكن بقيمة خاطئة (2 بدل 3). يجب:
- تحديث `maxProjects()` في الـ Enum من 2 إلى 3
- إضافة: Time Tracking gate (Pro+)، مشاريع الفريق (Business)

---

### 2.8 Invoices

**التأثير: متوسط**

`InvoiceController` — يحتاج:
- حد 5 فواتير/شهر للـ Starter
- gate: إرسال البريد (Pro+)
- gate: قوالب مخصصة + شعار (Pro+)
- gate: Recurring Invoices (Pro+)
- gate: ZATCA (Pro+)
- gate: White Label (Business)

---

### 2.9 Quotes

**التأثير: متوسط**

`QuoteController` — يحتاج:
- حد 3 عروض/شهر للـ Starter
- نفس gates الفواتير تقريباً

---

### 2.10 Transactions

**التأثير: منخفض — middleware موجود**

- تحديث الحد من 500 إلى 1,000 للـ Pro في الـ Enum
- إضافة: gate لـ Import من Excel (Pro+)
- إضافة: gate لـ Bulk Operations (Business)

---

### 2.11 Teams

**التأثير: متوسط**

`TeamMemberController` — يحتاج:
- حد 1 للـ Starter (لا يمكن إضافة أعضاء)
- حد 2 للـ Pro (مساعد واحد فقط)
- حد 10 للـ Business
- gate: صلاحيات مخصصة (Business)
- gate: Activity Log (Business)

---

### 2.12 Reports

**التأثير: متوسط**

`ReportController`, `ReportExportController` — يحتاج:
- gate: التقارير المتقدمة (Pro+)
- gate: تصدير Excel/CSV (Pro+)
- gate: تصدير PDF للتقارير (Pro+)
- gate: Cash Flow Forecast (Pro+)
- gate: تقارير الفريق (Business)

---

### 2.13 Client Portal

**التأثير: متوسط**

الـ Client Portal يُستدعى عبر `ClientPortalToken`. يجب:
- gate: الوصول للـ Client Portal (Pro+)
- إذا كان Starter: منع إنشاء tokens

---

### 2.14 WhatsApp

**التأثير: منخفض للمشاركة الأساسية — عالٍ للأتمتة**

- WhatsApp Basic Sharing (روابط wa.me في PDF/الفواتير): يبقى لجميع الخطط — لا تغيير
- WhatsApp Automation (تذكيرات تلقائية، إشعارات فريق، متابعة): gate لـ Business فقط
- أي كود أتمتة WhatsApp حالي أو مستقبلي يُلف بـ `$this->gates->check('whatsapp_automation')`

---

### 2.15 Wallets

**التأثير: متوسط**

`WalletController` — يحتاج:
- gate: الصناديق المالية (Pro+)
- إذا كان Starter: إخفاء الـ Wallets من القائمة الجانبية + redirect مع upgrade prompt

---

### 2.16 APIs

**التأثير: متوسط**

`routes/api.php` — يحتاج:
- middleware جديد: `RequireFeature:api_access`
- Rate limiting: 1,000 req/day للـ Business
- أي API route لا يُستدعى بـ Business plan → 403

---

### 2.17 Admin Panel (Filament)

**التأثير: عالٍ — صفحات جديدة**

يحتاج صفحات Filament جديدة (تفاصيل في القسم 8):
- إدارة الخطط والأسعار
- إدارة الاشتراكات
- تتبع Founder Seats
- Plan Overrides لمستخدمين محددين
- Audit Log للاشتراكات

---

### 2.18 Marketing Website

**التأثير: عالٍ — إعادة بناء**

`resources/views/marketing/pricing.blade.php` — يحتاج:
- CTAs وظيفية (بدلاً من href="#")
- USD-first pricing
- Annual/Monthly toggle يعمل
- Founder badge + counter ديناميكي
- تحديث جدول المقارنة

---

## 3. مصفوفة Feature Gates

### 3.1 تعريف الـ Gates

كل gate له اسم string ثابت يُستخدم في كل مكان في الكود.

| Gate Key | Starter | Pro | Business | الوصف |
|----------|:-------:|:---:|:--------:|-------|
| `advanced_crm` | ❌ | ✅ | ✅ | Health Score، Segments، Follow-ups |
| `client_portal` | ❌ | ✅ | ✅ | بوابة العميل |
| `custom_client_fields` | ❌ | ❌ | ✅ | حقول مخصصة للعملاء |
| `time_tracking` | ❌ | ✅ | ✅ | تتبع الوقت في المشاريع |
| `project_profitability` | ❌ | ✅ | ✅ | تقارير ربحية المشروع |
| `team_projects` | ❌ | ❌ | ✅ | مشاريع الفريق التعاونية |
| `milestones` | ❌ | ❌ | ✅ | Milestones & Dependencies |
| `import_excel` | ❌ | ✅ | ✅ | استيراد من Excel |
| `recurring_transactions` | ❌ | ✅ | ✅ | معاملات متكررة |
| `bulk_operations` | ❌ | ❌ | ✅ | عمليات جماعية |
| `send_invoice_email` | ❌ | ✅ | ✅ | إرسال الفواتير بالبريد |
| `custom_invoice_templates` | ❌ | ✅ | ✅ | قوالب + شعار الشركة |
| `recurring_invoices` | ❌ | ✅ | ✅ | فواتير متكررة |
| `zatca_compliance` | ❌ | ✅ | ✅ | امتثال ZATCA |
| `payment_gateways` | ❌ | ✅ | ✅ | ربط بوابات الدفع |
| `white_label` | ❌ | ❌ | ✅ | إزالة شعار دراهم |
| `advanced_reports` | ❌ | ✅ | ✅ | تقارير مالية متقدمة |
| `export_data` | ❌ | ✅ | ✅ | تصدير Excel/CSV/PDF |
| `wallets` | ❌ | ✅ | ✅ | الصناديق المالية |
| `multi_currency` | ❌ | ✅ | ✅ | عملات متعددة |
| `cash_flow_forecast` | ❌ | ✅ | ✅ | توقع التدفق النقدي |
| `team_reports` | ❌ | ❌ | ✅ | تقارير الفريق والمشاريع |
| `custom_permissions` | ❌ | ❌ | ✅ | صلاحيات مخصصة |
| `activity_log` | ❌ | ❌ | ✅ | سجل النشاطات |
| `two_factor_auth` | ❌ | ✅ | ✅ | المصادقة الثنائية |
| `api_access` | ❌ | ❌ | ✅ | الوصول للـ API |
| `webhooks` | ❌ | ❌ | ✅ | Webhooks |
| `automation_rules` | ❌ | ❌ | ✅ | قواعد الأتمتة |
| `whatsapp_automation` | ❌ | ❌ | ✅ | أتمتة WhatsApp |

---

### 3.2 آلية الـ FeatureGateService

```
FeatureGateService::check(string $gate, User $user = null): bool
FeatureGateService::require(string $gate, User $user = null): void  // throws FeatureNotAvailableException
FeatureGateService::getBlockedMessage(string $gate): string
FeatureGateService::getUpgradePlan(string $gate): SubscriptionPlan
```

الـ Service يقرأ الـ Gate من الـ SubscriptionPlan Enum عبر:
```
SubscriptionPlan::Pro->can('advanced_reports')  // true
SubscriptionPlan::Free->can('advanced_reports') // false
```

---

### 3.3 سلوك الـ UI عند الحجب

| السيناريو | السلوك المطلوب |
|-----------|---------------|
| زر/رابط لميزة محجوبة | يظهر مع lock icon 🔒 + tooltip "متاح في خطة Pro" |
| صفحة كاملة محجوبة | redirect لـ `/billing/upgrade?feature=X` مع رسالة |
| API call لميزة محجوبة | 403 JSON: `{"error": "feature_not_available", "upgrade_to": "pro"}` |
| نموذج إنشاء عند الوصول للحد | الزر معطّل + رسالة "وصلت للحد الأقصى" + رابط ترقية |
| Livewire action محجوبة | dispatch event → modal ترقية |

---

### 3.4 Upgrade Prompts حسب الـ Gate

| Gate | رسالة الترقية | الخطة المطلوبة |
|------|-------------|--------------|
| `advanced_crm` | "متابعة عملائك بذكاء — ترقّ للاحترافي" | Pro |
| `client_portal` | "أعطِ عملاءك بوابة احترافية — ترقّ للاحترافي" | Pro |
| `send_invoice_email` | "أرسل فواتيرك مباشرة بالبريد — ترقّ للاحترافي" | Pro |
| `export_data` | "صدّر بياناتك Excel/PDF — ترقّ للاحترافي" | Pro |
| `whatsapp_automation` | "أتمتة متابعة عملائك على واتساب — ترقّ للأعمال" | Business |
| `api_access` | "ادمج دراهم مع أنظمتك — ترقّ للأعمال" | Business |
| `white_label` | "احجب شعار دراهم من فواتيرك — ترقّ للأعمال" | Business |
| `team_projects` | "اعمل مع فريقك على نفس المشروع — ترقّ للأعمال" | Business |

---

### 3.5 استراتيجية Error Handling

```
FeatureNotAvailableException (extends HttpException, code 403)
  → يُلتقط في Handler.php
  → Web: redirect('/billing/upgrade?feature={gate}&from={url}')
  → API: JSON 403 مع upgrade_url
  → Livewire: dispatch('open-upgrade-modal', ['feature' => gate])
```

---

## 4. تنفيذ حدود الاستخدام

### 4.1 جدول الحدود الكاملة

| المورد | Starter | Pro | Business | نافذة الحساب |
|--------|:-------:|:---:|:--------:|-------------|
| clients | 5 | ∞ | ∞ | إجمالي (ليس شهري) |
| projects (active) | 3 | ∞ | ∞ | إجمالي |
| invoices | 5 | ∞ | ∞ | شهري (calendar month) |
| quotes | 3 | ∞ | ∞ | شهري (calendar month) |
| transactions | 50 | 1,000 | ∞ | شهري |
| team_members | 0 | 1 | 9 | إجمالي (إضافي فوق صاحب الحساب) |
| storage_mb | 500 | 10,240 | 102,400 | إجمالي |

> ملاحظة: الـ Pro يُجيز "مساعداً واحداً" إضافياً — بمعنى أعضاء فريق = 1 (بالإضافة للمالك = 2 إجمالاً).

---

### 4.2 Clients — التنفيذ التفصيلي

**Database Validation:**
- لا تحقق على مستوى DB (لا `check constraint` — Laravel يتحكم)

**Backend Validation:**
- `CheckSubscriptionLimits` middleware على `ClientController@store`
- منطق: `$user->clients()->whereNull('deleted_at')->count() >= $plan->maxClients()`
- للـ Pro/Business: `maxClients()` يُرجع `PHP_INT_MAX`

**UI Validation:**
- زر "إضافة عميل" يظهر معطّلاً مع رسالة عند الوصول للحد
- عداد: "5 / 5 عملاء — وصلت للحد الأقصى"

**Upgrade Flow:**
- flash message: "لإضافة المزيد من العملاء، ترقّ للخطة الاحترافية"
- الـ flash message يتضمن رابط `/billing/upgrade?feature=clients&current=5&limit=5`

---

### 4.3 Projects — التنفيذ التفصيلي

**Backend Validation (موجود — تحديث فقط):**
- `maxProjects()` في الـ Enum: Free → **3** (كان 2)
- الـ Middleware موجود في `routes/web.php` على `projects.store`
- تحديث رسالة الـ hint: "الترقية إلى Pro تتيح لك مشاريع غير محدودة"

**UI Validation:**
- بطاقة "مشروع جديد" تظهر مع قفل عند الحد
- عداد في header المشاريع: "3 / 3 مشاريع"

**Active Projects فقط:**
- المشاريع المؤرشفة/المكتملة لا تُحسب في الحد

---

### 4.4 Invoices — التنفيذ التفصيلي

**Backend Validation:**
```
$count = $user->invoices()
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count();

if ($count >= $plan->maxInvoicesPerMonth()) {
    // trigger upgrade
}
```

**UI Validation:**
- عداد: "3 / 5 فواتير هذا الشهر"
- زر "فاتورة جديدة" معطّل بعد الحد مع رسالة "ترقّ لفواتير غير محدودة"

**ملاحظة:** الفواتير المحذوفة (soft-deleted) لا تُحسب.

---

### 4.5 Quotes — التنفيذ التفصيلي

نفس منطق الفواتير تماماً:
```
$user->quotes()
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count() >= $plan->maxQuotesPerMonth()
```

---

### 4.6 Transactions — التنفيذ التفصيلي

**موجود — تحديث فقط:**
- `maxTransactionsPerMonth()` في الـ Enum: Pro → **1,000** (كان 500)
- رسالة الـ hint تُحدَّث: "الترقية إلى Pro تتيح لك 1,000 معاملة شهرياً"

---

### 4.7 Team Members — التنفيذ التفصيلي

**Backend Validation:**
```
$count = $user->teamMembers()->whereNull('deleted_at')->count();
// Starter: max 0 (لا يمكن إضافة أعضاء)
// Pro: max 1
// Business: max 9
```

**UI Validation:**
- Starter: زر "إضافة عضو" يُحيل مباشرة لـ upgrade
- Pro: بعد إضافة عضو واحد، الزر يُعطّل
- Business: عداد "3 / 9 أعضاء"

---

### 4.8 Storage — التنفيذ التفصيلي

**Backend Validation:**

```
StorageLimitService::checkBeforeUpload(User $user, int $fileSizeBytes): void
```

```
$usedMB = $user->getUsedStorageMB(); // يحسب من file_uploads أو attachments
$limitMB = $user->currentPlan()->maxStorageMB();
if (($usedMB + ($fileSizeBytes / 1048576)) > $limitMB) {
    throw StorageLimitExceededException
}
```

**Database:**
- عمود `used_storage_bytes` على `users` table — يُحدَّث بعد كل رفع/حذف
- أو: يُحسب من الـ attachments table (أبطأ لكن أدق)

**UI Validation:**
- شريط تقدم: "342 MB / 500 MB مستخدم"
- عند 80%: تحذير "تقترب من الحد"
- عند 100%: منع الرفع مع رسالة ترقية

---

## 5. تنفيذ Founder Pricing

### 5.1 هيكل قاعدة البيانات

**جدول `founder_seats` (جديد):**

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | ulid | primary key |
| `seat_number` | unsignedSmallInteger | 1 → 100 (unique) |
| `user_id` | foreignId | المستخدم الحاصل على المقعد |
| `subscription_id` | foreignId | الاشتراك المرتبط |
| `assigned_at` | timestamp | تاريخ التعيين |
| `released_at` | timestamp nullable | تاريخ الإلغاء (إذا ألغى الاشتراك) |
| `is_active` | boolean | هل المقعد نشط حالياً؟ |
| `plan` | enum(pro, business) | الخطة |

**Indexes:**
- `UNIQUE (seat_number)` — كل رقم مقعد مرة واحدة فقط
- `INDEX (user_id)` — البحث السريع
- `INDEX (is_active)` — لحساب المتبقي

---

**تعديلات `subscriptions` table:**

| العمود الجديد | النوع | الوصف |
|--------------|-------|-------|
| `billing_cycle` | enum(monthly, annual) | دورة الفوترة |
| `is_founder` | boolean default false | هل مشترك مؤسسين؟ |
| `founder_seat_id` | foreignId nullable | FK لـ founder_seats |
| `amount_paid` | decimal(8,2) nullable | المبلغ المدفوع (بالدولار) |
| `currency` | char(3) default 'USD' | عملة الفوترة |
| `grace_period_ends_at` | timestamp nullable | انتهاء فترة السماح |
| `cancelled_at` | timestamp nullable | تاريخ الإلغاء |
| `current_period_start` | timestamp | بداية الدورة الحالية |
| `current_period_end` | timestamp | نهاية الدورة الحالية |

---

### 5.2 منطق الأهلية

```
FounderPricingService::isFounderEligible(): bool
  → عدد المقاعد النشطة < 100
  → AND المستخدم لم يكن مشتركاً من قبل (أو كان مجانياً فقط)
  → AND لا يملك founder_seat حالياً

FounderPricingService::getSeatsRemaining(): int
  → 100 - founder_seats->where('is_active', true)->count()

FounderPricingService::assignSeat(User $user, string $plan, string $cycle): FounderSeat
  → DB transaction
  → lock for update على أعلى seat_number لمنع race condition
  → seat_number = max(seat_number) + 1
  → إنشاء الـ founder_seat
  → ربطه بالـ subscription
  → تحديث is_founder=true على الـ subscription
```

---

### 5.3 حساب المقاعد المتبقية

```
// يُخزَّن في Cache لـ 30 ثانية — لا يُستدعى DB في كل request
Cache::remember('founder_seats_remaining', 30, function () {
    return 100 - FounderSeat::active()->count();
});
```

---

### 5.4 منطق الإلغاء والإعادة

**عند إلغاء الاشتراك:**
```
FounderPricingService::releaseSeat(Subscription $subscription): void
  → founder_seats->update(['is_active' => false, 'released_at' => now()])
  → subscription->update(['is_founder' => false, 'founder_seat_id' => null])
  → PERMANENT — لا يمكن استعادة نفس رقم المقعد
  → Cache::forget('founder_seats_remaining')
```

**عند محاولة إعادة الاشتراك:**
```
FounderPricingService::isFounderEligible($user): bool
  → إذا كان للمستخدم released founder_seat سابق → false دائماً
  → حتى لو كان الـ founder_seats_remaining > 0
```

**السبب:** الـ V1-FINAL صريح — الإلغاء يُسقط الحق نهائياً.

---

### 5.5 Race Conditions — الحماية

المشكلة: مستخدمان يحاولان الاشتراك في نفس اللحظة عندما تبقّى مقعد واحد.

الحل:
```
DB::transaction(function () use ($user, $plan, $cycle) {
    // قفل الجدول بـ pessimistic lock
    $seatsUsed = FounderSeat::lockForUpdate()->where('is_active', true)->count();

    if ($seatsUsed >= 100) {
        throw new FounderSeatsExhaustedException();
    }

    $nextSeat = $seatsUsed + 1;

    FounderSeat::create([
        'seat_number' => $nextSeat,
        'user_id'     => $user->id,
        ...
    ]);
});
```

---

### 5.6 الحماية من الاحتيال (Fraud Prevention)

| الخطر | الحماية |
|-------|---------|
| نفس الشخص بحسابات متعددة | مطابقة IP + بطاقة الدفع عند التحقق |
| اشتراك وهمي لحجز مقعد | مقعد يُعيَّن فقط بعد تأكيد الدفع من Togo |
| إلغاء ثم إعادة | الـ released seats لا تُعيَّن لنفس المستخدم أبداً |
| Transfer بين حسابات | الـ seat مرتبط بـ user_id ولا يقبل النقل |
| إلغاء في فترة Grace | الـ seat يُحرَّر فور انتهاء Grace Period إذا لم يتجدد |

---

### 5.7 حالات الحافة (Edge Cases)

| الحالة | السلوك المطلوب |
|--------|--------------|
| الدفع فشل بعد حجز المقعد | rollback الـ DB transaction — المقعد لا يُعيَّن |
| المستخدم يُلغي خلال 24 ساعة | يُعامَل كإلغاء عادي — يخسر المقعد |
| الـ seat_number = 100 ويدخل مستخدمان في نفس الوقت | lockForUpdate يضمن أن واحداً فقط ينجح |
| المدير يُريد منح مقعد يدوياً | لوحة الإدارة: `AssignFounderSeatAction` بتوثيق |
| المدير يُريد إلغاء مقعد يدوياً | `RevokeFounderSeatAction` مع سبب موثّق |
| الـ 100 ممتلئة ومستخدم يحاول | يُوجَّه لصفحة "انتهت مقاعد المؤسسين" مع waitlist اختياري |

---

## 6. معمارية الاشتراكات

### 6.1 حالات الاشتراك (States)

```
                    ┌─────────────────────────────┐
                    │         ACTIVE               │
                    │  (status = 'active',         │
                    │   ends_at في المستقبل)        │
                    └─────────────────────────────┘
                           │              │
              [Renewal]    │              │  [Cancel / Expire]
                           │              ▼
                    ┌──────┘    ┌──────────────────┐
                    │           │  GRACE_PERIOD     │
                    │           │  (7 أيام بعد      │
                    │           │   ends_at)        │
                    │           └──────────────────┘
                    │                    │
                    │         [دفع ناجح] │  [7 أيام انتهت]
                    │                    │
                    │           ┌────────▼─────────┐
                    │           │    EXPIRED        │
                    │           │  (plan → Starter) │
                    │           └──────────────────┘
                    │
              ┌─────▼──────────────────────────────┐
              │         CANCELLED                   │
              │  (cancelled_at مُسجَّل،              │
              │   ينتهي في current_period_end)      │
              └─────────────────────────────────────┘
```

---

### 6.2 دورات الفوترة

**Monthly:**
- `current_period_start = now()`
- `current_period_end = now()->addMonth()`
- `ends_at = current_period_end`

**Annual:**
- `current_period_start = now()`
- `current_period_end = now()->addYear()`
- `ends_at = current_period_end`
- يُعرض: "وفر 24% — اشترك سنوياً"

---

### 6.3 تدفق الترقية (Upgrade Flow)

```
المستخدم (Starter) يضغط "ترقّ للاحترافي"
          │
          ▼
يختار Monthly ($17) أو Annual ($13×12 = $156)
          │
          ▼
[إذا founder eligible] → يُعرض خيار Founder: $10/mo أو $8×12 = $96/yr
          │
          ▼
checkout() → Togo → payment
          │
          ▼
togoCallback() → SubscriptionService::activatePlan()
          │
          ├─ [founder eligible + seats remaining] → FounderPricingService::assignSeat()
          │
          ▼
user->subscription_plan = Pro
subscription->status = active
          │
          ▼
redirect → billing.index + success message
```

---

### 6.4 تدفق الخفض (Downgrade Flow)

> **قاعدة:** دراهم في هذه المرحلة لا يدعم الخفض التلقائي من خلال الـ UI. المستخدم يتواصل بالدعم.
> السبب: Togo redirect-based — لا يدعم subscription management مباشرة.

للتنفيذ اليدوي من Admin:
```
Admin → Subscription Management → Downgrade User
      → SubscriptionService::downgradePlan($user, $newPlan)
      → ينتهي في current_period_end الحالي
      → عند الانتهاء: plan = $newPlan
```

---

### 6.5 تدفق الإلغاء (Cancellation Flow)

```
المستخدم يطلب الإلغاء
          │
          ▼
"هل أنت متأكد؟ ستفقد وصولك في [current_period_end]"
          │
          ▼
SubscriptionService::cancelPlan($user)
  → subscription->cancelled_at = now()
  → subscription->status = 'cancelled' (لكن يبقى active حتى current_period_end)
  → user->subscription_plan لا يتغير حتى الانتهاء
          │
          ▼
[عند current_period_end]:
  → Scheduled Job: ExpireSubscriptionsJob
  → user->subscription_plan = Starter
  → [إذا founder]: FounderPricingService::releaseSeat()
```

---

### 6.6 Grace Period

```
عند انتهاء ends_at بدون تجديد:
  → subscription->status = 'grace_period'
  → grace_period_ends_at = ends_at + 7 days
  → المستخدم يحتفظ بوصوله الكامل
  → يظهر banner: "اشتراكك منتهٍ — لديك 7 أيام لتجديده"

عند انتهاء grace_period_ends_at:
  → ExpireSubscriptionsJob يُشغَّل يومياً
  → user->subscription_plan = Starter
  → subscription->status = 'expired'
  → [إذا founder]: يخسر المقعد
```

---

### 6.7 Scheduled Jobs المطلوبة

| Job | الجدول | الغرض |
|-----|--------|-------|
| `ExpireSubscriptionsJob` | يومياً 02:00 | تحويل expired → Starter |
| `SendGraceWarningJob` | يومياً 08:00 | إرسال تحذير Day 1 + Day 5 |
| `UpdateFounderSeatsCacheJob` | كل 5 دقائق | تحديث Cache للمقاعد |

---

## 7. تغييرات قاعدة البيانات

### 7.1 قائمة تدقيق الـ Migrations

#### الجداول الجديدة

- [ ] `founder_seats` — مع index على (seat_number UNIQUE), (user_id), (is_active)
- [ ] `plan_limit_overrides` — عمدة: user_id, resource, limit_value, reason, granted_by, expires_at
- [ ] `subscription_audit_logs` — عمدة: subscription_id, action, old_values(JSON), new_values(JSON), performed_by, ip_address
- [ ] `pricing_configs` — عمدة: plan, billing_cycle, is_founder, amount_usd, currency_rates(JSON), effective_from, effective_to

---

#### تعديلات الجداول الموجودة

**`subscriptions` — إضافة أعمدة:**
- [ ] `billing_cycle` ENUM('monthly', 'annual') DEFAULT 'monthly'
- [ ] `is_founder` BOOLEAN DEFAULT FALSE
- [ ] `founder_seat_id` ULID NULLABLE FK → founder_seats
- [ ] `amount_paid` DECIMAL(8,2) NULLABLE
- [ ] `currency` CHAR(3) DEFAULT 'USD'
- [ ] `grace_period_ends_at` TIMESTAMP NULLABLE
- [ ] `cancelled_at` TIMESTAMP NULLABLE
- [ ] `current_period_start` TIMESTAMP NULLABLE
- [ ] `current_period_end` TIMESTAMP NULLABLE

**`subscriptions` — تعديل ENUM:**
- [ ] `status` ENUM: إضافة قيمة 'grace_period' → (active, grace_period, cancelled, expired)

**`users` — إضافة أعمدة:**
- [ ] `used_storage_bytes` BIGINT UNSIGNED DEFAULT 0
- [ ] `subscription_expires_at` TIMESTAMP NULLABLE — denormalized للسرعة

---

#### Indexes المطلوبة

- [ ] `subscriptions`: INDEX (user_id, status, current_period_end)
- [ ] `subscriptions`: INDEX (grace_period_ends_at) — لـ Job يومي
- [ ] `founder_seats`: UNIQUE (seat_number)
- [ ] `founder_seats`: INDEX (user_id, is_active)
- [ ] `subscription_audit_logs`: INDEX (subscription_id, created_at)

---

#### Enums المعدّلة

- [ ] `subscriptions.status` → إضافة 'grace_period'
- [ ] تحديث `SubscriptionPlan` PHP Enum (ليس DB enum) — إضافة methods جديدة
- [ ] `subscriptions.billing_cycle` → ENUM جديد: 'monthly', 'annual'

---

#### Relationships

- `Subscription` → `FounderSeat` (belongsTo, nullable)
- `FounderSeat` → `Subscription` (hasOne)
- `FounderSeat` → `User` (belongsTo)
- `User` → `PlanLimitOverride` (hasMany)
- `Subscription` → `SubscriptionAuditLog` (hasMany)

---

### 7.2 ترتيب Migrations

يجب تشغيل الـ migrations بهذا الترتيب:

1. `create_founder_seats_table`
2. `create_plan_limit_overrides_table`
3. `create_subscription_audit_logs_table`
4. `create_pricing_configs_table`
5. `add_billing_columns_to_subscriptions_table` (billing_cycle, is_founder, founder_seat_id, amount_paid, currency, grace_period_ends_at, cancelled_at, current_period_start, current_period_end)
6. `update_subscriptions_status_enum` (إضافة grace_period)
7. `add_storage_and_expiry_to_users_table`
8. `seed_pricing_configs_from_strategy_v1` — seed الأسعار المعتمدة

---

## 8. متطلبات لوحة الإدارة

### 8.1 Subscription Management Page

**Filament Resource: `SubscriptionResource`**

**القائمة (Table):**
- user_id → link للمستخدم
- plan (badge ملوّن)
- billing_cycle (monthly / annual)
- status (badge: active/grace/cancelled/expired)
- is_founder (badge)
- founder_seat_number
- current_period_end (date)
- amount_paid (مبلغ + عملة)

**Actions على كل سطر:**
- `ExtendSubscriptionAction` — تمديد X أشهر/سنوات مع سبب (يُسجَّل في audit log)
- `CancelSubscriptionAction` — إلغاء فوري أو في نهاية الدورة
- `ChangeToFounderAction` — منح مقعد مؤسسين يدوياً (مع confirmation + سبب)
- `RevokeFounderStatusAction` — سحب مقعد المؤسسين مع سبب
- `DowngradePlanAction` — تخفيض يدوي للخطة
- `ViewAuditLogAction` — عرض سجل التغييرات

**Filters:**
- plan, status, is_founder, billing_cycle, date range

---

### 8.2 Founder Seats Tracking Page

**Filament Page: `FounderSeatsPage`**

**Header Stats:**
- مقاعد مستخدمة: X / 100
- مقاعد متبقية: Y
- نسبة الإشغال: Z%

**جدول المقاعد:**
- seat_number (مرتّب تصاعدياً)
- user (link)
- plan + billing_cycle
- assigned_at
- is_active (badge)
- released_at (إذا ألغي)

**Actions:**
- `AssignManualFounderSeatAction` — للحالات الاستثنائية مع موافقة إدارية + سبب

---

### 8.3 Pricing Management Page

**Filament Page: `PricingManagementPage`**

يعرض الأسعار الحالية من `pricing_configs` table وجدول `billing.php` config.

**Sections:**
- الأسعار الرسمية الحالية (USD) — monthly / annual
- أسعار المؤسسين — monthly / annual
- معدلات الصرف (SAR / JOD / ILS) — تعديل يدوي

**Actions:**
- `UpdateExchangeRatesAction` — تحديث المعادلات بدون نشر كود
- `PreviewPricingPageAction` — معاينة كيف ستظهر الأسعار

**⚠️ قيد:** تغيير الأسعار الأساسية يتطلب migration جديد (مع audit trail)، لا تعديل مباشر من الـ UI.

---

### 8.4 Plan Limit Overrides Page

**Filament Resource: `PlanLimitOverrideResource`**

لمنح مستخدم بعينه حداً أعلى (مثال: عميل VIP يحتاج 10 عملاء على الـ Starter).

**أعمدة:**
- user (searchable)
- resource (clients / projects / invoices / quotes / transactions / storage / team_members)
- limit_value
- reason (text)
- granted_by (admin)
- expires_at (nullable)

**في `FeatureGateService`:**
قبل تطبيق حد الـ plan → تحقق من `PlanLimitOverride` للمستخدم أولاً.

---

### 8.5 Subscription Audit Log Page

**Filament Resource: `SubscriptionAuditLogResource`** (read-only)

يُسجَّل تلقائياً عند:
- تفعيل اشتراك
- إلغاء اشتراك
- تمديد اشتراك
- تغيير خطة
- منح/سحب مقعد مؤسسين
- تجاوز حد الاستخدام

**أعمدة:**
- subscription_id
- action (string)
- old_values (JSON expandable)
- new_values (JSON expandable)
- performed_by (user/system/admin)
- ip_address
- created_at

---

## 9. متطلبات الموقع التسويقي

### 9.1 `/pricing` — المتطلبات الكاملة

#### 9.1.1 Hero Section

- عنوان: "التسعير البسيط للمستقل الجاد"
- وصف: "ابدأ مجاناً. ترقّ عندما تنمو."
- لا toggle في الـ hero — فقط anchor للـ pricing section

---

#### 9.1.2 Annual/Monthly Toggle

- **الحالة الافتراضية:** Annual (يُشجَّع الـ annual)
- يظهر badge: "وفّر 24%" على Annual
- عند التبديل: الأسعار تتغير بـ JS بدون reload
- الـ prices تُقرأ من data attributes على الـ DOM لا من JS hardcoded

**البيانات المطلوبة في الـ blade:**
```blade
data-monthly-pro="{{ $prices['pro']['monthly'] }}"
data-annual-pro="{{ $prices['pro']['annual'] }}"
data-monthly-business="{{ $prices['business']['monthly'] }}"
data-annual-business="{{ $prices['business']['annual'] }}"
```

---

#### 9.1.3 Founder Badge + Seat Counter

يظهر فقط إذا `$seatsRemaining > 0`:

```
┌─────────────────────────────────────────┐
│ 🔥 عرض المؤسسين — متبقي [X] مقعداً فقط │
└─────────────────────────────────────────┘
```

- الـ Counter يُعرض من Cache (30 ثانية TTL)
- يُخفى تماماً إذا `$seatsRemaining == 0`
- إذا `$seatsRemaining <= 10`: يتحول لـ warning أحمر

---

#### 9.1.4 بطاقات الخطط (Pricing Cards)

**Starter (البداية):**
- السعر: $0 — مجاناً للأبد
- CTA: "ابدأ مجاناً" → `{{ route('register') }}`
- قائمة ميزات: 5 عملاء، 3 مشاريع، 50 معاملة/شهر، 5 فواتير/شهر، WhatsApp Sharing

**Pro (الاحترافي):**
- Badge: "⭐ الأكثر طلباً"
- الأسعار (تتغير بالـ toggle):
  - Annual: $13/شهر (يُدفع $156 سنوياً)
  - Monthly: $17/شهر
- إذا Founder active:
  - Annual Founder: $8/شهر (يُدفع $96 سنوياً)
  - Monthly Founder: $10/شهر
- CTA إذا غير مسجّل: "ابدأ الاحترافي" → `{{ route('register') }}?plan=pro`
- CTA إذا مسجّل: "ترقّ الآن" → `{{ route('billing.checkout') }}`
- معادلات: ≈ 49 SAR / ≈ 9.3 JOD / ≈ 48 ILS (للـ annual الرسمي)

**Business (الأعمال):**
- الأسعار:
  - Annual: $34/شهر
  - Monthly: $45/شهر
- إذا Founder: $21/شهر annual / $26/شهر monthly
- CTA مشابه للـ Pro
- معادلات: ≈ 127 SAR / ≈ 24 JOD / ≈ 126 ILS

---

#### 9.1.5 جدول المقارنة

مجموعات:
1. العملاء والـ CRM (6 صفوف)
2. المشاريع (5 صفوف)
3. المعاملات (5 صفوف)
4. الفواتير وعروض الأسعار (8 صفوف)
5. WhatsApp (4 صفوف — مع تمييز واضح بين Basic و Automation)
6. الفريق (5 صفوف)
7. التقارير والمالية (7 صفوف)
8. التكاملات والـ API (4 صفوف)

**التنسيق:** ✅ = متاح، — = غير متاح، رقم = حد محدد

---

#### 9.1.6 Trust Badges

- ✅ ابدأ مجاناً — لا بطاقة ائتمان
- ✅ لا عقود — ألغِ في أي وقت
- ✅ دعم 7 أيام / أسبوع
- ✅ بيانات آمنة ومشفّرة

---

#### 9.1.7 FAQ Accordion

أسئلة مقترحة:
1. "هل الخطة المجانية مجانية فعلاً للأبد؟" — نعم، بلا مدة زمنية
2. "ما هي أسعار المؤسسين؟" — فقط إذا `$seatsRemaining > 0`
3. "هل يمكنني الترقية أو الخفض في أي وقت؟" — الترقية نعم، الخفض عند نهاية الدورة
4. "بأي عملة تتم الفوترة؟" — بالدولار الأمريكي
5. "هل هناك ضمان استرداد؟" — 30 يوماً من أول دفع
6. "ما الفرق بين WhatsApp Basic و Automation؟"

---

#### 9.1.8 CTA نهائي

- عنوان: "جاهز لبدء عملك احترافياً؟"
- زر أساسي: "ابدأ مجاناً الآن" → register
- زر ثانوي: "تحدث معنا" → WhatsApp / contact

---

### 9.2 قواعد تقنية للموقع التسويقي

- الأسعار لا تُكتب hard-coded في الـ blade — تُمرَّر من `PricingController` أو `ViewServiceProvider`
- `$seatsRemaining` يأتي من `Cache::get('founder_seats_remaining')`
- الـ annual/monthly toggle يعمل بـ vanilla JS — لا Alpine dependency إذا ممكن
- الصفحة يجب أن تعمل بدون JS (أسعار Annual تظهر افتراضياً)

---

## 10. متطلبات منطقة الفوترة

### 10.1 `/billing` — الصفحة الرئيسية

**المشكلة الحالية:** يعرض SAR فقط، بدون Annual/Monthly، بدون Founder info.

**التحديثات المطلوبة:**

**Header — الخطة الحالية:**
```
┌─────────────────────────────────────┐
│ خطتك الحالية: [Pro]                │
│ نوع الاشتراك: [شهري / سنوي]        │
│ ينتهي في: [DD/MM/YYYY]             │
│ [إذا founder]: 🏆 مشترك مؤسسين     │
└─────────────────────────────────────┘
```

**إذا Grace Period:**
```
┌─────────────────────────────────────────────────────┐
│ ⚠️ اشتراكك منتهٍ. لديك X أيام لتجديده.            │
│ [جدّد الآن] — يحتفظ بنفس الخطة                     │
└─────────────────────────────────────────────────────┘
```

**عرض الأسعار:**
- USD أساساً مع معادل SAR ثانوياً
- يعرض الـ annual و monthly بجانب بعض
- badge "وفّر 24% مع الاشتراك السنوي" مميّز

**بطاقات الخطط:**
- تُظهر الـ plan الحالية كـ "active" مع border
- تُظهر Founder badge إذا `is_founder`

---

### 10.2 `/billing/upgrade` — صفحة الترقية

**التحديثات المطلوبة:**

- **الأسعار:** تحويل من SAR إلى USD (مع SAR كثانوي)
- **Toggle:** Annual/Monthly مع توفير سنوي واضح
- **Founder Section:** يظهر فقط إذا `$isFounderEligible && $seatsRemaining > 0`

```
┌──────────────────────────────────────────────────────┐
│ 🏆 أنت مؤهل لسعر المؤسسين!                          │
│ متبقي [X] مقعداً فقط من 100                         │
│ احجز مقعدك بـ $8/شهر (بدلاً من $13/شهر) — للأبد    │
└──────────────────────────────────────────────────────┘
```

- **إذا Togo غير متاح:** fallback لـ WhatsApp (الوضع الحالي يبقى)
- **إذا Togo متاح:** زر "ادفع الآن" → `billing.checkout` مع plan + cycle

---

### 10.3 `/billing/subscription` — صفحة تفاصيل الاشتراك (جديدة)

**معلومات تُعرض:**
- الخطة الحالية
- دورة الفوترة (Monthly/Annual)
- تاريخ بداية الدورة الحالية
- تاريخ انتهاء الدورة
- المبلغ المدفوع (بالدولار)
- حالة الاشتراك
- Founder status + رقم المقعد

**Actions:**
- "تجديد" (إذا منتهٍ أو في grace period)
- "إلغاء الاشتراك" (مع تأكيد وتوضيح ماذا سيخسر)

---

### 10.4 `/billing/invoices` — فواتير الاشتراك (جديدة — مستقبلية)

عرض سجل المدفوعات السابقة.
يعمل بعد ربط Togo بالكامل.
في المرحلة الحالية: Admin يُرسل إيصالات يدوياً.

---

## 11. تجربة الترقية

### 11.1 Hard Limits vs Soft Limits

| النوع | التعريف | مثال | السلوك |
|-------|---------|------|--------|
| **Hard Limit** | يمنع الإجراء كلياً | وصل لـ 5 عملاء → لا يستطيع إنشاء عميل 6 | رسالة + redirect لـ upgrade |
| **Soft Limit** | يُكمل الإجراء مع تحذير | 80% من التخزين مُستخدَم | banner تحذير + زر upgrade |
| **Feature Gate** | الميزة غير متاحة لهذه الخطة | Starter يحاول Export | lock icon + upgrade prompt |

---

### 11.2 Upgrade Modals

**Trigger:** أي حد hard أو feature gate.

**مكونات الـ Modal:**
```
┌─────────────────────────────────────────┐
│ 🚀 ترقّ وافتح هذه الميزة              │
│                                         │
│ [Feature Name] متاح في خطة [Pro/Business]
│                                         │
│ ما الذي ستحصل عليه:                    │
│ ✅ ميزة 1                              │
│ ✅ ميزة 2                              │
│ ✅ ميزة 3                              │
│                                         │
│ [ترقّ للاحترافي — $13/شهر] [لاحقاً]   │
└─────────────────────────────────────────┘
```

**قواعد:**
- يظهر مرة واحدة لنفس الـ gate في الجلسة (session-based dismissal)
- يُرسَل event لـ analytics: `upgrade_modal_shown({gate, plan, from})`
- زر "ترقّ" يُحيل لـ `/billing/upgrade?feature={gate}`

---

### 11.3 Notifications

**Banner داخل التطبيق (persistent):**
- يظهر عند 80% من الحد: "تقترب من حد [المورد]. ترقّ الآن"
- يظهر عند 100%: "وصلت للحد الأقصى لـ [المورد]"
- يظهر في Grace Period: "اشتراكك منتهٍ. جدّد خلال X أيام"

---

### 11.4 Emails

**Email 1 — 7 أيام قبل انتهاء الاشتراك:**
- Subject: "اشتراكك في دراهم ينتهي قريباً"
- يتضمن: تاريخ الانتهاء، زر التجديد

**Email 2 — يوم انتهاء الاشتراك (Grace Period Start):**
- Subject: "اشتراكك انتهى — لديك 7 أيام للتجديد"
- يتضمن: ما الذي سيُحجب، كيفية التجديد

**Email 3 — اليوم الأخير من Grace Period:**
- Subject: "⚠️ اشتراكك سيُلغى غداً — تصرّف الآن"
- يتضمن: زر تجديد واضح وبارز

**Email 4 — بعد الانتهاء الكامل (Downgrade إلى Starter):**
- Subject: "تم تحويل حسابك للخطة المجانية"
- يتضمن: ما تم الاحتفاظ به، كيفية الترقية للعودة

---

### 11.5 رحلات المستخدم

**رحلة 1: مستخدم Starter وصل لـ 5 عملاء**

```
يحاول إنشاء العميل السادس
          ↓
ClientController@store → CheckSubscriptionLimits
          ↓
flash session: upgrade_prompt
          ↓
redirect()->back() مع رسالة: "وصلت للحد الأقصى — ترقّ للاحترافي"
          ↓
Blade: يعرض الرسالة + زر "ترقّ الآن" → /billing/upgrade?feature=clients
          ↓
/billing/upgrade → يعرض Founder offer إذا متاح
          ↓
اختيار الخطة والدفع → تفعيل فوري → عودة للعملاء
```

**رحلة 2: Pro يحاول استخدام WhatsApp Automation**

```
Pro يضغط على "تفعيل تذكيرات واتساب التلقائية"
          ↓
FeatureGateService::require('whatsapp_automation')
          ↓
FeatureNotAvailableException
          ↓
Livewire: dispatch('open-upgrade-modal', {gate: 'whatsapp_automation', upgrade_to: 'business'})
          ↓
Modal: "أتمتة واتساب — متاحة في خطة الأعمال ($34/شهر)"
          ↓
[ترقّ للأعمال] → /billing/upgrade?plan=business
```

**رحلة 3: Starter يحاول تصدير Excel**

```
يضغط زر "تصدير Excel"
          ↓
زر معطّل (disabled) مع lock icon 🔒
          ↓
tooltip: "متاح في خطة الاحترافي"
          ↓
النقر على الزر المعطّل → يفتح upgrade modal
          ↓
[ترقّ للاحترافي] أو [لاحقاً]
```

---

## 12. المخاطر التقنية

### 12.1 مخاطر التنفيذ

| الخطر | الاحتمالية | الأثر | خطة التخفيف |
|-------|-----------|-------|------------|
| SubscriptionPlan Enum يُكسر عند الإضافة | منخفض | عالٍ | تغييرات additive فقط، backward compatible |
| حدود الاستخدام تُطبَّق بشكل خاطئ | متوسط | عالٍ | Unit tests لكل limit + integration tests |
| Grace Period لا تُطبَّق بسبب Job failure | متوسط | عالٍ | Job مع retry + monitoring + manual fallback |
| FeatureGate يُبطئ الـ requests | منخفض | متوسط | Cache نتائج الـ gate per user per session |

---

### 12.2 مخاطر الفوترة

| الخطر | الاحتمالية | الأثر | خطة التخفيف |
|-------|-----------|-------|------------|
| الدفع ينجح في Togo لكن الـ callback يفشل | متوسط | عالٍ | Logging + Admin manual activation + user contact form |
| مستخدم يدفع مرتين لنفس الخطة | منخفض | متوسط | `updateOrCreate` بـ idempotency key |
| Togo لا يُرسل transaction_id | موجود حالياً (bug) | عالٍ | تتبع بـ session order_id |
| تناقض أسعار بين الـ pages | متوسط | عالٍ | مصدر واحد للأسعار: `PricingConfig` model |

---

### 12.3 مخاطر سلامة البيانات

| الخطر | الأثر | خطة التخفيف |
|-------|-------|------------|
| مستخدم يتجاوز حده بسبب race condition (مثلاً: طلبان في نفس الوقت) | متوسط | `lockForUpdate()` أو atomic increment |
| حذف user مع founder_seat نشط | عالٍ | cascade FK + audit log |
| تغيير plan بدون تحديث founder_seat | عالٍ | FounderPricingService يُحدَّث دائماً في نفس DB transaction |
| Storage count غير دقيق بعد حذف ملفات | منخفض | job ليلي للمطابقة + recalculate |

---

### 12.4 مخاطر إساءة الاشتراك

| الخطر | الحماية |
|-------|---------|
| حسابات مزيفة لحجز Founder seats | الدفع الفعلي شرط إلزامي لتعيين المقعد |
| Transfer الـ founder seat لحساب آخر | seat مرتبط بـ user_id — لا يقبل التحويل |
| إلغاء واشتراك بنفس الفترة الشهرية لتجنب الدفع | Grace period: الـ seat يُحرَّر فقط بعد Grace |
| استغلال Grace Period كـ free extension | Grace 7 أيام صارمة — ExpireJob لا يقبل التأجيل |

---

## 13. خارطة التطوير

### Phase 1 — Foundation (الأساس)

**الغرض:** تحديث الـ Enum والـ Models لدعم الاستراتيجية الجديدة.

**Tasks:**
- [ ] تحديث `SubscriptionPlan` Enum: تصحيح الحدود + إضافة جميع الـ methods الجديدة
- [ ] تشغيل migrations: subscriptions (أعمدة جديدة) + users (storage + expiry)
- [ ] إنشاء `FounderSeat` model
- [ ] إنشاء `PlanLimitOverride` model
- [ ] إنشاء `SubscriptionAuditLog` model
- [ ] تحديث `SubscriptionService::activatePlan()` لدعم `billing_cycle` + `amount_paid` + `current_period_*`
- [ ] تحديث `getPlanPrices()` لـ USD-first + annual/monthly
- [ ] تحديث `config/billing.php` لـ USD amounts
- [ ] إنشاء `Subscription::scopeGracePeriod()` + `scopeExpired()`
- [ ] إنشاء `ExpireSubscriptionsJob`
- [ ] إنشاء `SendGraceWarningJob`

**Dependencies:** لا شيء — يمكن البدء فوراً.

**التعقيد:** متوسط

**Launch Blocker:** نعم — كل الـ phases تعتمد على هذا.

---

### Phase 2 — Feature Gates (بوابات الميزات)

**الغرض:** بناء نظام الـ feature gates المركزي.

**Tasks:**
- [ ] إنشاء `FeatureGateService`
- [ ] إضافة `can(string $gate): bool` على `SubscriptionPlan` Enum
- [ ] إنشاء `RequireFeature` middleware
- [ ] إنشاء `FeatureNotAvailableException`
- [ ] تسجيل Exception في `Handler.php` (web + API responses)
- [ ] إنشاء Blade directive `@feature` / `@endfeature`
- [ ] تطبيق Gates على: Wallets، Clients (advanced CRM)، Reports، Export، Client Portal
- [ ] تطبيق Gates على: WhatsApp Automation، API routes
- [ ] تطبيق Gates على: Invoice features (email، templates، recurring، ZATCA)
- [ ] UI: Lock icons على الميزات المحجوبة
- [ ] UI: Upgrade modal component (Blade/Livewire)

**Dependencies:** Phase 1

**التعقيد:** عالٍ

**Launch Blocker:** نعم

---

### Phase 3 — Billing (الفوترة)

**الغرض:** تحديث منظومة الفوترة لـ USD + Annual/Monthly + Togo complete.

**Tasks:**
- [ ] إنشاء `pricing_configs` migration + seed
- [ ] تحديث `BillingController::checkout()` لقبول `cycle` (monthly/annual)
- [ ] تحديث `BillingController::index()` لعرض USD + cycle
- [ ] تحديث `billing/index.blade.php` لـ USD-first + Annual/Monthly cards
- [ ] تحديث `billing/upgrade.blade.php` لـ USD + cycle toggle + Founder section
- [ ] إنشاء `billing/subscription.blade.php` (صفحة تفاصيل الاشتراك)
- [ ] تطبيق Grace Period banner في layouts
- [ ] إضافة scheduled emails (7-day warning، grace day 1، grace day 7، expiry)

**Dependencies:** Phase 1 + Phase 2

**التعقيد:** عالٍ

**Launch Blocker:** نعم

---

### Phase 4 — Founder Pricing (أسعار المؤسسين)

**الغرض:** بناء نظام Founder Seats كامل.

**Tasks:**
- [ ] تشغيل `create_founder_seats_table` migration
- [ ] إنشاء `FounderPricingService`
- [ ] `getSeatsRemaining()` مع Cache
- [ ] `isFounderEligible()` مع كل الـ checks
- [ ] `assignSeat()` مع `lockForUpdate()` + DB transaction
- [ ] `releaseSeat()` عند الإلغاء
- [ ] ربط `assignSeat()` في `SubscriptionService::activatePlan()`
- [ ] ربط `releaseSeat()` في `SubscriptionService::cancelPlan()`
- [ ] تحديث `billing/upgrade.blade.php` — Founder section
- [ ] تحديث `marketing/pricing.blade.php` — Founder badge + counter
- [ ] إنشاء `UpdateFounderSeatsCacheJob`
- [ ] Fraud prevention checks
- [ ] Filament: `FounderSeatsPage`

**Dependencies:** Phase 1 + Phase 3

**التعقيد:** عالٍ جداً

**Launch Blocker:** نعم — يجب أن يكون جاهزاً قبل أي تسويق للـ Founder offer.

---

### Phase 5 — UX & Upgrade Flows (تجربة المستخدم)

**الغرض:** تكامل كل تجارب الترقية.

**Tasks:**
- [ ] Usage limit counters في كل صفحة ذات صلة (Clients، Projects، Invoices...)
- [ ] `CheckSubscriptionLimits`: إضافة clients، invoices، quotes، storage، team
- [ ] Hard limit UI: disabled buttons + lock icons
- [ ] Soft limit UI: warning banners عند 80%
- [ ] Upgrade modal component كامل
- [ ] `FeatureGateService` يُعيّن رسائل Upgrade مُخصَّصة per gate
- [ ] Livewire dispatch events للـ modals
- [ ] Session-based modal dismissal (لا يتكرر في نفس الجلسة)

**Dependencies:** Phase 2 + Phase 3

**التعقيد:** متوسط

**Launch Blocker:** جزئياً — Hard limits ضرورية، Soft limits اختيارية للإطلاق

---

### Phase 6 — Launch Readiness (جاهزية الإطلاق)

**الغرض:** المتطلبات الأخيرة قبل الإطلاق.

**Tasks:**
- [ ] إعادة بناء `/marketing/pricing.blade.php` بالكامل
- [ ] Filament: `SubscriptionResource` + `FounderSeatsPage` + `PricingManagementPage`
- [ ] `SubscriptionAuditLog`: تسجيل كل التغييرات
- [ ] `PlanLimitOverride` Resource في Filament
- [ ] Unit tests لكل الـ limits (100% coverage)
- [ ] Integration test لـ Founder seat assignment + race condition
- [ ] Integration test لـ Grace Period flow
- [ ] `ExpireSubscriptionsJob` + `SendGraceWarningJob` في `Kernel`
- [ ] Monitoring: alert إذا Job فشل
- [ ] توثيق `docs/PRICING-IMPLEMENTATION-PLAN.md` (هذا الملف)

**Dependencies:** جميع الـ Phases السابقة

**التعقيد:** متوسط

**Launch Blocker:** نعم

---

### ملخص التعقيد والأولويات

| Phase | التعقيد | المدة التقديرية | Launch Blocker |
|-------|---------|----------------|----------------|
| 1 — Foundation | متوسط | 3-5 أيام | ✅ نعم |
| 2 — Feature Gates | عالٍ | 5-8 أيام | ✅ نعم |
| 3 — Billing | عالٍ | 4-6 أيام | ✅ نعم |
| 4 — Founder Pricing | عالٍ جداً | 5-7 أيام | ✅ نعم |
| 5 — UX Flows | متوسط | 4-6 أيام | جزئياً |
| 6 — Launch Readiness | متوسط | 3-5 أيام | ✅ نعم |
| **الإجمالي** | | **24-37 يوم** | |

---

## 14. قائمة التحقق النهائية

### 14.1 قاعدة البيانات

- [ ] migration: أعمدة جديدة على `subscriptions`
- [ ] migration: `founder_seats` table
- [ ] migration: `plan_limit_overrides` table
- [ ] migration: `subscription_audit_logs` table
- [ ] migration: `pricing_configs` table + seed
- [ ] migration: `used_storage_bytes` + `subscription_expires_at` على `users`
- [ ] migration: `status` ENUM في `subscriptions` يتضمن 'grace_period'
- [ ] جميع الـ indexes مُنشأة
- [ ] Relationships مُعرَّفة في Models

### 14.2 Backend / Business Logic

- [ ] `SubscriptionPlan` Enum: جميع الـ methods الجديدة (maxClients، maxInvoices، maxQuotes، maxStorageMB، maxTeamMembers، can($gate))
- [ ] `SubscriptionPlan` Enum: تصحيح القيم (maxProjects=3، maxTransactionsPerMonth Pro=1000)
- [ ] `FeatureGateService`: check()، require()، getBlockedMessage()، getUpgradePlan()
- [ ] `FounderPricingService`: isFounderEligible()، assignSeat()، releaseSeat()، getSeatsRemaining()
- [ ] `SubscriptionService`: activatePlan() مع billing_cycle + founder + amounts
- [ ] `SubscriptionService`: cancelPlan() مع releaseSeat() + audit log
- [ ] `CheckSubscriptionLimits`: clients، projects، invoices، quotes، transactions، storage، team
- [ ] `RequireFeature` middleware مع web/API responses
- [ ] `FeatureNotAvailableException` في Handler
- [ ] `ExpireSubscriptionsJob`: يعمل يومياً + retry + logging
- [ ] `SendGraceWarningJob`: day 1، day 5، day 7 + retry
- [ ] Audit logging: كل تغيير في الاشتراك

### 14.3 Founder Pricing

- [ ] `lockForUpdate()` في `assignSeat()` لمنع race conditions
- [ ] مقعد لا يُعيَّن قبل تأكيد الدفع
- [ ] مقعد لا يُعاد لنفس المستخدم بعد الإلغاء
- [ ] `founder_seats_remaining` في Cache بـ TTL 30 ثانية
- [ ] عداد المقاعد في الصفحة التسويقية ديناميكي (من Cache)
- [ ] Admin can override manual seat assignment مع سبب موثّق

### 14.4 الصفحة التسويقية `/pricing`

- [ ] Annual/Monthly toggle يعمل
- [ ] أسعار USD-first (لا SAR)
- [ ] معادلات SAR/JOD/ILS ثانوية
- [ ] Founder badge يظهر فقط إذا `seatsRemaining > 0`
- [ ] عداد المقاعد المتبقية ديناميكي
- [ ] جميع الـ CTAs تعمل (لا href="#")
- [ ] CTA "ابدأ مجاناً" → register
- [ ] CTA "ترقّ" → billing.checkout مع plan + cycle
- [ ] جدول المقارنة كامل بجميع الميزات
- [ ] WhatsApp Basic vs Automation موضّح بوضوح
- [ ] FAQ يتضمن سؤال الـ Founder Pricing
- [ ] الصفحة تعمل بدون JS (server-side defaults)

### 14.5 منطقة الفوترة

- [ ] `/billing` يعرض USD (لا SAR فقط)
- [ ] `/billing` يعرض billing_cycle الحالي
- [ ] `/billing` يعرض Grace Period banner إذا انتهى
- [ ] `/billing/upgrade` يعرض Annual/Monthly toggle
- [ ] `/billing/upgrade` يعرض Founder section إذا eligible
- [ ] `/billing/subscription` موجودة مع التفاصيل الكاملة
- [ ] أسعار متطابقة بين الصفحة التسويقية وصفحة الفوترة

### 14.6 لوحة الإدارة

- [ ] `SubscriptionResource` مع كل الـ actions
- [ ] `FounderSeatsPage` مع header stats
- [ ] `PricingManagementPage` مع exchange rates edit
- [ ] `PlanLimitOverrideResource`
- [ ] `SubscriptionAuditLogResource` (read-only)

### 14.7 الجودة والاختبار

- [ ] Unit tests: كل `SubscriptionPlan` method
- [ ] Unit tests: كل `FeatureGateService` method
- [ ] Unit tests: `FounderPricingService` + race condition test
- [ ] Integration test: كامل upgrade flow (Starter → Pro)
- [ ] Integration test: Grace Period → Expiry
- [ ] Integration test: Founder seat assignment + cancel + re-subscribe
- [ ] Manual test: الصفحة التسويقية على mobile
- [ ] Test: تطابق أسعار الصفحة التسويقية مع الفوترة
- [ ] Test: لا يمكن تجاوز الحدود بـ concurrent requests

### 14.8 التوافق مع V1-FINAL

- [ ] لا توجد أسعار SAR hard-coded (كلها USD)
- [ ] WhatsApp Basic Sharing: متاح لجميع الخطط
- [ ] WhatsApp Automation: Business فقط
- [ ] Enterprise: غير موجود في أي صفحة نشطة
- [ ] Founder limit: 100 مقعد صارمة — لا استثناءات بدون audit log
- [ ] Grandfathering: أي تغيير مستقبلي في الأسعار لا يطبّق على المشتركين الحاليين

---

## مراجع

- `docs/PRICING-STRATEGY-V1-FINAL.md` — المرجع الاستراتيجي
- `app/Support/Enums/SubscriptionPlan.php` — الـ Enum الحالي
- `app/Modules/Billing/Services/SubscriptionService.php` — خدمة الاشتراكات
- `app/Http/Middleware/CheckSubscriptionLimits.php` — Middleware الحالي
- `app/Modules/Billing/Services/TogoPaymentService.php` — بوابة الدفع
- `config/billing.php` — إعدادات الفوترة
- `database/migrations/2026_05_12_000008_create_subscriptions_table.php`
- `resources/views/billing/index.blade.php` — صفحة الفوترة الحالية
- `resources/views/billing/upgrade.blade.php` — صفحة الترقية الحالية
- `resources/views/marketing/pricing.blade.php` — الصفحة التسويقية الحالية
- `docs/TOGO-PAYMENT-GATEWAY.md` — توثيق بوابة الدفع

---

*آخر تحديث: 23 يونيو 2026 — الإصدار 1.0*
