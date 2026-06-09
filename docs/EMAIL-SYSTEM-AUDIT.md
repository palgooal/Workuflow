# Email System Audit — Darahum
> **Version:** 1.0.0 | **Date:** 8 Jun 2026 | **Phase:** Pre-Launch Audit
> **Audited by:** Senior Laravel Architect · SaaS PM · CRM Specialist · Email Automation Consultant
> **Scope:** All email functionality across the entire codebase — Mailables, Notifications, Jobs, Commands, Scheduler, Templates, Infrastructure

---

## Table of Contents

1. [Email Infrastructure Overview](#1-email-infrastructure-overview)
2. [Existing Email Features — Full Inventory](#2-existing-email-features--full-inventory)
3. [Missing Email Features — Full Checklist](#3-missing-email-features--full-checklist)
4. [Architecture Review](#4-architecture-review)
5. [Risk Assessment](#5-risk-assessment)
6. [Email-Only Launch Readiness Score](#6-email-only-launch-readiness-score)
7. [Top Priorities Before Launch](#7-top-priorities-before-launch)
8. [Phase A / B / C Email Roadmap](#8-phase-a--b--c-email-roadmap)
9. [Final Verdict](#9-final-verdict)

---

## 1. Email Infrastructure Overview

### 1.1 Stack

| Layer | Implementation | Status |
|---|---|---|
| Mail Driver | Laravel Mail (SMTP) | ✅ Configured |
| SMTP Settings | DB-driven via `Setting::group('mail')` | ✅ Admin-editable |
| Admin Panel | `MailSettings` Filament page (host, port, encryption, from_address) | ✅ Working |
| Template System | `EmailTemplate` model — key-based, DB-stored, Filament CRUD | ✅ Working |
| HTML Wrapper | `resources/views/emails/template.blade.php` | ✅ Branded |
| Queue Integration | Partial — WelcomeEmail via `SendWelcomeEmailJob`, InvoiceMail **synchronous** | ⚠️ Inconsistent |
| Failed Job Handling | `SendWelcomeEmailJob::failed()` logs error only | ⚠️ No retry alerting |
| Email Logging | `invoice_reminder_logs` table (reminder deduplication only) | ⚠️ Partial |
| Rate Limiting | None | ❌ Missing |
| Localization | All Arabic, hardcoded — no i18n support | ❌ Missing |
| Unsubscribe | No unsubscribe mechanism | ❌ Missing |

### 1.2 Files Audited

**Mailables (`app/Mail/`):**
- `WelcomeEmail.php`
- `InvoiceMail.php`
- `InvoiceReminderMail.php`

**Notifications (`app/Notifications/`):**
- `CustomResetPasswordNotification.php`
- `InvoiceDueSoonNotification.php`
- `InvoiceOverdueNotification.php`
- `DebtDueSoonNotification.php`
- `DebtOverdueNotification.php`
- `FollowUpReminderNotification.php`

**Jobs (`app/Jobs/`):**
- `SendWelcomeEmailJob.php`

**Commands (`app/Console/Commands/`):**
- `SendInvoiceReminders.php`
- `SendDebtAlerts.php`
- `SendFollowUpReminders.php`

**CRM Module (`app/Modules/CRM/Notifications/`):**
- `AutomationNotification.php`

**Models:**
- `EmailTemplate.php`

**Views (`resources/views/emails/`):**
- `template.blade.php`
- `welcome.blade.php` (fallback)

**Filament:**
- `MailSettings.php` (SMTP admin)
- `EmailTemplateResource.php` (template CRUD)

**Auth:**
- `EmailVerificationNotificationController.php`
- `EmailVerificationPromptController.php`
- `VerifyEmailController.php`

**Scheduler (`routes/console.php`):**
- `invoices:send-reminders` — daily 09:00
- `debts:send-alerts` — daily 08:00
- `crm:send-follow-up-reminders` — every 30 min

---

## 2. Existing Email Features — Full Inventory

### 2.1 Account Emails

| Email Type | Exists | Implementation | Template System | Queued |
|---|---|---|---|---|
| Welcome Email | ✅ YES | `WelcomeEmail` + `SendWelcomeEmailJob` | ✅ `welcome` key | ✅ Yes (tries:3, timeout:60) |
| Email Verification | ⚠️ DISABLED | Controllers exist, `MustVerifyEmail` **commented out** in `User.php` | ❌ No | N/A |
| Password Reset | ✅ YES | `CustomResetPasswordNotification` → `MailMessage` | ✅ `password_reset` key | ❌ Sync |
| Email Change Re-verification | ❌ NO | Profile update nullifies `email_verified_at` but verification is disabled | — | — |
| Account Deletion Confirmation | ❌ NO | — | — | — |
| Trial Expiration | ❌ NO | No trial system exists | — | — |
| Subscription Created | ❌ NO | No subscription lifecycle emails | — | — |
| Subscription Upgraded | ❌ NO | — | — | — |
| Subscription Downgraded | ❌ NO | — | — | — |
| Subscription Cancelled | ❌ NO | — | — | — |

**Critical note on email verification:** `User.php` line 7 contains:
```php
// use Illuminate\Contracts\Auth\MustVerifyEmail; // TODO: إعادة تفعيله قبل الإطلاق (Phase 13)
```
The class declaration is `implements FilamentUser` only — **not `MustVerifyEmail`**. The three verification controllers exist and are routed, but they are effectively dead code because the interface is not implemented. Any user can access the full app without ever verifying their email.

---

### 2.2 Invoice Emails

| Email Type | Exists | Implementation | Template System | Queue | PDF |
|---|---|---|---|---|---|
| Invoice Sent to Client | ✅ YES | `InvoiceMail` dispatched from `InvoiceController::sendEmail()` | ✅ `invoice_send` key | ❌ Sync | ✅ mPDF attachment |
| Invoice Reminder (Before Due) | ✅ YES | `InvoiceReminderMail` (type: `before_due`) via `SendInvoiceReminders` | ❌ **Hardcoded HTML** | ❌ Sync | ❌ No |
| Invoice Reminder (Overdue) | ✅ YES | `InvoiceReminderMail` (type: `overdue`) via `SendInvoiceReminders` | ❌ **Hardcoded HTML** | ❌ Sync | ❌ No |
| Invoice Paid Confirmation | ❌ NO | No email sent when invoice marked as paid | — | — | — |
| Invoice Created (Internal) | ❌ NO | No notification to user when invoice is created | — | — | — |
| Invoice Viewed by Client | ❌ NO | Public portal exists but no "viewed" notification | — | — | — |

**InvoiceMail — Full Feature Detail:**
- PDF attachment generated via mPDF
- Variables: `{{client_name}}`, `{{invoice_number}}`, `{{invoice_total}}`, `{{invoice_currency}}`, `{{invoice_due_date}}`, `{{invoice_notes}}`, `{{invoice_url}}`, `{{invoice_items}}`, `{{from_name}}`, `{{due_color}}`
- Signed URL with 30-day expiry for public invoice view
- HTML items table built inline
- Conditional notes block
- Falls back to plain `body` if template inactive

**InvoiceReminderMail — Issues:**
- Does NOT use `EmailTemplate::render()` — body is a hardcoded PHP string
- No PDF attached (unlike InvoiceMail)
- No fallback body
- Sends to `$invoice->client->email` directly
- Deduplication via `invoice_reminder_logs` ✅ (this part is correct)

---

### 2.3 Quote Emails

| Email Type | Exists | Notes |
|---|---|---|
| Quote Sent to Client | ❌ NO | No `QuoteMail` class exists anywhere |
| Quote Accepted | ❌ NO | — |
| Quote Rejected | ❌ NO | — |
| Quote Expiring Soon | ❌ NO | — |
| Quote Converted to Invoice | ❌ NO | — |

**Assessment:** The Quotes module is fully implemented (DB schema, public portal, token-based access, lifecycle management) but has **zero email integration**. Clients receive quotes with no notification. Acceptances and rejections generate no outbound email. This is a significant gap for a B2B SaaS.

---

### 2.4 CRM Emails

| Email Type | Exists | Channel | Notes |
|---|---|---|---|
| Follow-up Reminder to User | ❌ Email NO | Database only | `FollowUpReminderNotification` via `['database']` |
| Client Automation Notification | ❌ Email NO | Database only | `AutomationNotification` via `['database']` |
| New Lead Notification | ❌ NO | — | No lead email system |
| Client Health Score Alert | ❌ NO | — | `crm:recalculate-health-scores` updates DB, no email |
| Inactive Client Alert | ❌ NO | — | `crm:detect-inactive` triggers automations (DB only) |

---

### 2.5 Debt / Financial Alert Emails

| Email Type | Exists | Channel | Notes |
|---|---|---|---|
| Debt Due Soon (to User) | ❌ Email NO | Database only | `DebtDueSoonNotification` via `['database']` |
| Debt Overdue (to User) | ❌ Email NO | Database only | `DebtOverdueNotification` via `['database']` |

**Critical gap:** The scheduler runs `debts:send-alerts` daily at 08:00. `SendDebtAlerts` calls `NotificationService::generateDebtAlerts()` which sends only database notifications. Users who are not logged in — the users who most need debt alerts — never see them.

---

### 2.6 System Emails

| Email Type | Exists | Notes |
|---|---|---|
| Subscription Created | ❌ NO | No payment provider → no subscription emails |
| Subscription Cancelled | ❌ NO | — |
| Failed Payment | ❌ NO | — |
| Plan Limit Hit | ❌ NO | `abort(403)` is thrown, no email |
| Admin: New User Registered | ❌ NO | No admin notification system |

---

### 2.7 Summary Count

| Category | Emails That Exist | Emails Missing |
|---|---|---|
| Account | 2 (Welcome, Password Reset) | 6 (Verification, Change, Deletion, Trial, Subscription ×3) |
| Invoice | 3 (Send, Reminder ×2) | 3 (Paid, Created, Viewed) |
| Quote | 0 | 5 (Sent, Accepted, Rejected, Expiring, Converted) |
| CRM | 0 | 5 (Follow-up, Automation, Lead, Health, Inactive) |
| Debt/Financial | 0 | 2 (Due Soon, Overdue) |
| System | 0 | 4 (Subscription ×3, Admin alert) |
| **Total** | **5** | **25** |

---

## 3. Missing Email Features — Full Checklist

### ✅ Implemented
- [x] Welcome email (queued, template system, branded)
- [x] Password reset email (template system)
- [x] Invoice send to client (full: PDF, template, signed URL)
- [x] Invoice reminder before due (scheduled, deduplication)
- [x] Invoice reminder overdue (scheduled, deduplication)

### ❌ Missing — Business-Critical
- [ ] **Email verification enforcement** (`MustVerifyEmail` commented out)
- [ ] **Quote sent to client** (no email when quote is delivered)
- [ ] **Quote accepted/rejected confirmation** (no email feedback loop)
- [ ] **Invoice paid confirmation** to client (no "payment received" email)
- [ ] **Debt due/overdue email** to user (currently database-only)
- [ ] **Follow-up reminder email** to user (currently database-only)

### ❌ Missing — Important for Retention
- [ ] Invoice reminder does NOT use template system (hardcoded HTML — inconsistency)
- [ ] Email verification is fully disabled — users can register fake emails
- [ ] No subscription lifecycle emails (no payment provider yet, but emails should be ready)
- [ ] No re-verification on email change (security gap)

### ❌ Missing — Nice-to-Have
- [ ] Invoice viewed by client notification
- [ ] Client activity digest (weekly)
- [ ] Quote expiring soon reminder
- [ ] Admin alert on new user registration
- [ ] Account deletion confirmation
- [ ] Trial expiration warning (no trial system yet)
- [ ] CRM automation email channel (automation actions are DB-only)

---

## 4. Architecture Review

### 4.1 EmailTemplate System — Strengths

The `EmailTemplate` model is architecturally sound:

```php
// Simple, effective render contract
EmailTemplate::render(string $key, array $vars): ?array
// Returns ['subject' => '...', 'body' => '...'] or null if inactive/missing
```

- Key-based lookup (not ID-based → admin-safe)
- Variables stored as JSON, documented per template
- Admin-editable via Filament RichEditor
- Falls back gracefully when template is `is_active = false`
- Used correctly by: `InvoiceMail`, `WelcomeEmail`, `CustomResetPasswordNotification`

**Gap:** `InvoiceReminderMail` completely bypasses this system. This creates two maintenance problems:
1. The reminder template cannot be edited from the admin panel
2. Future developers will not know which pattern to follow

### 4.2 Queue Integration — Inconsistency

| Mailable | Queued? | Method |
|---|---|---|
| `WelcomeEmail` | ✅ Yes | `SendWelcomeEmailJob` (ShouldQueue, tries:3, timeout:60, failed()) |
| `InvoiceMail` | ❌ No | `Mail::to()->send()` directly in controller — blocks HTTP request |
| `InvoiceReminderMail` | ❌ No | Called in `SendInvoiceReminders` Artisan command (acceptable in CLI) |
| `CustomResetPasswordNotification` | ❌ No | Sent synchronously by Laravel's notification system |

**Risk from synchronous InvoiceMail:** If the SMTP server is slow or down, the user's browser request blocks for up to 30+ seconds and then returns an error. The invoice send UX is completely dependent on SMTP response time. This should be queued.

### 4.3 Deduplication — Partial

`invoice_reminder_logs` correctly prevents duplicate invoice reminders:

```php
InvoiceReminderLog::create([
    'invoice_id' => $invoice->id,
    'type'       => $type,  // before_due | overdue
    'sent_at'    => now(),
    'channel'    => 'email',
]);
```

`NotificationService::notifyOnce()` deduplicates debt notifications by checking `notifications` table within 24h window.

**Gap:** No deduplication for `InvoiceMail` manual sends. A user could accidentally send the same invoice email to the same client three times without any warning.

### 4.4 HTML Email Template

`resources/views/emails/template.blade.php` is a clean, responsive HTML wrapper:
- RTL Arabic layout
- Inline CSS (email-client compatible)
- Branded header (gradient, "دراهم" logo)
- Footer with "Do not reply" note
- Uses `{!! $body !!}` — accepts HTML from template engine

`resources/views/emails/welcome.blade.php` is a full standalone template (fallback) with feature showcase, CTA button, quick links. Well-designed.

**Note:** The welcome view references `$billingUrl` and `$settingsUrl` variables. Verify that `WelcomeEmail.php` always passes these — if missing, Blade will throw an undefined variable error in the fallback path.

### 4.5 SMTP Admin Panel

`MailSettings` Filament page supports:
- Host, port, encryption (SSL/TLS/none)
- Auto-sets port when encryption changes (live Filament form)
- Hidden `mail_scheme` field (smtps/null)
- From address + from name
- **Test email field** (send test email feature)

This is production-ready for a manual-SMTP setup. Admins can change the sending account without touching `.env`.

### 4.6 Security Gaps

1. **No email verification** — `MustVerifyEmail` is commented out with a TODO note. Any email address, including non-existent ones, can be used to register. Combined with a future billing system, this is a fraud risk.

2. **Signed invoice URLs** — Correctly use `URL::temporarySignedRoute()` with 30-day expiry. ✅

3. **No email enumeration protection** — Laravel's default password reset may expose whether an email is registered. This is standard Laravel behavior and low priority.

4. **InvoiceMail sends to `$client->email` without validation** — If a client has no email, `Mail::to(null)` may throw silently or loudly depending on the driver.

---

## 5. Risk Assessment

### 5.1 Pre-Launch Risks

| Risk | Severity | Likelihood | Impact |
|---|---|---|---|
| InvoiceMail is synchronous — SMTP timeout blocks user | 🔴 High | High | Bad UX, lost sends |
| Email verification disabled — fake email registrations | 🔴 High | High | Spam, data integrity |
| InvoiceReminderMail hardcodes HTML — not admin-editable | 🟡 Medium | High | Support burden |
| No Quote emails — clients receive no notification | 🔴 High | Certain | Clients confused, trust damage |
| Debt alerts are DB-only — offline users miss them | 🟡 Medium | High | User value loss |
| No Invoice Paid email to client — payment process incomplete | 🔴 High | Certain | Professional credibility damage |

### 5.2 Post-Launch Risks

| Risk | Severity | Notes |
|---|---|---|
| No subscription emails | 🟡 Medium | No billing yet — low immediate risk |
| No rate limiting | 🟡 Medium | Mass reminder sends could trigger spam filters |
| No unsubscribe mechanism | 🟡 Medium | CAN-SPAM/GDPR compliance gap |
| No email send audit log | 🟟 Low | Hard to debug "did the email go out?" |
| All emails hardcoded Arabic | 🟟 Low | Acceptable for MVP if market is Arabic-only |

---

## 6. Email-Only Launch Readiness Score

### Score by Category

| Category | Weight | Score | Weighted |
|---|---|---|---|
| Welcome & Onboarding | 15% | 8/10 | 1.20 |
| Authentication Emails | 10% | 5/10 (reset ✅, verification ❌ disabled) | 0.50 |
| Invoice Emails | 25% | 6/10 (send ✅, reminder ✅ but broken template, paid ❌) | 1.50 |
| Quote Emails | 20% | 0/10 (zero implementation) | 0.00 |
| CRM / Follow-up Emails | 15% | 0/10 (DB only) | 0.00 |
| Email Infrastructure | 15% | 7/10 (template system ✅, queue inconsistency ❌) | 1.05 |
| **Total** | **100%** | | **4.25 / 10** |

**Email-Only Launch Readiness: 42.5%**

### Interpretation

A score of 42.5% means the email system covers roughly half of what a professional SaaS needs at launch. The core welcome and invoice-send flows work. Everything client-facing beyond the initial invoice send is broken or missing.

---

## 7. Top Priorities Before Launch

Ranked by impact on first-customer experience:

| # | Task | Effort | Impact | Priority |
|---|---|---|---|---|
| 1 | Re-enable `MustVerifyEmail` in `User.php` + add template key `email_verification` | 2h | 🔴 Critical | P0 |
| 2 | Queue `InvoiceMail` — wrap in `SendInvoiceEmailJob` | 3h | 🔴 Critical | P0 |
| 3 | Create `QuoteMail` for quote-sent-to-client (PDF optional) | 4h | 🔴 Critical | P0 |
| 4 | Fix `InvoiceReminderMail` to use `EmailTemplate::render('invoice_reminder')` | 2h | 🟡 High | P1 |
| 5 | Create "Invoice Paid" email to client (trigger: invoice marked paid) | 3h | 🟡 High | P1 |
| 6 | Add email channel to `DebtDueSoonNotification` + `DebtOverdueNotification` | 2h | 🟡 High | P1 |
| 7 | Add email channel to `FollowUpReminderNotification` | 1h | 🟡 High | P1 |
| 8 | Register email templates in DB seeder: `email_verification`, `invoice_reminder`, `invoice_paid`, `quote_send` | 2h | 🟡 High | P1 |
| 9 | Validate `$client->email` before `Mail::to()` in InvoiceMail | 1h | 🟡 Medium | P2 |
| 10 | Add `invoice_mail_logs` or reuse `invoice_reminder_logs` for tracking manual sends | 2h | 🟟 Low | P3 |

**Minimum viable email set for launch: Tasks 1–8 (~20h)**

---

## 8. Phase A / B / C Email Roadmap

### Phase A — Launch Blocker (Before any paying customer) — ~20h

**Goal:** Close the critical gaps that directly affect client trust and user data integrity.

| Task | File | Est. |
|---|---|---|
| Re-enable `MustVerifyEmail` | `app/Models/User.php` | 0.5h |
| Add `email_verification` EmailTemplate seed | DB seeder | 0.5h |
| Create `SendInvoiceEmailJob` (queued) | `app/Jobs/` | 2h |
| Create `QuoteMail` + `SendQuoteEmailJob` | `app/Mail/` + `app/Jobs/` | 4h |
| Add `quote_send` EmailTemplate | DB seeder | 0.5h |
| Fix `InvoiceReminderMail` → use EmailTemplate | `app/Mail/InvoiceReminderMail.php` | 1h |
| Add `invoice_reminder` EmailTemplate | DB seeder | 0.5h |
| Create `InvoicePaidMail` or add `InvoicePaidNotification` with email channel | `app/Mail/` | 2h |
| Add `invoice_paid` EmailTemplate | DB seeder | 0.5h |
| Add email channel to `DebtDueSoonNotification` | `app/Notifications/` | 1h |
| Add email channel to `DebtOverdueNotification` | `app/Notifications/` | 1h |
| Add email channel to `FollowUpReminderNotification` | `app/Notifications/` | 1h |
| Add `debt_due_soon`, `debt_overdue`, `follow_up_reminder` templates | DB seeder | 1h |
| **Total** | | **~16h** |

### Phase B — First Month Post-Launch — ~15h

**Goal:** Complete the professional email experience. Make every lifecycle event traceable.

| Task | Est. |
|---|---|
| Quote accepted / rejected emails to sender (user) | 2h |
| Quote expiring soon reminder (scheduled) | 2h |
| Email change re-verification on profile update | 1h |
| Add email send audit log table (`email_send_logs`) | 3h |
| Resend email button for invoices (with cooldown) | 2h |
| Admin notification on new user registration | 1h |
| Manual send deduplication warning for `InvoiceMail` | 2h |
| Add `quote_accepted`, `quote_rejected`, `quote_expiring` templates | 2h |
| **Total** | | **~15h** |

### Phase C — Growth Phase — ~20h

**Goal:** Compliance, deliverability, and marketing-grade email.

| Task | Est. |
|---|---|
| Subscription lifecycle emails (when payment provider launches) | 4h |
| Unsubscribe mechanism + preference center | 4h |
| Rate limiting on bulk sends (invoice reminders) | 2h |
| Email bounce/failure webhook handling (if using Mailgun/SES) | 4h |
| English language support for email templates | 3h |
| Weekly client activity digest (optional) | 3h |
| **Total** | | **~20h** |

---

## 9. Final Verdict

### If Darahum launched tomorrow, would the current email system be sufficient?

# NO

**Here is why:**

The email system has a working skeleton — the infrastructure is solid (SMTP admin, template engine, branded HTML), and the welcome + invoice-send flows work correctly. But the gaps are too significant for a professional launch:

**1. No Quote emails whatsoever.**
The Quotes module is complete, but clients receive no email when a quote is sent to them. A freelancer sends a quote and the client never gets notified. This is the most fundamental B2B workflow and it is completely broken from an email perspective.

**2. No "Invoice Paid" email to the client.**
When an invoice is marked as paid, the client receives no confirmation. In a professional invoicing tool, a payment receipt is expected. Without it, clients will contact the freelancer asking "did you receive my payment?"

**3. Email verification is disabled.**
The codebase has a commented-out `TODO: re-enable before launch`. If this goes live as-is, anyone can register with any email address — including fake ones — and gain full access to the platform. Combined with future billing, this is a real fraud vector.

**4. Debt and follow-up alerts are invisible to offline users.**
These reminders run on a scheduler, but send only database notifications. A user who checks in once a week misses critical financial alerts entirely.

**5. InvoiceReminderMail bypasses the template system.**
The admin can edit the invoice-send template but cannot edit the reminder template. This inconsistency will confuse both admins and developers, and makes the reminder email uneditable without a code deploy.

**6. InvoiceMail is synchronous.**
Sending an invoice blocks the HTTP request. If the SMTP server responds slowly, the user sees a loading spinner for 10-30 seconds. This is unacceptable UX for a paid product.

---

### What "sufficient for launch" would look like

The gap is not large. Fixing the six issues above requires approximately **16–20 hours of focused work** (Phase A). The infrastructure is already in place. The EmailTemplate system is well-designed. The pattern for Mailables is established. What is missing is coverage — more classes following the same pattern.

**Phase A closes the launch blocker.** After Phase A, Darahum's email system would be at roughly 75/100 — sufficient for the first 10–50 customers.

---

*Audit complete. All findings based on direct code inspection of the Darahum codebase as of 8 Jun 2026.*
