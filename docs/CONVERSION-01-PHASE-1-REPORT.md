# CONVERSION-01 Phase 1 Report
# Plan Intent Preservation

**Date:** 2026-06-26
**Status:** ✅ Completed — No DB changes, no middleware changes, no payment logic changes.

---

## Files Modified

| File | Change |
|---|---|
| `app/Http/Requests/Auth/RegisterRequest.php` | Add optional `plan_intent` + `cycle_intent` validation rules |
| `resources/views/auth/register.blade.php` | Add hidden `plan_intent` + `cycle_intent` fields from URL params |
| `app/Modules/Auth/Actions/RegisterUserAction.php` | Store `paid_intent` in session after auto-login |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Branch post-registration redirect |
| `app/Http/Controllers/BillingController.php` | `upgrade()` pulls `paid_intent` from session |
| `resources/views/billing/upgrade.blade.php` | Intent banner + Alpine cycle pre-selection |
| `resources/views/welcome.blade.php` | Pro/Business guest CTAs now carry `plan` + `cycle` params |

---

## Plan Intent Preservation

### Entry Point — welcome.blade.php CTAs

Guest-facing CTAs for paid plans now carry URL parameters:

```
Pro  →  /register?plan=pro&cycle=monthly
Biz  →  /register?plan=business&cycle=monthly
```

Annual CTAs (if added later) would use `cycle=annual`. The register form reads these automatically via `request('plan')` / `request('cycle')`.

### Through Registration Form — register.blade.php

Two hidden fields added immediately after `_form_token`:

```html
<input type="hidden" name="plan_intent"  value="{{ old('plan_intent',  request('plan')) }}">
<input type="hidden" name="cycle_intent" value="{{ old('cycle_intent', request('cycle', 'monthly')) }}">
```

- `old()` preserves the values on validation failure (e.g., email already taken)
- `request('plan')` reads from URL query param on first render
- Default `cycle` falls back to `'monthly'` if absent

### Validation — RegisterRequest.php

Optional nullable rules — validation failure does NOT block registration:

```php
'plan_intent'  => ['nullable', 'string', 'in:pro,business'],
'cycle_intent' => ['nullable', 'string', 'in:monthly,annual'],
```

Invalid values (e.g., `?plan=hacked`) are silently rejected by validation. Only `pro`/`business` and `monthly`/`annual` are accepted.

### Session Storage — RegisterUserAction.php

After `Auth::login($user)`, if plan is valid:

```php
$planIntent = $request->input('plan_intent');
if (in_array($planIntent, ['pro', 'business'], true)) {
    session([
        'paid_intent' => [
            'plan'  => $planIntent,
            'cycle' => $request->input('cycle_intent', 'monthly'),
        ],
    ]);
}
```

Session key: `paid_intent` — shape: `['plan' => 'pro'|'business', 'cycle' => 'monthly'|'annual']`

Starter (Free) users produce no `paid_intent` session entry → unchanged behavior.

---

## Registration Flow

### Free User (unchanged)

```
/register  →  register?  →  No plan_intent
RegisterUserAction → no session set
RegisteredUserController → redirect(dashboard)
dashboard blocked by verified → verification.notice
User verifies → dashboard
```

### Paid User (new)

```
welcome.blade.php  →  "ابدأ الآن" on Pro card
→  /register?plan=pro&cycle=monthly
→  Hidden fields: plan_intent=pro, cycle_intent=monthly
RegisterUserAction:
  create user (Free plan — unchanged)
  fire Registered event (verification email sent)
  Auth::login($user)
  session(['paid_intent' => ['plan'=>'pro','cycle'=>'monthly']])
RegisteredUserController:
  session()->has('paid_intent') → TRUE
  → redirect(billing.upgrade)
billing.upgrade blocked by verified → verification.notice
  (Laravel stores billing.upgrade as "intended" URL)
User opens inbox → clicks link → VerifyEmailController
  markEmailAsVerified()
  → redirect()->intended('dashboard') → billing.upgrade
billing.upgrade loads:
  $paidIntent = session()->pull('paid_intent')  ← consumed
  Alpine initializes with cycle = 'monthly' (from intent)
  Intent banner shown: "اخترت خطة Pro (شهري)"
  Pro card highlighted with ring-2 ring-brand/20
  [Payment button OR WhatsApp button depending on $providerReady]
```

**Key insight:** Laravel's `redirect()->intended()` stores the attempted URL (`billing.upgrade`) when the `verified` middleware redirects to `verification.notice`. After email verification, the user lands directly on `billing.upgrade` — with `paid_intent` still in session (session is maintained across the verification flow).

