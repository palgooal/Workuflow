# Phase 26 — Communication Layer
> **Version:** 1.0.0 | **Date:** 9 Jun 2026  
> **Status:** 📋 Approved — Ready for Implementation  
> **Authored by:** Lead SaaS Architect · Laravel 12 Technical Lead · Product Manager · CRM Architect · CTO

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Business Value Analysis](#2-business-value-analysis)
3. [Architecture Design](#3-architecture-design)
4. [Database Impact](#4-database-impact)
5. [Laravel Implementation Plan](#5-laravel-implementation-plan)
6. [UI/UX Plan](#6-uiux-plan)
7. [Updated Roadmap](#7-updated-roadmap)
8. [Sprint Breakdown](#8-sprint-breakdown)
9. [Estimated Development Hours](#9-estimated-development-hours)
10. [Risks and Mitigation](#10-risks-and-mitigation)

---

## 1. Executive Summary

### Context

Darahum has completed 25 phases of product development. The core financial engine is production-ready: projects, transactions, invoices, quotes, CRM, debts, reports, and PDF generation all work correctly. The platform is technically stable.

**The gap before the first paying customer is not features — it is communication.**

When a freelancer uses Darahum today:
- They can create an invoice but the client gets no professional notification
- They can send a quote but the client receives no email
- They get debt alerts only if they are logged in (database-only notifications)
- The email reminder bypasses the admin-editable template system
- Invoice emails block the HTTP request for up to 30 seconds
- There is no notification center where a user can review, filter, and archive their alerts
- There is no WhatsApp shortcut despite WhatsApp being the dominant B2B communication channel in Arabic-speaking markets

**Phase 26 closes these gaps.** It is the last phase before commercial launch.

### What Phase 26 Is NOT

Phase 26 does not include:
- AI or machine learning of any kind
- Cash Flow Forecast Engine
- Full WhatsApp Business API integration (Meta/Twilio)
- Payment Gateway
- Full Client Portal
- New financial features

These are intentionally deferred. Phase 26 does one thing: makes every communication between Darahum users and their clients professional, reliable, and traceable.

### What Phase 26 Delivers

| Sprint | Name | Core Output |
|---|---|---|
| 1 | Email Foundation | All emails queued, all emails use template system, verification enabled |
| 2 | Business Emails | Quote emails, Invoice Paid confirmation, Debt/Follow-up email channel |
| 3 | Notification Center | Unified center with priorities, types, archive, dashboard widget |
| 4 | WhatsApp Quick Actions | One-click wa.me links for invoices, quotes, clients — no API |

**Total estimated effort: 58–72 hours across 4 sprints.**

---

## 2. Business Value Analysis

### 2.1 The "First 10 Customers" Test

Every feature in Phase 26 was evaluated against one question: *Does this directly affect a freelancer's first 10 customers?*

| Feature | Affects First 10? | Business Impact |
|---|---|---|
| Quote email to client | ✅ Yes — direct client interaction | A freelancer sends a quote with no notification — client confused |
| Invoice Paid confirmation | ✅ Yes — payment workflow completion | Client pays, gets no receipt — appears unprofessional |
| Queued invoice email | ✅ Yes — reliability | Invoice send hangs for 30s on slow SMTP — user thinks it failed |
| Email verification | ✅ Yes — data integrity | Fake registrations accepted silently |
| Debt/follow-up email | ✅ Yes — daily usage value | Alerts exist but invisible unless user is online |
| Notification Center | ✅ Yes — product polish | Bell icon drops to blank dropdown — no history |
| WhatsApp quick actions | ✅ Yes — Arabic market fit | 90%+ of B2B communication in Arabic markets is WhatsApp |

### 2.2 Revenue Impact

| Gap (Pre-Phase-26) | Lost Conversion Moment |
|---|---|
| No quote email | Client never sees the quote → no conversion to invoice |
| No invoice paid email | Payment loop feels incomplete → trust damage → churn |
| No WhatsApp button | Freelancer has to manually compose WhatsApp message → friction → abandonment |
| Database-only debt alerts | User doesn't log in daily → misses deadlines → blames the tool |
| Notification Center empty | Dashboard feels unfinished → trial user doesn't convert |

### 2.3 Feature Scoring (Business Value / User Value / Dev Complexity / Launch Impact)

| Feature | BV | UV | DC | LI | Priority |
|---|---|---|---|---|---|
| Queue InvoiceMail | 9 | 8 | 2 | 9 | P0 |
| Email Verification | 9 | 6 | 1 | 9 | P0 |
| QuoteMail | 10 | 9 | 3 | 10 | P0 |
| Fix InvoiceReminderMail template | 7 | 7 | 2 | 8 | P0 |
| InvoicePaidMail | 9 | 9 | 3 | 9 | P1 |
| Debt email channel | 8 | 8 | 2 | 8 | P1 |
| Follow-up email channel | 7 | 8 | 1 | 7 | P1 |
| Notification Center | 8 | 9 | 5 | 8 | P1 |
| Dashboard Notification Widget | 7 | 8 | 3 | 8 | P1 |
| WhatsApp Quick Actions | 9 | 10 | 2 | 9 | P1 |

*BV = Business Value, UV = User Value, DC = Dev Complexity (lower = easier), LI = Launch Impact. All scored 1–10.*

---

## 3. Architecture Design

### 3.1 Guiding Principles

1. **Zero breaking changes** — every change extends existing code, does not modify contracts
2. **Follow established patterns** — DTO + Action + Service, BelongsToUser, HasUlids, ShouldQueue
3. **EmailTemplate system is the single source of truth** — no hardcoded email HTML
4. **Notification data structure is standardized** — every notification has: type, category, priority, notification_type, title, message, link, icon
5. **WhatsApp = zero infrastructure** — wa.me links only, no DB, no API, no cost

### 3.2 Sprint 1: Email Foundation

**Problem:** InvoiceMail blocks HTTP. InvoiceReminderMail bypasses template system. Email verification is disabled.

**Solution Architecture:**

```
HTTP Request (InvoiceController::sendEmail)
  └─► SendInvoiceEmailJob::dispatch($invoice, $senderName, $toEmail)
        └─► (queue: emails) → Mail::to()->send(new InvoiceMail(...))
```

```
Scheduler (invoices:send-reminders)
  └─► InvoiceReminderMail
        └─► EmailTemplate::render('invoice_reminder', [...vars...])
              └─► emails.template Blade wrapper
```

**New Files:**
```
app/Jobs/
  SendInvoiceEmailJob.php          ShouldQueue, tries:3, timeout:90, failed()
  
app/Mail/
  InvoiceReminderMail.php          UPDATED — remove hardcoded HTML, use EmailTemplate

database/seeders/
  EmailTemplateSeeder.php          Seeds: invoice_reminder, debt_due_soon, debt_overdue,
                                          follow_up_reminder, quote_send, invoice_paid
                                          quote_accepted, quote_rejected
app/Models/User.php                UPDATED — re-enable MustVerifyEmail
```

**Email Template Keys Registry:**

| Key | Subject | Used By | Variables |
|---|---|---|---|
| `welcome` | مرحباً بك في دراهم | WelcomeEmail | `{{name}}`, `{{login_url}}` |
| `password_reset` | إعادة تعيين كلمة المرور | CustomResetPasswordNotification | `{{name}}`, `{{reset_url}}` |
| `invoice_send` | فاتورة رقم {{invoice_number}} | InvoiceMail | `{{client_name}}`, `{{invoice_number}}`, `{{invoice_total}}`, `{{invoice_currency}}`, `{{invoice_due_date}}`, `{{invoice_url}}`, `{{from_name}}` |
| `invoice_reminder` | تذكير: فاتورة مستحقة {{invoice_number}} | InvoiceReminderMail | `{{client_name}}`, `{{invoice_number}}`, `{{invoice_total}}`, `{{invoice_due_date}}`, `{{invoice_url}}`, `{{days_overdue}}` |
| `invoice_paid` | تأكيد استلام الدفعة | InvoicePaidMail | `{{client_name}}`, `{{invoice_number}}`, `{{invoice_total}}`, `{{invoice_currency}}`, `{{paid_date}}`, `{{from_name}}` |
| `quote_send` | عرض سعر رقم {{quote_number}} | QuoteMail | `{{client_name}}`, `{{quote_number}}`, `{{quote_total}}`, `{{quote_currency}}`, `{{quote_valid_until}}`, `{{quote_url}}`, `{{from_name}}` |
| `quote_accepted` | ✅ تم قبول عرض السعر | QuoteAcceptedMail | `{{quote_number}}`, `{{client_name}}`, `{{quote_total}}`, `{{quote_url}}` |
| `quote_rejected` | عرض سعر {{quote_number}} — رد العميل | QuoteRejectedMail | `{{quote_number}}`, `{{client_name}}`, `{{quote_url}}` |
| `debt_due_soon` | تنبيه: دين يستحق قريباً | DebtDueSoonNotification | `{{party_name}}`, `{{amount}}`, `{{currency}}`, `{{due_date}}`, `{{days_left}}` |
| `debt_overdue` | تنبيه عاجل: دين متأخر | DebtOverdueNotification | `{{party_name}}`, `{{amount}}`, `{{currency}}`, `{{days_late}}` |
| `follow_up_reminder` | تذكير متابعة: {{client_name}} | FollowUpReminderNotification | `{{client_name}}`, `{{follow_up_title}}`, `{{follow_up_type}}`, `{{due_at}}` |

---

### 3.3 Sprint 2: Business Emails

**Problem:** No Quote emails. No Invoice Paid confirmation. Debt and follow-up alerts are invisible to offline users.

**New Mailables:**

```
app/Mail/
  QuoteMail.php
  InvoicePaidMail.php
  QuoteAcceptedMail.php          (to user — "your quote was accepted")
  QuoteRejectedMail.php          (to user — "your quote was rejected")
  
app/Jobs/
  SendQuoteEmailJob.php          ShouldQueue, tries:3, timeout:90
  SendInvoicePaidEmailJob.php    ShouldQueue, tries:3, timeout:90
```

**Notification Email Channel Addition:**

Existing notifications get `mail` added to their `via()` method. Each notification adds `toMail()` using `EmailTemplate::render()`:

```php
// DebtDueSoonNotification — before
public function via($notifiable): array { return ['database']; }

// DebtDueSoonNotification — after
public function via($notifiable): array { return ['database', 'mail']; }

public function toMail($notifiable): MailMessage
{
    $rendered = EmailTemplate::render('debt_due_soon', [
        '{{party_name}}' => $this->debt->party_name,
        '{{amount}}'     => number_format($this->debt->remaining_amount, 2),
        '{{currency}}'   => $this->debt->currency,
        '{{due_date}}'   => $this->debt->due_date?->format('Y/m/d'),
        '{{days_left}}'  => max(0, now()->diffInDays($this->debt->due_date, false)),
    ]);

    return (new MailMessage)
        ->subject($rendered['subject'] ?? 'تنبيه: دين يستحق قريباً')
        ->view('emails.template', ['body' => $rendered['body'] ?? '']);
}
```

**Quote Lifecycle Email Triggers:**

| Event | Trigger Point | Mailable | Recipient |
|---|---|---|---|
| Quote sent | `QuoteController::send()` | `QuoteMail` via `SendQuoteEmailJob` | Client email |
| Quote accepted | `QuoteController::accept()` (public portal) | `QuoteAcceptedMail` | User (freelancer) |
| Quote rejected | `QuoteController::reject()` (public portal) | `QuoteRejectedMail` | User (freelancer) |
| Invoice paid | `InvoiceController::markPaid()` | `InvoicePaidMail` via `SendInvoicePaidEmailJob` | Client email |

---

### 3.4 Sprint 3: Notification Center

**Problem:** Notifications exist only as a bell dropdown. No filtering, no priority, no archive, no history page, no standardized data structure.

**Architecture:**

```
NotificationPriority enum   Low | Medium | High | Critical
NotificationType enum       Success | Warning | Error | Info
```

**Standardized Notification Data Contract:**

Every notification's `toArray()` must return this structure:

```php
[
    // Required
    'type'              => 'invoice_due_soon',     // snake_case identifier
    'category'          => 'invoice',              // invoice|quote|debt|crm|project|system
    'priority'          => 'high',                 // low|medium|high|critical
    'notification_type' => 'warning',              // success|warning|error|info
    'title'             => 'فاتورة تستحق قريباً',
    'message'           => 'فاتورة 2024-001 للعميل ...',
    'link'              => '/invoices/01abc...',
    'icon'              => '⏰',

    // Optional — related record
    'related_id'        => '01abc...',             // ULID
    'related_type'      => 'invoice',
    
    // Optional — financial
    'amount'            => 1500.00,
    'currency'          => 'SAR',
]
```

**Priority Assignment by Notification Type:**

| Notification | Priority | Type |
|---|---|---|
| `invoice_overdue` | Critical | Error |
| `debt_overdue` | Critical | Error |
| `invoice_due_soon` | High | Warning |
| `debt_due_soon` | High | Warning |
| `follow_up_reminder` | Medium | Info |
| `automation` | Medium | Info |
| Weekly summary | Low | Success |

**New Controller:**

```
app/Http/Controllers/NotificationController.php
  index()           GET  /notifications            — paginated, filterable
  markRead()        POST /notifications/{id}/read
  markAllRead()     POST /notifications/read-all
  archive()         DELETE /notifications/{id}     — soft delete (read_at = now if not read)
  destroy()         DELETE /notifications/{id}/delete  — hard delete
```

**New Views:**

```
resources/views/notifications/
  index.blade.php         — full notification center (filter by category/priority/read status)

resources/views/components/
  notification-bell.blade.php      — header component: icon + unread badge + dropdown (last 5)
  notification-widget.blade.php    — dashboard widget: last 5 with priority badge + quick actions
  notification-item.blade.php      — reusable single notification row
```

**Dashboard Widget Data:**

```php
// NotificationController::widget() or DashboardController
$notifications = auth()->user()
    ->unreadNotifications()
    ->latest()
    ->take(5)
    ->get()
    ->map(fn($n) => [
        'id'       => $n->id,
        'data'     => $n->data,
        'priority' => $n->data['priority'] ?? 'low',
        'type'     => $n->data['notification_type'] ?? 'info',
        'time'     => $n->created_at->diffForHumans(),
    ]);

$unreadCount = auth()->user()->unreadNotifications()->count();
```

---

### 3.5 Sprint 4: WhatsApp Quick Actions

**Scope:** Generate pre-filled wa.me links. Zero API. Zero DB. Zero cost.

**wa.me URL format:**
```
https://wa.me/{phone}?text={urlencoded_message}
```

Phone format: remove all non-digits, ensure country code prefix (default: `966` for SA).

**Service Class:**

```
app/Support/WhatsAppLinkGenerator.php
```

```php
class WhatsAppLinkGenerator
{
    public static function forInvoice(Invoice $invoice): ?string
    public static function forQuote(Quote $quote): ?string
    public static function forClient(Client $client, string $message = ''): ?string
    
    private static function buildUrl(string $phone, string $message): string
    private static function formatPhone(string $phone): string
    private static function encode(string $text): string  // rawurlencode
}
```

**Pre-filled Messages:**

```php
// Invoice
"السلام عليكم {client_name}،
أرسلت لك فاتورة رقم {invoice_number} بمبلغ {total} {currency}.
يمكنك الاطلاع عليها من الرابط التالي:
{invoice_url}
شكراً 🙏"

// Quote
"السلام عليكم {client_name}،
أرسلت لك عرض سعر رقم {quote_number} بمبلغ {total} {currency}.
يمكنك مراجعته والرد عليه من الرابط التالي:
{quote_url}
بانتظار ردك 😊"

// Client (generic)
"السلام عليكم {client_name}،
أتواصل معك بخصوص..."
```

**Blade Component:**

```blade
{{-- resources/views/components/whatsapp-button.blade.php --}}
@props(['url', 'label' => 'إرسال عبر واتساب', 'size' => 'sm'])

@if($url)
<a href="{{ $url }}"
   target="_blank"
   rel="noopener noreferrer"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 
          bg-[#25D366] hover:bg-[#1ebe5e] text-white text-sm 
          rounded-lg transition-colors font-medium">
    <svg ...> {{-- WhatsApp SVG icon --}} </svg>
    {{ $label }}
</a>
@endif
```

**Usage in Views:**

```blade
{{-- invoices/show.blade.php --}}
<x-whatsapp-button 
    :url="App\Support\WhatsAppLinkGenerator::forInvoice($invoice)" 
    label="إرسال الفاتورة عبر واتساب" />

{{-- quotes/show.blade.php --}}
<x-whatsapp-button 
    :url="App\Support\WhatsAppLinkGenerator::forQuote($quote)" 
    label="إرسال عرض السعر عبر واتساب" />

{{-- clients/show.blade.php --}}
<x-whatsapp-button 
    :url="App\Support\WhatsAppLinkGenerator::forClient($client)" 
    label="تواصل عبر واتساب" />
```

**Null Safety:** If client has no phone number, `forInvoice()` returns `null`. The component checks `@if($url)` and renders nothing — no broken button.

---

## 4. Database Impact

### 4.1 New Migrations

Phase 26 requires **zero new database tables**.

| Decision | Reason |
|---|---|
| No new tables for email | Emails are one-way fire-and-forget; `invoice_reminder_logs` already exists for deduplication |
| No new tables for notifications | Laravel's `notifications` table already exists and handles all data via JSON `data` column |
| No new tables for WhatsApp | wa.me links are generated on-the-fly; no storage needed |

### 4.2 Data Structure Changes (Non-Breaking)

The only "migration" needed is **data standardization** — updating `toArray()` in existing notification classes to include `priority` and `notification_type` keys. Since the `data` column is JSON, adding new keys is fully backward-compatible.

### 4.3 New Seeders

```
database/seeders/
  EmailTemplateSeeder.php     Inserts/upserts 11 email templates
```

The seeder uses `EmailTemplate::updateOrCreate(['key' => $key], [...])` so it is safe to run multiple times.

### 4.4 Migration: Email Templates Already Seeded

| Existing | New in Phase 26 |
|---|---|
| `welcome` ✅ | `invoice_reminder` |
| `password_reset` ✅ | `invoice_paid` |
| `invoice_send` ✅ | `quote_send` |
| | `quote_accepted` |
| | `quote_rejected` |
| | `debt_due_soon` |
| | `debt_overdue` |
| | `follow_up_reminder` |

---

## 5. Laravel Implementation Plan

### 5.1 Folder Structure — Phase 26 Additions

```
app/
├── Jobs/
│   ├── SendInvoiceEmailJob.php          NEW — Sprint 1
│   ├── SendQuoteEmailJob.php            NEW — Sprint 2
│   └── SendInvoicePaidEmailJob.php      NEW — Sprint 2
│
├── Mail/
│   ├── InvoiceMail.php                  UNCHANGED
│   ├── InvoiceReminderMail.php          UPDATED — Sprint 1 (use EmailTemplate)
│   ├── WelcomeEmail.php                 UNCHANGED
│   ├── QuoteMail.php                    NEW — Sprint 2
│   ├── InvoicePaidMail.php              NEW — Sprint 2
│   ├── QuoteAcceptedMail.php            NEW — Sprint 2
│   └── QuoteRejectedMail.php            NEW — Sprint 2
│
├── Http/Controllers/
│   ├── InvoiceController.php            UPDATED — Sprint 1 (dispatch job)
│   ├── QuoteController.php              UPDATED — Sprint 2 (dispatch job on send/accept/reject)
│   └── NotificationController.php       UPDATED — Sprint 3 (add index, archive, markRead)
│
├── Notifications/
│   ├── InvoiceDueSoonNotification.php   UPDATED — Sprint 2 (add toMail + priority)
│   ├── InvoiceOverdueNotification.php   UPDATED — Sprint 2 (add toMail + priority)
│   ├── DebtDueSoonNotification.php      UPDATED — Sprint 2 (add toMail + priority)
│   ├── DebtOverdueNotification.php      UPDATED — Sprint 2 (add toMail + priority)
│   └── FollowUpReminderNotification.php UPDATED — Sprint 2 (add toMail + priority)
│
├── Support/
│   ├── Enums/
│   │   ├── NotificationPriority.php     NEW — Sprint 3
│   │   └── NotificationType.php         NEW — Sprint 3
│   └── WhatsAppLinkGenerator.php        NEW — Sprint 4
│
└── Models/
    └── User.php                         UPDATED — Sprint 1 (re-enable MustVerifyEmail)

resources/views/
├── emails/
│   ├── template.blade.php               UNCHANGED
│   └── welcome.blade.php                UNCHANGED
├── notifications/
│   └── index.blade.php                  NEW — Sprint 3
└── components/
    ├── notification-bell.blade.php       UPDATED — Sprint 3 (priority badges)
    ├── notification-widget.blade.php     NEW — Sprint 3
    ├── notification-item.blade.php       NEW — Sprint 3
    └── whatsapp-button.blade.php         NEW — Sprint 4

database/seeders/
└── EmailTemplateSeeder.php              NEW — Sprint 1
```

### 5.2 Queue Configuration

All email jobs use the `emails` queue:

```php
// config/queue.php — no change needed if using default
// In each Job:
public string $queue = 'emails';
public int    $tries = 3;
public int    $timeout = 90;
public array  $backoff = [30, 120, 300]; // 30s, 2m, 5m

public function failed(\Throwable $e): void {
    Log::error("Email job failed: " . static::class, [
        'error'   => $e->getMessage(),
        'model_id' => $this->invoice->id ?? $this->quote->id ?? null,
    ]);
}
```

For production on cPanel shared hosting (per `docs/DEPLOY.md`), queue runs via Scheduler:

```php
// routes/console.php — add
Schedule::command('queue:work --queue=emails --max-jobs=50 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

### 5.3 Multi-Tenant Safety

All new code respects the existing BelongsToUser pattern:
- Jobs serialize the Eloquent Model — `BelongsToUser` Global Scope is bypassed during serialization/deserialization correctly because the model is loaded by ID in the job's context (queued as `$this->invoice` via `SerializesModels`)
- `EmailTemplate::render()` is a global (non-tenant) lookup by key — templates are platform-wide, not per-user ✅
- `WhatsAppLinkGenerator` generates URLs from model data — no direct DB queries ✅
- Notification data is always scoped to `auth()->user()->notifications()` ✅

### 5.4 Email Verification Re-enablement

```php
// app/Models/User.php — line 17
// BEFORE:
class User extends Authenticatable implements FilamentUser

// AFTER:
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
```

```php
// ALSO uncomment line 7:
use Illuminate\Contracts\Auth\MustVerifyEmail;
```

Add `email_verification` template key. Add `verified` middleware to protected routes (already supported by Laravel — just needs to be added to route groups).

---

## 6. UI/UX Plan

### 6.1 Email Touchpoints

**Invoice Show Page — new action buttons:**

```
[PDF] [إرسال بالبريد] [إرسال عبر واتساب ↗] [تحديد كمدفوعة]
```

When "إرسال بالبريد" is clicked → dispatches `SendInvoiceEmailJob` → user sees success toast immediately (no more 30-second wait).

**Quote Show Page — new action buttons:**

```
[PDF] [إرسال للعميل بالبريد] [إرسال عبر واتساب ↗] [تحويل لفاتورة]
```

### 6.2 Notification Center UI

**Bell Icon (Header):**
- Red badge with unread count (max display: 99+)
- Dropdown shows last 5 notifications
- Each item: priority color indicator + icon + title + time ago + link
- Footer: "عرض كل الإشعارات →"

**Priority Color Coding:**

| Priority | Color | Usage |
|---|---|---|
| Critical | `red-600` | Overdue invoices, overdue debts |
| High | `orange-500` | Due-soon invoices, due-soon debts |
| Medium | `blue-500` | Follow-up reminders, automations |
| Low | `gray-400` | Weekly summaries, info messages |

**Notification Center Page (`/notifications`):**

```
┌─────────────────────────────────────────────────────────────┐
│  مركز الإشعارات                    [تحديد الكل كمقروء]      │
├─────────────────────────────────────────────────────────────┤
│  فلترة: [الكل ▼] [الفئة ▼] [الأولوية ▼] [الحالة ▼]       │
├─────────────────────────────────────────────────────────────┤
│  🔴 CRITICAL  ⚡ فاتورة متأخرة         منذ ساعتين   [فتح] │
│  🟠 HIGH      ⏰ دين يستحق غداً        منذ 3 ساعات [فتح] │
│  🔵 MEDIUM    📋 تذكير متابعة          منذ يوم     [فتح] │
│  ─────────────────────────────────────────────────────     │
│  ✓ مقروء    📦 أرشيف    ×  حذف  (per-item actions)        │
└─────────────────────────────────────────────────────────────┘
```

**Dashboard Notification Widget:**

Positioned below the existing wallet summary cards. Shows last 5 unread notifications with:
- Priority color stripe on the left
- Icon + title + message excerpt
- Time ago (Arabic relative time)
- "Mark read" and "Open" quick actions

### 6.3 WhatsApp Button Design

- Background: `#25D366` (official WhatsApp green)
- Icon: WhatsApp SVG (inline, no external CDN)
- Opens in new tab (`target="_blank"`)
- Gracefully hidden when `$url === null` (client has no phone)

**Placement:**
- Invoice Show → action bar
- Invoice Index → per-row action button (mobile: icon only)
- Quote Show → action bar
- Quote Index → per-row action button
- Client Show → contact section
- Client Index → per-row quick action

---

## 7. Updated Roadmap

### Deferred Phases (Previously Phase 26)

The following features were previously scoped as Phase 26 "Business Intelligence & Communication Layer." They are deferred to allow a focused, achievable communication foundation:

| Feature | New Phase | Rationale |
|---|---|---|
| WhatsApp Business Center (full API) | Phase 27 | Requires Meta API approval process; wa.me covers 90% of use cases |
| Cash Flow Forecast Engine | Phase 28 | Not needed for first 10 customers |
| AI Insights Engine | Phase 29 | Not needed for launch |
| Payment Gateway | Phase 30 | Deferred by business decision — manual billing first |

### Updated Phase Map

```
Phase 1–25   ✅ Complete — Financial Engine, CRM, Invoices, Quotes, PDF, Reports
Phase 26     📋 Communication Layer (this document) — 58–72h — Launch Blocker
Phase 27     📅 WhatsApp Business Center (full API — post-launch)
Phase 28     📅 Cash Flow Forecast Engine
Phase 29     📅 AI Insights Engine Phase 1
Phase 30     📅 Payment Gateway Integration
Phase 31     📅 REST API + Flutter App
Phase 32     📅 Teams + Multi-User
```

---

## 8. Sprint Breakdown

### Sprint 1 — Email Foundation (Days 1–5)

**Goal:** All emails queued, all emails use template system, email verification enabled.

| # | Task | File(s) | Est. |
|---|---|---|---|
| 1.1 | Re-enable `MustVerifyEmail` in `User.php` | `app/Models/User.php` | 0.5h |
| 1.2 | Add `email_verification` route middleware to protected routes | `routes/web.php` | 0.5h |
| 1.3 | Create `EmailTemplateSeeder` with 8 new template keys | `database/seeders/EmailTemplateSeeder.php` | 2h |
| 1.4 | Create `SendInvoiceEmailJob` (ShouldQueue, tries:3, failed()) | `app/Jobs/SendInvoiceEmailJob.php` | 1.5h |
| 1.5 | Update `InvoiceController::sendEmail()` to dispatch job | `app/Http/Controllers/InvoiceController.php` | 0.5h |
| 1.6 | Update `InvoiceReminderMail` to use `EmailTemplate::render('invoice_reminder')` | `app/Mail/InvoiceReminderMail.php` | 1.5h |
| 1.7 | Add queue runner to Scheduler (cPanel-compatible) | `routes/console.php` | 0.5h |
| 1.8 | Test: verify email sends, queue dispatches, template fallback | Pest | 2h |
| **Total Sprint 1** | | | **~9h** |

---

### Sprint 2 — Business Emails (Days 6–14)

**Goal:** Quote emails, Invoice Paid email, Debt/Follow-up email channel.

| # | Task | File(s) | Est. |
|---|---|---|---|
| 2.1 | Create `QuoteMail` (template system, signed URL 30d, no PDF first) | `app/Mail/QuoteMail.php` | 2h |
| 2.2 | Create `SendQuoteEmailJob` | `app/Jobs/SendQuoteEmailJob.php` | 1h |
| 2.3 | Update `QuoteController::send()` to dispatch `SendQuoteEmailJob` | `app/Http/Controllers/QuoteController.php` | 0.5h |
| 2.4 | Create `QuoteAcceptedMail` + `QuoteRejectedMail` | `app/Mail/` | 2h |
| 2.5 | Update public portal accept/reject to dispatch mail to user | `app/Http/Controllers/QuoteController.php` | 1h |
| 2.6 | Create `InvoicePaidMail` + `SendInvoicePaidEmailJob` | `app/Mail/` + `app/Jobs/` | 2h |
| 2.7 | Update `InvoiceController::markPaid()` to dispatch job | `app/Http/Controllers/InvoiceController.php` | 0.5h |
| 2.8 | Add `toMail()` + `mail` channel to `DebtDueSoonNotification` | `app/Notifications/DebtDueSoonNotification.php` | 1h |
| 2.9 | Add `toMail()` + `mail` channel to `DebtOverdueNotification` | `app/Notifications/DebtOverdueNotification.php` | 1h |
| 2.10 | Add `toMail()` + `mail` channel to `FollowUpReminderNotification` | `app/Notifications/FollowUpReminderNotification.php` | 1h |
| 2.11 | Add `toMail()` + `mail` channel to `InvoiceDueSoonNotification` | `app/Notifications/InvoiceDueSoonNotification.php` | 1h |
| 2.12 | Add `toMail()` + `mail` channel to `InvoiceOverdueNotification` | `app/Notifications/InvoiceOverdueNotification.php` | 1h |
| 2.13 | Test: QuoteMail, InvoicePaidMail, notification emails | Pest | 3h |
| **Total Sprint 2** | | | **~17h** |

---

### Sprint 3 — Notification Center (Days 15–24)

**Goal:** Unified notification center with priorities, types, archive, and dashboard widget.

| # | Task | File(s) | Est. |
|---|---|---|---|
| 3.1 | Create `NotificationPriority` enum (Low/Medium/High/Critical) | `app/Support/Enums/NotificationPriority.php` | 0.5h |
| 3.2 | Create `NotificationType` enum (Success/Warning/Error/Info) | `app/Support/Enums/NotificationType.php` | 0.5h |
| 3.3 | Update all 6 notifications — add `priority`, `notification_type`, `category` to `toArray()` | All Notification classes | 2h |
| 3.4 | Update `AutomationNotification` in CRM module — add priority keys | `app/Modules/CRM/Notifications/AutomationNotification.php` | 0.5h |
| 3.5 | Add `index()`, `archive()`, `markRead()`, `markAllRead()`, `destroy()` to `NotificationController` | `app/Http/Controllers/NotificationController.php` | 2h |
| 3.6 | Add routes for notification actions | `routes/web.php` | 0.5h |
| 3.7 | Create `notifications/index.blade.php` with filter tabs (All/Unread/Archived), priority badges, per-item actions | `resources/views/notifications/index.blade.php` | 4h |
| 3.8 | Update `notification-bell.blade.php` — add priority color indicators, improve dropdown | `resources/views/components/notification-bell.blade.php` | 2h |
| 3.9 | Create `notification-item.blade.php` — reusable row component | `resources/views/components/notification-item.blade.php` | 1h |
| 3.10 | Create `notification-widget.blade.php` — dashboard widget (last 5 unread + unread count) | `resources/views/components/notification-widget.blade.php` | 2h |
| 3.11 | Integrate notification widget into `dashboard.blade.php` | `resources/views/dashboard.blade.php` | 0.5h |
| 3.12 | Test: mark read, mark all read, archive, filter, pagination | Pest | 2h |
| **Total Sprint 3** | | | **~17.5h** |

---

### Sprint 4 — WhatsApp Quick Actions (Days 25–28)

**Goal:** One-click wa.me links on invoices, quotes, and client pages.

| # | Task | File(s) | Est. |
|---|---|---|---|
| 4.1 | Create `WhatsAppLinkGenerator` service | `app/Support/WhatsAppLinkGenerator.php` | 2h |
| 4.2 | Create `whatsapp-button.blade.php` component | `resources/views/components/whatsapp-button.blade.php` | 1h |
| 4.3 | Add WhatsApp button to Invoice Show page | `resources/views/invoices/show.blade.php` | 0.5h |
| 4.4 | Add WhatsApp button to Invoice Index (per-row action) | `resources/views/invoices/index.blade.php` | 0.5h |
| 4.5 | Add WhatsApp button to Quote Show page | `resources/views/quotes/show.blade.php` | 0.5h |
| 4.6 | Add WhatsApp button to Quote Index (per-row action) | `resources/views/quotes/index.blade.php` | 0.5h |
| 4.7 | Add WhatsApp contact button to Client Show page | `resources/views/clients/show.blade.php` | 0.5h |
| 4.8 | Add WhatsApp to Client Index quick actions | `resources/views/clients/index.blade.php` | 0.5h |
| 4.9 | Test: link generation, null phone handling, URL encoding | Pest | 1.5h |
| **Total Sprint 4** | | | **~7.5h** |

---

### Sprint Summary

| Sprint | Name | Duration | Hours |
|---|---|---|---|
| Sprint 1 | Email Foundation | Days 1–5 | ~9h |
| Sprint 2 | Business Emails | Days 6–14 | ~17h |
| Sprint 3 | Notification Center | Days 15–24 | ~17.5h |
| Sprint 4 | WhatsApp Quick Actions | Days 25–28 | ~7.5h |
| **Total** | | **~4 weeks** | **~51h dev + 10h testing = ~61h** |

---

## 9. Estimated Development Hours

### By Category

| Category | Hours | % of Total |
|---|---|---|
| New Mailables (4) | 8h | 13% |
| New Jobs (3) | 4h | 7% |
| Notification updates (6) | 8h | 13% |
| Notification Center (controller + views) | 10h | 16% |
| Dashboard Widget | 3h | 5% |
| WhatsApp Generator + Component | 3h | 5% |
| WhatsApp Integration (8 views) | 4h | 7% |
| Email Templates Seeder | 2h | 3% |
| Bug fixes (MustVerifyEmail, InvoiceReminderMail) | 3h | 5% |
| Enums + Support classes | 1.5h | 2% |
| Routes + Controller updates | 2h | 3% |
| Testing (Pest) | 9h | 15% |
| Documentation updates | 3.5h | 6% |
| **Total** | **~61h** | **100%** |

### Velocity Estimate

| Team Size | Estimated Calendar Time |
|---|---|
| 1 developer (full-time) | 7–8 working days |
| 1 developer (part-time, 4h/day) | 15–16 working days |
| 2 developers (Sprint 1+4 / Sprint 2+3 parallel) | 4–5 working days |

---

## 10. Risks and Mitigation

### Technical Risks

| Risk | Severity | Probability | Mitigation |
|---|---|---|---|
| Queue jobs fail silently on cPanel | High | Medium | Add `failed()` method to all jobs; log to `storage/logs/email-failures.log`; test on staging first |
| `MustVerifyEmail` breaks existing users (already registered without verification) | High | High | Run migration to set `email_verified_at = now()` for all existing users before enabling the middleware |
| `toMail()` in Notifications sends duplicate emails (DB + Mail) | Medium | Low | Add a `user_preferences` check — allow user to opt out of email notifications in settings (Phase B) |
| EmailTemplate missing for a key → null render → email not sent | Medium | Low | Add fallback body to all Mailables; log when template not found |
| `WhatsAppLinkGenerator::formatPhone()` fails on non-standard Saudi numbers | Medium | Medium | Permissive formatter — strip all non-digits, prepend `966` only if no country code detected; return `null` on failure |

### Business Risks

| Risk | Severity | Mitigation |
|---|---|---|
| Notification emails annoying users who preferred DB-only | Medium | Sprint 2B: Add per-notification-type email toggle in user settings |
| WhatsApp messages look unprofessional if default template is bad | Medium | Write professional, tested Arabic copy in `WhatsAppLinkGenerator`; allow user to customize message (Phase 27) |
| Existing users get verification email storm after re-enabling `MustVerifyEmail` | High | Mass-set `email_verified_at` for all existing users via migration BEFORE enabling the interface |

### Execution Risks

| Risk | Mitigation |
|---|---|
| Scope creep — team adds "just one more email type" | This document defines the scope boundary. Phase 26 = 4 sprints as defined. Anything new → Phase 27. |
| Sprint 3 (Notification Center) expands into a full notification preferences system | Sprint 3 delivers display + archive + read/unread only. Preferences = Phase 27. |

---

## Appendix A — EmailTemplate Seeder Content

```php
// database/seeders/EmailTemplateSeeder.php (reference — not final code)

$templates = [
    [
        'key'       => 'invoice_reminder',
        'name'      => 'تذكير بفاتورة مستحقة',
        'subject'   => 'تذكير: فاتورة رقم {{invoice_number}} مستحقة',
        'body'      => '<p>عزيزي {{client_name}}،</p>
<p>نودّ تذكيرك بأن الفاتورة رقم <strong>{{invoice_number}}</strong> بمبلغ 
<strong>{{invoice_total}} {{invoice_currency}}</strong> 
مستحقة في تاريخ <strong>{{invoice_due_date}}</strong>.</p>
<p>يمكنك مراجعة الفاتورة وسداد المبلغ من خلال الرابط التالي:</p>
<p style="text-align:center">
  <a href="{{invoice_url}}" style="background:#6366f1;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;display:inline-block">
    عرض الفاتورة
  </a>
</p>
<p>شكراً لتعاملكم معنا.</p>',
        'variables' => [
            '{{client_name}}'     => 'اسم العميل',
            '{{invoice_number}}'  => 'رقم الفاتورة',
            '{{invoice_total}}'   => 'المبلغ الإجمالي',
            '{{invoice_currency}}'=> 'العملة',
            '{{invoice_due_date}}'=> 'تاريخ الاستحقاق',
            '{{invoice_url}}'     => 'رابط الفاتورة',
        ],
    ],

    [
        'key'       => 'invoice_paid',
        'name'      => 'تأكيد استلام الدفعة',
        'subject'   => 'تأكيد: تم استلام دفعتك للفاتورة {{invoice_number}}',
        'body'      => '<p>عزيزي {{client_name}}،</p>
<p>يسعدنا إبلاغك بأنه تم استلام دفعتك بنجاح.</p>
<p>تفاصيل الدفعة:<br>
رقم الفاتورة: <strong>{{invoice_number}}</strong><br>
المبلغ: <strong>{{invoice_total}} {{invoice_currency}}</strong><br>
تاريخ الاستلام: <strong>{{paid_date}}</strong></p>
<p>شكراً لثقتكم بنا. نتطلع إلى التعامل معكم مجدداً.</p>
<p>مع تحياتنا،<br><strong>{{from_name}}</strong></p>',
        'variables' => [
            '{{client_name}}'     => 'اسم العميل',
            '{{invoice_number}}'  => 'رقم الفاتورة',
            '{{invoice_total}}'   => 'المبلغ المسدَّد',
            '{{invoice_currency}}'=> 'العملة',
            '{{paid_date}}'       => 'تاريخ الاستلام',
            '{{from_name}}'       => 'اسم المُرسِل',
        ],
    ],

    [
        'key'       => 'quote_send',
        'name'      => 'إرسال عرض السعر',
        'subject'   => 'عرض سعر رقم {{quote_number}} من {{from_name}}',
        'body'      => '<p>عزيزي {{client_name}}،</p>
<p>يسعدني تقديم عرض السعر رقم <strong>{{quote_number}}</strong> بمبلغ 
<strong>{{quote_total}} {{quote_currency}}</strong>.</p>
<p>العرض صالح حتى تاريخ <strong>{{quote_valid_until}}</strong>.</p>
<p>يمكنك مراجعة العرض والموافقة عليه أو التواصل معنا من خلال الرابط التالي:</p>
<p style="text-align:center">
  <a href="{{quote_url}}" style="background:#6366f1;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;display:inline-block">
    عرض التفاصيل والرد
  </a>
</p>
<p>بانتظار ردكم الكريم،<br><strong>{{from_name}}</strong></p>',
        'variables' => [
            '{{client_name}}'      => 'اسم العميل',
            '{{quote_number}}'     => 'رقم عرض السعر',
            '{{quote_total}}'      => 'المبلغ الإجمالي',
            '{{quote_currency}}'   => 'العملة',
            '{{quote_valid_until}}'=> 'تاريخ انتهاء الصلاحية',
            '{{quote_url}}'        => 'رابط عرض السعر',
            '{{from_name}}'        => 'اسم المُرسِل',
        ],
    ],
];
```

---

## Appendix B — Artisan Commands Checklist

| Command | Trigger | Purpose | Added In |
|---|---|---|---|
| `invoices:send-reminders` | Daily 09:00 | Send `InvoiceReminderMail` to clients | Phase 18 |
| `debts:send-alerts` | Daily 08:00 | Send `DebtDueSoonNotification` (DB + email after Sprint 2) | Phase 10 |
| `crm:send-follow-up-reminders` | Every 30min | Send `FollowUpReminderNotification` (DB + email after Sprint 2) | Phase 17 |
| `queue:work --queue=emails` | Every 1min (Scheduler) | Process email queue on cPanel shared hosting | Sprint 1 |

---

*Phase 26 Communication Layer — Architecture & Design Document. Implementation begins upon approval.*
