# CONVERSION-01 Phase 2 Report
# Email Verification Grace Period for Paid Users

**Date:** 2026-06-27
**Status:** ✅ Completed — DB migration + model methods + service hook + UI banner + email change guards.

---

## Overview

Phase 2 adds a 7-day grace period so that a user who pays before verifying their email is not immediately blocked by the `verified` middleware. The grace period is granted once per account lifetime, shows a dismissible warning banner, and is cleared if the user changes their email address.

**Design principle:** Zero changes to routes, middleware, payment gateway, or registration flow. One override (`hasVerifiedEmail()`) propagates through every protected route automatically.

---

## Files Changed

| File | Change |
|---|---|
| `database/migrations/2026_06_27_000001_add_email_verification_grace_to_users_table.php` | NEW — two nullable timestamp columns on `users` |
| `app/Models/User.php` | `$fillable` + `casts()` + `hasVerifiedEmail()` override + 3 helper methods |
| `app/Modules/Billing/Services/SubscriptionService.php` | `activatePlan()` — set grace columns after first paid activation |
| `resources/views/layouts/app.blade.php` | Grace period warning banner (full-width, above sidebar) |
| `app/Http/Controllers/SettingsController.php` | Clear `grace_until` on email change |
| `app/Http/Controllers/ProfileController.php` | Clear `grace_until` on email change |

---

## Migration

**File:** `database/migrations/2026_06_27_000001_add_email_verification_grace_to_users_table.php`

```sql
-- up
ALTER TABLE users
  ADD COLUMN email_verification_grace_until    TIMESTAMP NULL AFTER email_verified_at,
  ADD COLUMN email_verification_grace_used_at  TIMESTAMP NULL AFTER email_verification_grace_until;

-- down
ALTER TABLE users
  DROP COLUMN email_verification_grace_until,
  DROP COLUMN email_verification_grace_used_at;
```

### Column semantics

| Column | NULL | NOT NULL |
|---|---|---|
| `email_verification_grace_until` | No active grace / grace expired | Grace deadline (future = still active) |
| `email_verification_grace_used_at` | Grace never granted | Timestamp of first grace grant — never reset |

`grace_used_at` is a one-time flag. Even if the user verifies their email, renews, or the grace expires, this column stays set. A second `activatePlan()` call will NOT grant a new 7-day window.

---

## User Model (`app/Models/User.php`)

### Fillable additions

```php
'email_verification_grace_until',    // CONVERSION-01 Phase 2
'email_verification_grace_used_at',  // CONVERSION-01 Phase 2
```

### Cast additions

```php
'email_verification_grace_until'   => 'datetime',
'email_verification_grace_used_at' => 'datetime',
```

### `hasVerifiedEmail()` — override of MustVerifyEmail

```php
public function hasVerifiedEmail(): bool
{
    // (أ) normal — verified
    if ($this->email_verified_at !== null) {
        return true;
    }

    // (ب) paid grace — still active
    if ($this->email_verification_grace_until !== null
        && $this->email_verification_grace_until->isFuture()) {
        return true;
    }

    return false;
}
```

`EnsureEmailIsVerified` middleware calls this method directly. No route or middleware changes needed.

### Helper methods

```php
// Is the user currently inside an active paid grace window?
public function isInEmailVerificationGrace(): bool

// Was grace ever granted for this account? (lifetime one-time check)
public function hasUsedEmailVerificationGrace(): bool

// Days remaining in grace (min 1). Used by the banner.
public function graceDaysRemaining(): int
```

---

## SubscriptionService (`activatePlan()`)

Condition to grant grace — both must be true:
1. `$user->email_verified_at === null` — email not yet verified
2. `! $user->hasUsedEmailVerificationGrace()` — grace never granted before

```php
if ($user->email_verified_at === null && ! $user->hasUsedEmailVerificationGrace()) {
    $userUpdate['email_verification_grace_until']   = now()->addDays(7);
    $userUpdate['email_verification_grace_used_at'] = now();
}
$user->update($userUpdate);
```

This runs inside the existing `$user->update()` call — atomic, no extra DB round-trip.

---

## Grace Period Warning Banner (`layouts/app.blade.php`)

Placed as a full-width bar before the sidebar+content wrapper (same tier as impersonation and phone banners):

```blade
@auth
@if(auth()->user()->isInEmailVerificationGrace())
    {{-- amber bar showing $graceDaysText remaining --}}
    {{-- CTA: route('verification.notice') --}}
    {{-- Resend: POST route('verification.send') --}}
@endif
@endauth
```

### Days remaining copy logic

| Days left | Arabic copy |
|---|---|
| ≥ 7 | `7 أيام` |
| 3–6 | `X أيام` |
| 2 | `يومين` |
| 1 | `يوم واحد` |

The banner is **not dismissible** (unlike phone notice). It remains visible on every page until the user verifies or the grace expires.

---

## Email Change Guards

When a user changes their email, the old verification becomes invalid. We clear `grace_until` so the grace window does not carry over to the new (unverified) email. `grace_used_at` is preserved — the one-time rule stays enforced.

### SettingsController (`updateProfile()`)

```php
if ($data['email'] !== $user->email) {
    $data['email_verified_at'] = null;
    $data['email_verification_grace_until'] = null; // clear grace for new email
    // grace_used_at intentionally not cleared
}
$user->update($data);
```

