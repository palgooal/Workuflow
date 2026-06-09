# دراهم — وثيقة المعمارية التقنية

> Laravel 12 / PHP 8.2 — SaaS Financial Platform  
> آخر تحديث: 9 يونيو 2026 | الإصدار: 3.1.0 (Phase 26 — Communication Layer)

---

## نظرة عامة

**دراهم** منصة SaaS مالية مبنية على Laravel 12 تستهدف المستقلين وأصحاب الأعمال الصغيرة.  
نظام **Multi-tenant** حيث بيانات كل مستخدم معزولة تلقائياً عبر `BelongsToUser` Global Scope.

---

## التقنيات المستخدمة

### Backend

| الحزمة | الإصدار | الاستخدام |
|--------|---------|-----------|
| Laravel | 12.x | Framework الرئيسي |
| PHP | 8.2 | لغة البرمجة |
| MySQL | 8.x | قاعدة البيانات |
| spatie/laravel-permission | ^6 | إدارة الأدوار والصلاحيات |
| filament/filament | ^3 | لوحة الإدارة |
| maatwebsite/excel | ^3 | استيراد/تصدير xlsx |
| laravel/telescope | dev | مراقبة وتتبع الطلبات |

### Frontend

| التقنية | الاستخدام |
|---------|-----------|
| Blade Templates | قوالب الواجهة |
| Tailwind CSS v4 | التنسيق |
| Alpine.js | التفاعلات بدون reload |
| Chart.js | الرسوم البيانية |
| Sortable.js | Drag & Drop للوسوم |
| Tajawal Font | خط عربي احترافي |

### Testing

| الأداة | النتيجة |
|--------|---------|
| Pest PHP | 54 / 54 ✅ |

---

## مبدأ التصميم

```
Controller → Form Request → DTO → Action → Service → Model
```

- **Controller**: يستقبل الطلب، يُفوّض للـ Action، يُعيد الاستجابة
- **Form Request**: يتحقق من صحة البيانات (Arabic validation messages)
- **DTO**: Data Transfer Object — ينقل البيانات بين الطبقات بأمان
- **Action**: منطق عملية واحدة محددة — قابل للاختبار والإعادة
- **Service**: منطق أعمال معقد يُستخدم عبر أكثر من Action
- **Model**: البيانات والعلاقات فقط (لا منطق أعمال)

---

## نمط العزل متعدد المستأجرين (Multi-tenancy)

```php
// BelongsToUser Trait — يُطبَّق على كل Model
trait BelongsToUser {
    protected static function bootBelongsToUser(): void {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where($builder->getModel()->getTable() . '.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }
}
```

**القاعدة:** كل Model يستخدم هذا الـ Trait لا يمكن لمستخدم الوصول لبيانات مستخدم آخر — حتى لو عرف الـ ID.

---

## هيكل المجلدات الكامل