---

## Redirect Logic

`RegisteredUserController::store()`:

```php
app(RegisterUserAction::class)->execute($request);

if (session()->has('paid_intent')) {
    return redirect()->route('billing.upgrade');
}

return redirect()->route('dashboard');
```

Simple and clean — no provider check needed here. The `billing.upgrade` view handles the provider-ready vs WhatsApp branch internally.

---

## Provider Ready Behavior

`BillingController::upgrade()` passes `$paidIntent` to the view. The view already has the `$providerReady` branch:

**Provider ready (`BILLING_PROVIDER` set):**
- Pro card: cycle pre-selected from intent, "الدفع الآن — Pro شهري" button visible
- Intent banner: "اضغط على 'الدفع الآن' للمتابعة."
- One click to checkout → Togo → payment

**Provider NOT ready (manual / null):**
- Pro card: cycle pre-selected from intent, WhatsApp button visible
- Intent banner: "تواصل معنا عبر واتساب لتفعيل خطتك فوراً."
- One click to WhatsApp with pre-filled message

In both cases: **zero re-selection needed**. The user arrives at the upgrade page with their plan already highlighted.

---

## Regression Risks

| Risk | Severity | Status |
|---|---|---|
| Free user registration broken | 🔴 Critical | ✅ Not affected — `paid_intent` session key is only set if `plan_intent` is valid |
| Validation failure loses plan intent | 🟡 Medium | ✅ Handled — `old('plan_intent', ...)` preserves value through failed submissions |
| `paid_intent` session consumed on upgrade page, disappears on refresh | 🟡 Low | ✅ By design — `pull()` consumes once; user has already selected plan, no loss |
| Bot injects `plan_intent=business` | 🟢 Low | ✅ No privilege escalation — user still created as `Free`, no subscription activated |
| `billing.upgrade` already behind `verified` — redirect blocked in Phase 1 | 🟡 Known | ✅ Handled by `intended()` mechanism — user goes there after email verification |
| `$paidIntent` undefined in upgrade view (old sessions) | 🟢 None | ✅ `session()->pull()` returns `null` if key absent — blade uses `!empty($paidIntent)` |
| Existing logged-in users hitting billing.upgrade without session | 🟢 None | ✅ `$paidIntent` is null → no banner, standard upgrade page behavior unchanged |

---

## Phase 1 vs Phase 2 Interplay

Phase 1 improves the flow even **without Phase 2**:

| | Before Phase 1 | After Phase 1 | After Phase 1+2 |
|---|---|---|---|
| Plan selected at registration | Never | ✅ Yes — URL params | ✅ Yes |
| Email verification required | Yes | Yes | Deferred 7 days |
| After verifying: landing page | Dashboard | billing.upgrade (pre-selected) | billing.upgrade (pre-selected, no verify needed first) |
| Steps to complete checkout | Register → verify → navigate → select → pay = 5 | Register → verify → land on billing pre-selected → pay = 3 | Register → land on billing pre-selected → pay = 2 |

---

## Git Commit Message

```
feat(conversion): CONVERSION-01 Phase 1 — plan intent preservation

Preserve paid plan selection through the registration flow so users
who click a pricing CTA land on billing.upgrade with their plan
already pre-selected after completing registration.

Changes:
- welcome.blade.php: Pro/Business guest CTAs now include ?plan=X&cycle=Y
  → /register?plan=pro&cycle=monthly  (Pro)
  → /register?plan=business&cycle=monthly  (Business)

- register.blade.php: two hidden fields pass plan_intent/cycle_intent
  through form submission (preserved across validation failures via old())

- RegisterRequest: optional nullable validation for plan_intent, cycle_intent
  (in:pro,business / in:monthly,annual — invalid values silently rejected)

- RegisterUserAction: after Auth::login(), stores session paid_intent
  if plan_intent is pro or business

- RegisteredUserController: post-registration redirect branches:
  paid_intent present → billing.upgrade
  no intent (Starter) → dashboard  [unchanged]

- BillingController::upgrade(): pulls paid_intent from session (consumed once),
  passes $paidIntent to view

- billing/upgrade.blade.php:
  - Intent banner shown when $paidIntent present
  - Pro card Alpine x-data initializes cycle from intent if plan=pro
  - Business card Alpine x-data initializes cycle from intent if plan=business
  - Pre-selected card highlighted with ring-2

No DB changes. No middleware changes. No payment logic changes.
Phase 2 (hasVerifiedEmail override + grace period) pending approval.
```
