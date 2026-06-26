# CONVERSION-01 Audit Report
# Deferred Email Verification For Paid Users

**Date:** 2026-06-26
**Status:** AUDIT ONLY — no code changed
**Scope:** Inspection of verification flow, billing routes, registration, checkout, and activation hooks

---

## 1. Current Verification Flow

```
Guest
  ↓ fills /register
RegisteredUserController::store()
  ↓ runs RegisterUserAction::execute()
    ↓ User::create()  [subscription_plan = Free]
    ↓ event(new Registered($user))  ← triggers verification email automatically
    ↓ createDefaultCategories()
    ↓ SendWelcomeEmailJob::dispatch()
    ↓ Auth::login($user)
  ↓ return redirect()->route('dashboard')
  ↓
BLOCKED — Route::middleware(['auth', 'verified']) on dashboard
  ↓
EnsureEmailIsVerified middleware runs
  $request->user()->hasVerifiedEmail()  → false
  ↓
redirect('verification.notice')  [auth.verify-email view]
  ↓
User opens inbox, clicks signed URL
  ↓
VerifyEmailController::__invoke()
  markEmailAsVerified()  → email_verified_at = now()
  event(new Verified($user))
  ↓
redirect(dashboard?verified=1)
```

**Delay introduced: user is stuck at verification gate before reaching checkout.**

---

## 2. Where `verified` Middleware Is Enforced

There is **one large route group** in `routes/web.php` (line ~50):

```php
Route::middleware(['auth', 'verified'])->group(function () { ... });
```

This single group covers **every protected route** including:

| Route | Name | Problem |
|---|---|---|
| `/dashboard` | `dashboard` | Blocked until verified |
| `/billing` | `billing.index` | **Blocked** — paid user can't see billing |
| `/billing/upgrade` | `billing.upgrade` | **Blocked** — can't reach plan selection |
| `/billing/checkout` | `billing.checkout` | **Blocked** — checkout POST inaccessible |
| `/billing/togo/pending` | `billing.togo.pending` | **Blocked** — pre-payment confirmation page |
| `/billing/togo/callback` | `billing.togo.callback` | **Blocked** — payment return URL (**critical**) |
| `/billing/togo/cancel` | `billing.togo.cancel` | **Blocked** |
| `/billing/success` | `billing.success` | Blocked |
| All app routes | — | All blocked |

### Critical Path

The Togo callback URL (`/billing/togo/callback`) is where the payment gateway redirects the user **after paying**. If the user hasn't verified their email yet, this URL throws them to the verify-email page. The payment is recorded in Togo's system, but `togoCallback()` never runs. The subscription is never activated.

### Built-in Middleware Source

`vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php` line 35:

```php
! $request->user()->hasVerifiedEmail()
```

The middleware calls `hasVerifiedEmail()` on the User model. **This is the override point.**

---

## 3. Paid User Journey (Current — Broken)

```
Welcome page: user clicks "Pro — اشترك الآن" (currently → /register)
  ↓
/register — No plan/cycle fields exist in RegisterRequest or form
  ↓  ← plan intent is LOST here
User registers → auto-login → redirect(dashboard)
  ↓
BLOCKED by 'verified' middleware → verify-email page
  ↓
User leaves to inbox (friction point #1)
  ↓
Verifies → back to dashboard → navigates to /billing/upgrade
  ↓  ← second navigation friction
Selects plan again (had to re-select)
  ↓
Checkout → Togo → pay → callback → subscription activated
```

**Two blockers exist, not one:**
1. Plan intent is not preserved through registration (no `plan`/`cycle` session)
2. Billing routes require `verified` middleware

---

## 4. Recommended Architecture

### Decision: Option B (enhanced) — `canAccessDashboard()` via `hasVerifiedEmail()` override

**Why not Option A (bypass `verified` middleware on billing routes):**
- Would require removing `verified` from the billing route group or splitting routes
- Inconsistent: user accesses dashboard sections without verifying email
- The callback URL (`togo.callback`) would still silently fail on redirect if session expired

**Why not Option C (set deadline post-payment only):**
- Doesn't solve the pre-payment checkout blockage
- A user can't reach `/billing/checkout` before they've paid

**Why Option B:**
- Single, clean override: `hasVerifiedEmail()` on User model
- Laravel's built-in `EnsureEmailIsVerified` calls this method — no middleware changes needed
- The `verified` route group stays exactly as-is
- Grace period is explicit, auditable, and stored in the DB
- After grace expires, Laravel's built-in flow resumes automatically — no special expiry logic needed

### Architecture Overview