```
app/
├── Console/Commands/
│   ├── ProcessRecurringTransactions.php   ← Scheduler 01:00 يومي
│   ├── SendDebtAlerts.php                 ← Scheduler 08:00 يومي
│   └── Crm/
│       ├── RecalculateClientHealthCommand.php
│       ├── RefreshClientSegmentsCommand.php
│       └── ReconcileClientStatsCommand.php
│
├── Filament/
│   ├── Resources/
│   │   ├── UserResource.php               ← CRUD + Suspend/Activate/ResetPlan/SendEmail/DeleteData/Impersonate
│   │   SubscriptionResource.php
│   │   └── TransactionResource.php
│   └── Widgets/
│       ├── StatsOverviewWidget.php        ← MRR, ARR, Churn
│       └── UsersChartWidget.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── ImpersonateController.php  ← impersonate() + leave()
│   │   ├── Auth/
│   │   ├── BillingController.php
│   │   ├── BudgetController.php
│   │   ├── CategoryController.php
│   │   ├── DashboardController.php
│   │   ├── DebtController.php
│   │   ├── InvoiceController.php          ← CRUD + markSent + markPaid + cancel
│   │   ├── OnboardingController.php
│   │   ├── ProjectController.php
│   │   ├── QuoteController.php            ← CRUD + markSent + convert + portal
│   │   ├── RecurringController.php
│   │   ├── ReportController.php
│   │   ├── ServiceController.php
│   │   ├── SettingsController.php
│   │   ├── TeamMemberController.php
│   │   └── TransactionController.php
│   │
│   └── Middleware/
│       ├── EnsureUserIsActive.php
│       └── EnsureSubscriptionActive.php
│
├── Models/
│   ├── Budget.php
│   ├── Category.php
│   ├── Client.php                         ← BelongsToUser, SoftDeletes, ULID
│   ├── Debt.php
│   ├── Invoice.php                        ← BelongsToUser, SoftDeletes, ULID
│   ├── InvoiceItem.php
│   ├── Project.php
│   ├── Quote.php                          ← BelongsToUser, SoftDeletes, ULID, token
│   ├── QuoteItem.php
│   ├── RecurringTransaction.php
│   ├── Service.php
│   ├── Subscription.php
│   ├── TeamMember.php
│   ├── Transaction.php
│   └── User.php                           ← FilamentUser, HasRoles
│
├── Modules/
│   ├── Billing/Services/SubscriptionService.php
│   ├── Budgets/Actions/ + DTOs/ + Services/
│   ├── Categories/Actions/ + DTOs/
│   ├── CRM/
│   │   ├── Actions/
│   │   │   ├── CreateClientAction.php
│   │   │   ├── UpdateClientAction.php
│   │   │   ├── DeleteClientAction.php
│   │   │   ├── ArchiveClientAction.php
│   │   │   ├── LogClientActivityAction.php
│   │   │   └── ExecuteAutomationAction.php  ← Job: تنفيذ قاعدة أتمتة
│   │   ├── DTOs/
│   │   │   ├── CreateClientDTO.php
│   │   │   ├── UpdateClientDTO.php
│   │   │   └── ClientFiltersDTO.php
│   │   ├── Enums/
│   │   │   ├── ClientStatus.php
│   │   │   ├── ClientSource.php
│   │   │   ├── FollowUpType.php
│   │   │   ├── FollowUpPriority.php
│   │   │   ├── HealthScoreGrade.php
│   │   │   ├── AutomationTrigger.php
│   │   │   └── ClientActivityType.php
│   │   ├── Http/Controllers/
│   │   │   ├── ClientController.php
│   │   │   ├── ClientTagController.php
│   │   │   ├── ClientFollowUpController.php
│   │   │   ├── ClientSegmentController.php
│   │   │   ├── ClientImportController.php
│   │   │   └── AutomationRuleController.php
│   │   ├── Models/                         ← (CRM-specific models in app/Models/)
│   │   ├── Policies/ClientPolicy.php
│   │   ├── Requests/
│   │   │   ├── StoreClientRequest.php
│   │   │   └── UpdateClientRequest.php
│   │   └── Services/
│   │       ├── ClientService.php
│   │       ├── ClientTagService.php
│   │       ├── ClientHealthScoreService.php
│   │       ├── ClientSegmentService.php
│   │       ├── FollowUpService.php
│   │       ├── AutomationRuleEngine.php
│   │       └── SmartTagSuggestionService.php
│   ├── Debts/Actions/ + DTOs/ + Services/
│   ├── Projects/
│   │   ├── Actions/ (Create/Update/Delete)
│   │   ├── DTOs/ProjectData.php
│   │   └── Services/ProjectFinancialService.php  ← per-currency summary
│   ├── Recurring/Actions/ + DTOs/ + Services/
│   ├── Reports/Services/ (CashFlow/MonthlyReport/ProfitLoss/TopCategories)
│   └── Transactions/
│       ├── Actions/ (Create/Update/Delete)
│       ├── DTOs/TransactionData.php
│       └── Services/
│           ├── TransactionService.php     ← per-currency getSummary()
│           └── BalanceCalculatorService.php
│
├── Policies/
│   ├── BudgetPolicy.php
│   ├── CategoryPolicy.php
│   ├── DebtPolicy.php
│   ├── InvoicePolicy.php
│   ├── ProjectPolicy.php
│   ├── QuotePolicy.php
│   ├── RecurringPolicy.php
│   └── TransactionPolicy.php
│
├── Support/
│   ├── Enums/
│   │   ├── DebtStatus.php / DebtType.php
│   │   ├── InvoiceStatus.php              ← draft|sent|paid|overdue|cancelled
│   │   ├── ProjectType.php                ← personal|business
│   │   ├── QuoteStatus.php                ← draft|sent|viewed|accepted|rejected|expired|converted
│   │   ├── RecurringFrequency.php
│   │   ├── SubscriptionPlan.php
│   │   ├── TransactionType.php
│   │   └── UserStatus.php
│   ├── Traits/
│   │   └── BelongsToUser.php              ← Global Scope عزل تلقائي
│   └── Helpers/MoneyFormatter.php

resources/views/
├── layouts/
│   ├── app.blade.php                      ← Sidebar RTL + Impersonation Banner
│   └── auth.blade.php
├── components/                            ← badge, empty-state, stats-card...
├── crm/
│   ├── clients/ (index, create, edit, show)
│   ├── follow-ups/ (index)
│   ├── tags/ (index — Drag & Drop)
│   ├── segments/ (index)
│   └── automation-rules/ (index, create, edit)
├── invoices/ (index, create, edit, show)
├── quotes/ (index, create, edit, show, portal)
├── portal/
│   ├── error.blade.php
│   └── invoices/show.blade.php
├── projects/ (index, show, create, edit)
├── transactions/ (index, create, edit)
└── ... (dashboard, reports, settings, billing, debts...)

routes/
├── web.php                                ← Auth routes + Public portal routes
└── crm.php                                ← CRM module routes

config/
├── billing.php
├── crm.php                                ← health_score weights + recency_bias
└── stripe.php

database/migrations/
├── [core migrations...]
├── 2026_05_xx_create_clients_table.php    ← CRM Phase 1
├── 2026_05_xx_create_invoices_table.php   ← Phase 18
├── 2026_05_xx_create_quotes_table.php     ← Phase 19
├── 2026_05_28_000001_make_transactions_project_id_nullable.php
├── 2026_05_29_000001_fix_quotes_number_unique_per_user.php
└── 2026_05_29_000002_fix_invoices_number_unique_per_user.php
```