### ProfileController (`update()`)

```php
if ($request->user()->isDirty('email')) {
    $request->user()->email_verified_at = null;
    $request->user()->email_verification_grace_until = null; // clear grace for new email
}
$request->user()->save();
```

---

## State Transitions

```
User registers (Free)
│
├── [Free user, never pays]
│       grace_until      = null
│       grace_used_at    = null
│       hasVerifiedEmail → depends on email_verified_at only
│
└── [Pays for Pro/Business — email NOT yet verified]
        activatePlan() called
        → grace_until    = now() + 7 days
        → grace_used_at  = now()
        hasVerifiedEmail → true (grace active)
        Banner visible  → "يرجى تأكيد بريدك خلال 7 أيام"
        │
        ├── [User verifies within 7 days]
        │       email_verified_at = now()
        │       hasVerifiedEmail  → true (verified normally)
        │       Banner disappears (isInEmailVerificationGrace = false)
        │       grace_used_at remains set (immutable)
        │
        ├── [User ignores — day 7 passes]
        │       grace_until      = past timestamp
        │       hasVerifiedEmail → false (grace expired, not verified)
        │       middleware intercepts → verification.notice
        │
        ├── [User changes email during grace]
        │       grace_until cleared → null
        │       email_verified_at  → null
        │       hasVerifiedEmail   → false (no grace, not verified)
        │       New verification email sent (existing flow)
        │
        └── [User renews subscription after verifying]
                grace_used_at = already set
                activatePlan() condition fails → no new grace granted
                (user already verified anyway — no effect)
```

---

## What Was NOT Changed

- Routes — no new routes
- Middleware — no changes to `verified` or any group
- Payment gateway — Togo integration unchanged
- Registration flow — `RegisterUserAction`, `RegisteredUserController` unchanged
- Email verification controller — `VerifyEmailController` unchanged
- Admin panel — Filament access unaffected

---

## Regression Risks

| Risk | Severity | Status |
|---|---|---|
| Free users see banner | 🔴 Critical | ✅ `isInEmailVerificationGrace()` requires both unverified AND grace_until future — impossible for Free users |
| Already-verified paid users see banner | 🔴 Critical | ✅ `email_verified_at !== null` → `isInEmailVerificationGrace()` returns false |
| Second subscription activation grants new grace | 🟡 Medium | ✅ `hasUsedEmailVerificationGrace()` check blocks it |
| Email change exploits grace | 🟡 Medium | ✅ Both SettingsController + ProfileController clear `grace_until` |
| `floatDiffInDays` returns 0 on last day | 🟢 Low | ✅ `max(1, ...)` in `graceDaysRemaining()` ensures minimum 1 |
| Existing unverified users get grace on first login | 🟢 None | ✅ `activatePlan()` is the only grant point — not login |
| Admin impersonation shows banner for grace users | 🟢 Info | ✅ Banner shows for impersonated user — expected and correct |

---

## Phase 1 + Phase 2 Combined Flow

```
Guest clicks "ابدأ الآن" (Pro card)
→ /register?plan=pro&cycle=monthly        [Phase 1]
→ Hidden fields: plan_intent=pro
RegisterUserAction:
  create user (Free plan)
  fire Registered event → verification email sent
  Auth::login($user)
  session(['paid_intent' => ['plan'=>'pro','cycle'=>'monthly']])  [Phase 1]
RegisteredUserController:
  → redirect(billing.upgrade)              [Phase 1]
billing.upgrade shows:
  Intent banner + cycle pre-selected       [Phase 1]
User pays → Togo callback → activatePlan()
  email_verified_at = null → grace_until = now()+7, grace_used_at = now()  [Phase 2]
  subscription_plan → Pro
User redirected to dashboard:
  hasVerifiedEmail() → true (grace active) [Phase 2]
  Banner visible: "تأكيد البريد خلال 7 أيام"
User opens email, clicks link → verified
  email_verified_at = now()
  Banner disappears
```

Steps to pay: **Register → land on billing pre-selected → pay → use app** = 3 steps (vs. 5 before Phase 1).
Email verification: deferred 7 days — does NOT block dashboard access after payment.

---

## Git Commit Message

```
feat(conversion): CONVERSION-01 Phase 2 — email verification grace period

Add a 7-day grace period for paid users who have not yet verified their
email, so the verified middleware does not block them immediately after
payment.

Changes:
- migration: email_verification_grace_until + email_verification_grace_used_at
  nullable timestamps on users table

- User model:
  - fillable + casts for both grace columns
  - override hasVerifiedEmail(): true if verified OR grace_until is future
  - isInEmailVerificationGrace(): unverified + grace_until future
  - hasUsedEmailVerificationGrace(): one-time flag check
  - graceDaysRemaining(): days left for banner display (min 1)

- SubscriptionService::activatePlan(): after activating paid plan,
  if email_verified_at = null AND grace never used → set grace for 7 days

- layouts/app.blade.php: full-width amber warning banner when user is in
  grace — shows days remaining, verify CTA, resend button

- SettingsController + ProfileController: clear email_verification_grace_until
  on email change (grace_used_at preserved — one-time rule stays)

No route changes. No middleware changes. No payment logic changes.
```