```
Phase 1: Plan Intent Preservation
  - Add optional hidden fields `plan` + `cycle` to register form
  - Store in session after login: session(['paid_intent' => ['plan' => $plan, 'cycle' => $cycle]])
  - After registration, if paid_intent exists → redirect to billing.upgrade (or billing.checkout if provider ready)
  - No RegisterRequest validation change needed (optional fields, validated in BillingController)

Phase 2: Grace Period Mechanism
  - Add users.email_verification_grace_until (TIMESTAMP NULL)
  - Override User::hasVerifiedEmail(): return true if within grace AND email not yet verified
  - Set grace period in SubscriptionService::activatePlan(): user->update(['email_verification_grace_until' => now()->addDays(7)])
  - Show persistent banner in app layout when user is in grace period
  - Separate notification: "تذكير تأكيد البريد" at day 3 and day 6

Phase 3: Grace Expiry Enforcement
  - After day 7: hasVerifiedEmail() returns false again → user hits verify-email page naturally
  - Artisan command (or scheduler): find grace-expired paid users, send final warning at day 6
  - Admin column in UserResource: show "مدفوع / غير موثّق" filter
  - No feature restriction at expiry (just force verification) OR restrict selected gates (decision needed)
```

---

## 5. Grace Period Strategy

### Proposed: 7-day implicit grace after first payment

```
Day 0  (payment) → email_verification_grace_until = now() + 7 days
                   hasVerifiedEmail() returns TRUE
                   User accesses dashboard freely
                   Banner shown: "يرجى تأكيد بريدك الإلكتروني خلال 7 أيام..."

Day 3  → Notification: "تذكير #1: تأكيد البريد الإلكتروني"

Day 6  → Notification: "تذكير #2 (أخير): ستُطبَّق القيود غداً إذا لم تتحقق"

Day 7+ → email_verification_grace_until < now()
          hasVerifiedEmail() returns FALSE again
          User redirected to verify-email page
          Can still log in, can still reach /verify-email
          Cannot access dashboard or billing until verified
```

### `hasVerifiedEmail()` Override Logic

```php
// In User model
public function hasVerifiedEmail(): bool
{
    // Normal case: already verified
    if ($this->email_verified_at !== null) {
        return true;
    }

    // Grace case: within paid grace period
    if ($this->email_verification_grace_until !== null
        && now()->lt($this->email_verification_grace_until)) {
        return true;
    }

    return false;
}
```

This is the **only addition to User model** needed for the grace period mechanism.

---

## 6. Database Changes Required

### New column: `users.email_verification_grace_until`

```php
// Migration: add_email_verification_grace_to_users_table
$table->timestamp('email_verification_grace_until')->nullable()->after('email_verified_at');
```

**Set by:** `SubscriptionService::activatePlan()` — after successful payment:
```php
$user->update([
    'subscription_plan'                  => $plan,
    'email_verification_grace_until'     => $user->email_verified_at ? null : now()->addDays(7),
]);
```

Only sets the grace period if `email_verified_at` is still null — already-verified users are unaffected.

### No other schema changes needed

The `subscriptions` table is unchanged. The `payment_orders` table is unchanged. The plan intent is stored in session (no DB needed for that).

---

## 7. Security Risks

| Risk | Severity | Mitigation |
|---|---|---|
| Disposable email used for paid plan | 🟡 Medium | Already blocked by `RegisterRequest` disposable domain check. Card payment links real identity. |
| Grace period abuse — register → pay → cancel → re-register | 🟡 Medium | Grace period only set on successful payment. Cancelled subscriptions don't reset grace. `email_verification_grace_until` is only set once unless admin overrides. |
| Fraudulent payment triggers grace period | 🟢 Low | Grace only set in `activatePlan()`, which is called only after Togo API confirms `status === 'PAID'`. |
| User changes email during grace period | 🟡 Medium | `SettingsController::updateProfile()` already nullifies `email_verified_at` on email change. Grace period should also be nullified on email change — add to Phase 2. |
| Togo callback blocked by session expiry | 🟢 None (solved by existing code) | `BillingController::resolvePaymentOrder()` already has fallback to `provider_hashed_id` from query string. Callback works even if session expires. |
| Grace user without verified email sends client-facing invoice | 🟡 Low | Invoices can be created. Email delivery requires functional inbox — which the grace period encourages them to verify. Acceptable risk. |
| Admin impersonation of grace-period user grants dashboard access | 🟢 Acceptable | Admin access is already privileged. No regression vs current behavior. |

---

## 8. Conversion Benefits