---

## قرارات معمارية مهمة

### 1. ULID كمفتاح مسار

**القرار:** كل Model يستخدم ULID (`char(26)`) كـ route key بدلاً من `id` الرقمي التسلسلي.

**السبب:** يمنع enumeration attacks (تخمين IDs بالتسلسل) بينما يحتفظ بخاصية الترتيب الزمني المدمجة في ULID.

**الاستثناء:** `clients` يستخدم `public_id` (ULID) بدلاً من `id` مع `getRouteKeyName() → 'public_id'`.

### 2. Token منفصل لبوابة العميل

**القرار:** `Quote.token` هو 48 حرف Base62 عشوائي — مستقل تماماً عن `ulid`.

**السبب:** الـ ULID له ترتيب زمني مدمج قد يُساعد على التخمين. الـ token عشوائي بالكامل لجعل القسمة العامة `/q/{token}` آمنة تماماً بدون مصادقة.

### 3. Unique Key per-user للترقيم

**القرار:** `quotes.number` و `invoices.number` unique على `(user_id, number)` لا على `number` وحده.

**السبب:** كل مستخدم في SaaS يبدأ ترقيمه من QUO-0001/INV-0001. الـ unique العالمي يُسبب `DuplicateEntry` عند ثاني مستخدم.

### 4. عرض العملات منفصلة لا دمجها

**القرار:** عند وجود معاملات/فواتير بعملات مختلفة، تُعرض مجمّعة per-currency مع بانر تحذيري.

**السبب:** دمج عملات مختلفة بدون سعر صرف محدد يُنتج أرقاماً مُضللة. عرضها منفصلة أكثر أمانة وأمانة للمستخدم.

### 5. إحصائيات العميل: حية لا مخزنة

**القرار:** `ClientController::show()` يحسب `total_revenue/total_paid` من الفواتير مباشرة ويُحدِّث DB إذا تغيّرت.

**السبب:** القيم المخزنة في `clients` table تُصبح قديمة عند أي تغيير في الفواتير. الحل: حساب حي عند الطلب + تحديث DB + تحديث عند كل `markPaid()`.

### 6. SoftDeletes على الوثائق المالية

**القرار:** `quotes`, `invoices`, `transactions`, `clients` جميعها تستخدم `SoftDeletes`.

**السبب:** الوثائق المالية لا تُحذف نهائياً لأغراض التدقيق والمراجعة والمراجعة القانونية.

---

## تدفق المصادقة والأمان

```
HTTP Request
    │
    ▼
[EnsureUserIsActive Middleware] ← يمنع المستخدمين الموقوفين
    │
    ▼
[Auth Middleware (Breeze)]
    │
    ▼
[BelongsToUser Global Scope] ← على كل Model تلقائياً
    │
    ▼
[Policy Check] ← $this->authorize('action', $model)
    │
    ▼
Controller Logic
```

---

## مخطط العلاقات الرئيسية

