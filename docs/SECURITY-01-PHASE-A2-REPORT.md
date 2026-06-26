# SECURITY-01-PHASE-A2-REPORT
**Sprint:** SECURITY-01 — Password Reset Route Throttling  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 1**

### `routes/auth.php`

```diff
  Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
+     ->middleware('throttle:6,1')
      ->name('password.email');

  Route::post('reset-password', [NewPasswordController::class, 'store'])
+     ->middleware('throttle:5,1')
      ->name('password.store');
```

---

## Routes Protected

| Method | URI | Throttle | Limit |
|---|---|---|---|
| `POST` | `/forgot-password` | `throttle:6,1` | 6 requests / minute / IP |
| `POST` | `/reset-password` | `throttle:5,1` | 5 requests / minute / IP |

**Rationale for limits:**
- `POST /forgot-password` → 6/min matches the email verification resend limit already set on `POST /email/verification-notification`. Consistent policy across all email-sending endpoints.
- `POST /reset-password` → 5/min is tighter because this route processes a token + potentially sets a new password. A lower ceiling reduces token-bruteforce exposure (though signed tokens make this negligible, defence-in-depth).

---

## Routes Not Changed

| Method | URI | Status |
|---|---|---|
| `GET` | `/forgot-password` | Unchanged — no throttle (display only) |
| `GET` | `/reset-password/{token}` | Unchanged — no throttle (display only) |
| `POST` | `/register` | Unchanged — `throttle:10,1` |
| `POST` | `/login` | Unchanged — `throttle:10,1` |
| `GET` | `/verify-email/{id}/{hash}` | Unchanged — `signed + throttle:6,1` |
| `POST` | `/email/verification-notification` | Unchanged — `throttle:6,1` |
| `POST` | `/logout` | Unchanged — no throttle |

No controllers, request classes, or any other file was modified.

---

## Verification

### Route grep confirmation

```
routes/auth.php throttle lines (grep -n "throttle"):
Line 19:  ->middleware('throttle:10,1');   ← POST /register       (unchanged)
Line 25:  ->middleware('throttle:10,1');   ← POST /login          (unchanged)
Line 31:  ->middleware('throttle:6,1')     ← POST /forgot-password ✅ NEW
Line 38:  ->middleware('throttle:5,1')     ← POST /reset-password  ✅ NEW
Line 47:  ->middleware(['signed', 'throttle:6,1'])  ← GET /verify-email/{id}/{hash} (unchanged)
Line 51:  ->middleware('throttle:6,1')     ← POST /email/verification-notification (unchanged)
```

### How Laravel resolves the throttle key

Laravel's default `ThrottleRequests` middleware uses `IP address` as the key for unauthenticated routes (no `auth` middleware active). Both routes are inside `Route::middleware('guest')`, so the key is the requester's IP. A bot must rotate IPs to exceed the limit.

### Laravel's internal reset limiter (still active)

`Password::sendResetLink()` internally checks a 60-second cooldown per email address via the `password_reset_tokens` table timestamp. This internal limiter is additive — both layers apply independently:

- Route throttle: max 6 requests/minute per **IP** (catches volume attacks)
- Laravel internal: 1 email per 60 seconds per **email address** (catches targeted attacks on a specific inbox)

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Legitimate user hitting 6/min on forgot-password | 🟢 None | 6 requests in 60 seconds is unreachable in normal use |
| CI/automated tests hitting throttle | 🟡 Low | Tests should use `WithoutMiddleware` trait or mock throttle — standard Laravel testing practice |
| Reset flow broken by throttle | 🟢 None | Controller and request class untouched; throttle only gates entry |
| GET routes blocked | 🟢 None | Throttle added only to POST routes; GET routes have no throttle |

---

## Git Commit Message

```
security: add route-level throttle to password reset endpoints (SECURITY-01 / Phase A2)

- POST /forgot-password: throttle:6,1 (6 req/min per IP)
- POST /reset-password:  throttle:5,1 (5 req/min per IP)

Previously these routes had no route-level protection. Laravel's internal
Password::sendResetLink() limiter (1 email/60s per address) remains active
as a second layer. GET routes are unchanged.

Refs: SECURITY-01-AUDIT-REPORT.md, SECURITY-01-PHASE-A2-REPORT.md
```