| Metric | Before | After |
|---|---|---|
| Steps before reaching checkout | Register → Verify → Navigate → Select plan → Pay = **5 steps** | Register (with plan) → Pay = **2 steps** |
| Inbox round-trip before payment | Required | Deferred 7 days |
| Checkout abandonment risk | High — inbox interrupts payment intent | Low — payment intent preserved in session |
| Plan intent lost at registration | Yes (no plan fields) | No (session-preserved) |
| Return rate (unverified paid) | N/A | Recoverable via 2 timed email nudges |
| Admin visibility of risk users | None | "Paid / Unverified" filter in UserResource |

---

## 9. Implementation Plan

### Phase 1 — Plan Intent Preservation (no DB change)

**Scope:** Registration → post-login redirect  
**Files to modify:**
- `resources/views/auth/register.blade.php` — add hidden `plan` + `cycle` fields (optional, from URL params)
- `app/Modules/Auth/Actions/RegisterUserAction.php` — store `paid_intent` in session if plan is paid
- `app/Http/Controllers/Auth/RegisteredUserController.php` — change redirect logic:
  - if `session('paid_intent')` exists → `redirect(route('billing.upgrade'))`
  - else → `redirect(route('dashboard'))`
- `resources/views/welcome.blade.php` — CTAs already updated to `route('register')`, just add `?plan=pro&cycle=monthly` query params
- `app/Http/Controllers/BillingController::upgrade()` — read `paid_intent` from session, pre-select plan in view

**No middleware, route, DB, or payment changes.**

---

### Phase 2 — Grace Period (1 migration, 2 model methods, 1 service call, 1 banner)

**Scope:** Allow paid-but-unverified users to access dashboard + billing  
**Files to modify:**
- `database/migrations/XXXX_add_email_verification_grace_to_users_table.php` — new column
- `app/Models/User.php` — override `hasVerifiedEmail()` + add `isInVerificationGrace()` helper
- `app/Modules/Billing/Services/SubscriptionService::activatePlan()` — set `email_verification_grace_until`
- `resources/views/layouts/app.blade.php` — persistent banner when `isInVerificationGrace()`
- `app/Notifications/VerificationGraceReminderNotification.php` — new notification (days 3 + 6)
- `app/Console/Commands/SendGraceReminders.php` — Artisan command scheduled daily

**No route, middleware, or payment logic changes.**

---

### Phase 3 — Expiry Enforcement + Admin Visibility

**Scope:** Grace-expired users, admin monitoring  
**Files to modify:**
- `app/Filament/Resources/UserResource.php` — add `email_verification_grace_until` column + "Paid & Unverified" filter
- `app/Console/Commands/SendGraceReminders.php` — add day-6 final warning
- Optional: decide whether to restrict any `SubscriptionPlan::can()` gates for grace-expired users (separate decision)

---

## 10. Open Decisions (Require Product Sign-off Before Phase 2)

| # | Question | Options |
|---|---|---|
| 1 | What happens at day 7 if email still unverified? | A) Force verify-email page (block dashboard) · B) Keep access, show error banner, notify admin |
| 2 | Should grace period reset if user resubs after expiry? | A) Yes, new payment = new 7 days · B) No, one grace per account lifetime |
| 3 | What if user changes email during grace? | A) Nullify grace → must verify new email · B) Grace continues, new verification email sent |
| 4 | Restrict any features at day 7+ or just block dashboard? | A) Block dashboard only · B) Also restrict invoice email sending / PDF |
| 5 | Grace period duration | 7 days · 14 days · 30 days |

---

## 11. Files That Will Change (Phase 1 + 2)

```
CREATE:
  database/migrations/XXXX_add_email_verification_grace_to_users_table.php
  app/Notifications/VerificationGraceReminderNotification.php
  app/Console/Commands/SendGraceReminders.php

MODIFY:
  app/Models/User.php                                     ← hasVerifiedEmail() override
  app/Modules/Billing/Services/SubscriptionService.php    ← activatePlan() sets grace
  app/Modules/Auth/Actions/RegisterUserAction.php         ← session('paid_intent')
  app/Http/Controllers/Auth/RegisteredUserController.php  ← redirect logic
  resources/views/auth/register.blade.php                 ← hidden plan/cycle fields
  resources/views/layouts/app.blade.php                   ← grace period banner
  resources/views/welcome.blade.php                       ← CTA URLs with plan params (already done)

DO NOT TOUCH:
  routes/web.php                                          ← verified group stays as-is
  app/Http/Middleware/*                                   ← no middleware changes
  app/Http/Controllers/BillingController.php              ← no payment logic changes
  app/Modules/Billing/Services/TogoPaymentService.php     ← untouched
  database/migrations/2026_05_12_000008_create_subscriptions_table.php
  config/billing.php
```

---

**Awaiting approval to proceed with Phase 1.**