```
User
 ├── Projects (hasMany)
 │    ├── Transactions (hasMany)
 │    ├── Invoices (hasMany)
 │    ├── Quotes (hasMany)
 │    └── ProjectService (pivot: amount, type, team_member_id)
 │
 ├── Clients (hasMany)
 │    ├── Invoices (hasMany)
 │    ├── Quotes (hasMany)
 │    ├── FollowUps (hasMany)
 │    ├── Activities (hasMany)
 │    └── Tags (belongsToMany)
 │
 ├── Invoices (hasMany)
 │    └── InvoiceItems (hasMany)
 │
 ├── Quotes (hasMany)
 │    └── QuoteItems (hasMany)
 │
 ├── Transactions (hasMany)
 ├── Categories (hasMany)
 ├── Debts (hasMany)
 ├── Budgets (hasMany)
 ├── RecurringTransactions (hasMany)
 └── Subscriptions (hasMany)

Quote → Invoice (via reference = quote.number)
Quote → Project (عند convertToInvoice مع create_project)
```

---

## Scheduler

```php
// routes/console.php
$schedule->command('recurring:process')->dailyAt('01:00');
$schedule->command('debts:send-alerts')->dailyAt('08:00');
$schedule->command('crm:recalculate-health-scores --apply-tags')->dailyAt('02:00');
// Output: storage/logs/crm-health-scores.log

$schedule->command('crm:reconcile-aggregates')->dailyAt('03:00');
$schedule->command('crm:refresh-segments')->dailyAt('03:30');
$schedule->command('crm:detect-inactive')->dailyAt('04:00');              // Sprint 6 — GAP-05
// Output: storage/logs/crm-detect-inactive.log
// Triggers: days_since_contact | health_score_below | invoice_overdue

$schedule->command('crm:send-follow-up-reminders')->everyThirtyMinutes(); // Sprint 6 — GAP-04
// Output: storage/logs/crm-follow-up-reminders.log

$schedule->command('invoices:send-reminders')->dailyAt('09:00');           // Phase 18
// Output: storage/logs/invoice-reminders.log

// Phase 26 — Sprint 1: Queue runner (cPanel-compatible)
$schedule->command('queue:work --queue=emails --max-jobs=50 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

---

## Admin Panel (Filament)

**المسار:** `/admin`  
**الحماية:** `canAccessPanel()` → `hasRole('super_admin') && isActive()`

### Resources

| Resource | الوصف |
|----------|-------|
| UserResource | إدارة المستخدمين + Impersonate |
| SubscriptionResource | عرض الاشتراكات |
| TransactionResource | عرض المعاملات (قراءة فقط) |

### Widgets

| Widget | الوصف |
|--------|-------|
| StatsOverviewWidget | MRR, ARR, Churn Rate |
| UsersChartWidget | نمو المستخدمين |
| RevenueWidget | إيرادات المنصة |
| SystemHealthWidget | Queue/Failed Jobs/Scheduler/Log |

### Admin Impersonation

```
Admin → /admin/users
  → زر "دخول كمستخدم"
  → GET /admin/impersonate/{userId}
  → Session::put('impersonator_id', admin.id)
  → Auth::login($targetUser)
  → redirect → /dashboard (مع شريط أصفر)
  → زر "العودة للأدمن"
  → GET /admin/impersonate-leave
  → Auth::login($admin)
  → redirect → /admin/users
```

---

## إحصاءات المشروع

| المقياس | القيمة |
|---------|--------|
| إجمالي Migrations | ~42 (Phase 26: لا migrations جديدة) |
| إجمالي Models | ~25 |
| إجمالي Controllers | ~30 |
| إجمالي Enums | ~24 (Phase 26 يضيف: NotificationPriority, NotificationType) |
| Mailables | 7 (Phase 26 يضيف: QuoteMail, InvoicePaidMail, QuoteAcceptedMail, QuoteRejectedMail) |
| Jobs | 5 (Phase 26 يضيف: SendInvoiceEmailJob, SendQuoteEmailJob, SendInvoicePaidEmailJob) |
| Email Template Keys | 11 (Phase 26 يضيف 8 قوالب جديدة) |
| اختبارات Pest | 54/54 ✅ (يرتفع مع Phase 26) |
| ملفات Blade | ~80 (Phase 26 يضيف: notification center, widget, whatsapp button) |
| المراحل المكتملة | 25 مرحلة

---

## Phase 26 — Communication Layer

> وثيقة التصميم الكامل: `docs/PHASE-26-COMMUNICATION-LAYER.md`  
> تدقيق البريد الإلكتروني: `docs/EMAIL-SYSTEM-AUDIT.md`

### Sprint 1 & 2 — Email Layer

```
app/Jobs/
├── SendInvoiceEmailJob.php          NEW — queue:'emails', tries:3
├── SendQuoteEmailJob.php            NEW — queue:'emails', tries:3
└── SendInvoicePaidEmailJob.php      NEW — queue:'emails', tries:3

app/Mail/
├── InvoiceMail.php                  UNCHANGED
├── InvoiceReminderMail.php          UPDATED — use EmailTemplate (was hardcoded HTML)
├── WelcomeEmail.php                 UNCHANGED
├── QuoteMail.php                    NEW — Signed URL 30d, EmailTemplate('quote_send')
├── InvoicePaidMail.php              NEW — EmailTemplate('invoice_paid')
├── QuoteAcceptedMail.php            NEW — EmailTemplate('quote_accepted')
└── QuoteRejectedMail.php            NEW — EmailTemplate('quote_rejected')

app/Notifications/
├── InvoiceDueSoonNotification.php   UPDATED — add mail channel + toMail()
├── InvoiceOverdueNotification.php   UPDATED — add mail channel + toMail()
├── DebtDueSoonNotification.php      UPDATED — add mail channel + toMail()
├── DebtOverdueNotification.php      UPDATED — add mail channel + toMail()
└── FollowUpReminderNotification.php UPDATED — add mail channel + toMail()

database/seeders/
└── EmailTemplateSeeder.php          NEW — 8 new template keys

Email Template Keys (Phase 26):
├── invoice_reminder
├── invoice_paid
├── quote_send
├── quote_accepted
├── quote_rejected
├── debt_due_soon
├── debt_overdue
└── follow_up_reminder

No new DB migrations — uses existing notifications + email_templates tables
```

### Sprint 3 — Notification Center

```
app/Support/Enums/
├── NotificationPriority.php         NEW — Low/Medium/High/Critical + color() + badgeClass()
└── NotificationType.php             NEW — Success/Warning/Error/Info + icon() + bgClass()

app/Http/Controllers/
└── NotificationController.php       UPDATED — add index(), markRead(), markAllRead(),
                                              archive(), destroy()

resources/views/notifications/
└── index.blade.php                  NEW — Notification Center page with filters

resources/views/components/
├── notification-bell.blade.php      UPDATED — priority color badges
├── notification-item.blade.php      NEW — reusable single notification row
└── notification-widget.blade.php    NEW — dashboard widget (last 5 + unread count)

Priority Assignment:
  invoice_overdue  → Critical / Error
  debt_overdue     → Critical / Error
  invoice_due_soon → High / Warning
  debt_due_soon    → High / Warning
  follow_up        → Medium / Info
  automation       → Medium / Info
```

### Sprint 4 — WhatsApp Quick Actions

```
app/Support/
└── WhatsAppLinkGenerator.php        NEW — forInvoice(), forQuote(), forClient()
                                          formatPhone(), buildUrl(), encode()
                                          Returns null if no phone — no broken buttons

resources/views/components/
└── whatsapp-button.blade.php        NEW — bg:#25D366, inline SVG, target:_blank
                                          hidden if url === null

Integration points:
  invoices/show.blade.php            ← x-whatsapp-button
  invoices/index.blade.php           ← per-row icon button
  quotes/show.blade.php              ← x-whatsapp-button
  quotes/index.blade.php             ← per-row icon button
  clients/show.blade.php             ← contact section button
  clients/index.blade.php            ← per-row quick action

wa.me URL format:
  https://wa.me/{phone}?text={rawurlencode($message)}
  Phone: strip non-digits, prepend country code if missing

NO: API, DB, Meta API, Twilio, webhooks, storage
```

### مبدأ التصميم — Phase 26

```
HTTP Request
  └─► Controller → dispatch(EmailJob)
                        └─► (queue:emails) → Mailable
                                               └─► EmailTemplate::render(key, vars)
                                                     └─► emails.template Blade wrapper

Scheduler
  └─► Command → Notification::send(user, XxxNotification)
                   └─► via: ['database', 'mail']
                         ├─► toArray() — standardized data with priority + category
                         └─► toMail() — EmailTemplate::render(key, vars)

WhatsApp
  └─► View → WhatsAppLinkGenerator::forInvoice($invoice)
               └─► wa.me URL (no server-side state)
```
