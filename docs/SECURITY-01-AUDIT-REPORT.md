# SECURITY-01-AUDIT-REPORT.md
**Sprint:** SECURITY-01 — Registration Spam Protection Audit  
**Date:** 2026-06-26  
**Status:** Audit only — no code modified

---

## Auth Stack

**Package:** `laravel/breeze ^2.4` + `laravel/sanctum ^4.0`  
**Pattern:** Breeze scaffolding with a custom Action layer on top.

| Layer | File |
|---|---|
| Routes | `routes/auth.php` (Breeze-generated) |
| Register Controller | `app/Http/Controllers/Auth/RegisteredUserController.php` |
| Register Logic | `app/Modules/Auth/Actions/RegisterUserAction.php` |
| Register Validation | `app/Http/Requests/Auth/RegisterRequest.php` |
| Login Controller | `app/Http/Controllers/Auth/AuthenticatedSessionController.php` |
| Login Validation + Rate Limit | `app/Http/Requests/Auth/LoginRequest.php` |
| Password Reset | `app/Http/Controllers/Auth/PasswordResetLinkController.php` |
| Email Verification | `app/Http/Controllers/Auth/VerifyEmailController.php` |

The standard Breeze controller/view files are all present. The register flow has been extended with a custom `RegisterUserAction` that creates default categories and dispatches a welcome email job.

---

## Current Register Flow

**Route:** `POST /register` → `RegisteredUserController@store` → `RegisterUserAction::execute()`

**Throttle on route:** `throttle:10,1` (10 requests per minute, per IP)

**Validation rules (`RegisterRequest`):**
- `name` — required, string, max:255
- `email` — required, lowercase, email, unique:users
- `password` — required, confirmed, `Password::defaults()`
- `currency` — required, in: SAR/USD/EUR/GBP/AED/KWD
- `timezone` — required, timezone:all

**What RegisterUserAction does:**
1. Creates user with `subscription_plan = Free`
2. Fires `Registered` event
3. Creates 12 default categories
4. Dispatches `SendWelcomeEmailJob` (5-second delay)
5. Calls `Auth::login($user)` — **user is logged in immediately**

**What is NOT present:**
- No CAPTCHA (reCAPTCHA, hCaptcha, or Turnstile)
- No honeypot field
- No temporary/disposable email domain check
- No email verification gate before login
- No registration IP/user-agent logged to `users` table

---

## Current Login Flow

**Route:** `POST /login` → `AuthenticatedSessionController@store` → `LoginRequest::authenticate()`

**Throttle on route:** `throttle:10,1` (10 requests/min per IP)

**Additional rate limiting inside `LoginRequest`:**
- Key: `email|IP` (transliterated, lowercased)
- Max attempts: **5** before lockout
- Fires `Lockout` event on breach
- Returns `auth.throttle` message with seconds remaining
- Clears limiter on successful login

Login rate limiting is solid — it uses a combined `email|IP` key so targeting the same email from the same IP blocks after 5 failures.

---

## Current Password Reset Flow

**Routes:**
```
GET  /forgot-password   → no throttle
POST /forgot-password   → NO throttle middleware  ← gap
GET  /reset-password/{token} → no throttle
POST /reset-password    → no throttle middleware
```

**Throttle on route:** ❌ None on either POST route.

Laravel's `Password::sendResetLink()` does have an internal limiter (1 reset email per 60 seconds per email address), but there is no route-level `throttle:` middleware — a bot can hammer the endpoint from multiple IPs targeting valid emails.

**Email verification resend:** `throttle:6,1` ✅

---

## Current Contact Form

**Route:** `GET /contact` → `fn() => view('marketing.contact')` — static view, no POST route.

The form submission in `public/marketing/js/contact.js` calls `e.preventDefault()` and performs **client-side validation only**. There is no server-side endpoint handling the contact form. The form does not actually send data to the server.

**Risk level:** Negligible (form is non-functional as a server-side attack surface), but the form appears broken to users.

---

## Email Verification Status

**Column exists:** `email_verified_at` ✅ (in `create_users_table` migration)

**`MustVerifyEmail` interface:** ❌ **COMMENTED OUT**

```php
// app/Models/User.php line 7 & 17:
// use Illuminate\Contracts\Auth\MustVerifyEmail; // TODO: إعادة تفعيله قبل الإطلاق (Phase 13)
class User extends Authenticatable implements FilamentUser // implements MustVerifyEmail
```

**Effect:** Although `routes/web.php` wraps protected routes with `['auth', 'verified']` middleware, Laravel's `EnsureEmailIsVerified` middleware checks `$request->user() instanceof MustVerifyEmail` before enforcing verification. Since `User` does not implement `MustVerifyEmail`, **the `verified` middleware does nothing** — unverified users access the full app.

**`Registered` event is fired** in `RegisterUserAction`, which would trigger a verification email if `MustVerifyEmail` were active. The infrastructure is ready; the interface is just not enabled.

**Verdict: Email verification is entirely unenforced. Any email address, real or fake, gains immediate full access.**

---

## Rate Limiting Status

| Endpoint | Middleware Throttle | Internal Limiter | Notes |
|---|---|---|---|
| `POST /register` | `throttle:10,1` | None | 10/min per IP only — no email+IP key |
| `POST /login` | `throttle:10,1` | 5 attempts (email\|IP key) | Good — dual layer |
| `POST /forgot-password` | ❌ None | Laravel internal: 1/60s per email | Route-level unprotected |
| `POST /reset-password` | ❌ None | None | Unprotected |
| `GET /verify-email/{id}/{hash}` | `throttle:6,1` + `signed` | — | Good |
| `POST /email/verification-notification` | `throttle:6,1` | — | Good |

---

## Users Table — Security Columns Inventory

| Column | Present | Location | Notes |
|---|---|---|---|
| `email_verified_at` | ✅ | `users` table | Not enforced (MustVerifyEmail disabled) |
| `status` (active/suspended) | ✅ | `users` table | Used by `EnsureUserIsActive` middleware |
| `last_login_at` | ❌ | Not in `users` | Not tracked anywhere |
| `last_activity_at` | ❌ | Not in `users` | `last_activity` (int) is in `sessions` table only |
| `ip_address` (registration) | ❌ | Not in `users` | `ip_address` is in `sessions` table — session IP, not registration IP |
| `user_agent` (registration) | ❌ | Not in `users` | Same — `sessions` table only |

The `sessions` table stores `ip_address`, `user_agent`, and `last_activity` for active sessions, but these are session-level and not persisted to the user record. Once a session expires, this data is lost.

---

## Admin UserResource — Security Features Inventory

| Feature | Present | Notes |
|---|---|---|
| `email_verified_at` visible in table | ❌ | Not in any column definition |
| Registration IP in table | ❌ | Not stored on users |
| User-agent in table | ❌ | Not stored on users |
| Suspend account action | ✅ | `suspend` action — sets `status = suspended` |
| Activate account action | ✅ | `activate` action |
| Bulk suspend | ✅ | `suspendAll` bulk action |
| Delete user data action | ✅ | `deleteData` — clears all related records |
| Delete user account | ❌ | Only deletes data, not the user row itself |
| Filter by verified/unverified | ❌ | No such filter |
| Filter by registration date range | ❌ | Only plan + status filters |
| Last login visible | ❌ | Not tracked |

---

## Temporary Email Domain Blocking

**Status: ❌ Not implemented.**

No blocklist, no third-party package check, no DNS MX validation. The `email` validation rule uses Laravel's `email` rule with no additional checks. Addresses from mailinator.com, guerrillamail.com, yopmail.com, 10minutemail.com, etc. are fully accepted.

---

## Spam Indicators Found

Based on the audit, the following conditions make Darahum easy to spam:

1. **No email verification gate** — registrations are instant and fully functional with any email address. A bot can register, explore the app, and consume resources without ever owning the email used.

2. **No CAPTCHA or honeypot** — the register form is entirely submittable by automated scripts with no human check.

3. **Throttle is IP-only, too generous** — `throttle:10,1` allows 10 accounts per minute per IP. With rotating proxies (common in spam tools), this provides zero real protection. A single /16 subnet can generate thousands of accounts per hour.

4. **No temporary email domain check** — disposable email services are the #1 tool used for spam registrations.

5. **Forgot-password unthrottled at route level** — a bot can enumerate email addresses or generate mail-server load at will.

6. **No registration IP recorded on user** — once a spam wave happens, there is no way to query "all users registered from IP x.x.x.0/24" for bulk suspension.

7. **`email_verified_at` visible in DB but never shown in admin** — no easy way to see how many unverified accounts exist or to bulk-action them.

---

## Missing Protections (Prioritized)

1. **Email verification enforcement** — `MustVerifyEmail` is commented out. One line change, massive impact.
2. **CAPTCHA on register form** — Cloudflare Turnstile (free, no JS challenge) or hCaptcha.
3. **Honeypot field** — simple hidden field bots fill in; rejecting the submission costs nothing.
4. **Temporary/disposable email blocklist** — `propaganistas/laravel-disposable-email` package or a local blocklist.
5. **Route-level throttle on forgot-password POST** — same pattern as register (`throttle:6,1`).
6. **`registration_ip` column on `users`** — log IP at registration time for post-hoc bulk actions.
7. **`last_login_at` column on `users`** — identify dormant/bot accounts that never logged in again after registration.
8. **Admin filter: unverified accounts** — add `email_verified_at IS NULL` filter to UserResource.
9. **Admin column: `email_verified_at`** — show badge in UserResource table.
10. **Forgot-password: throttle reset POST** — add `throttle:5,1` to `POST /reset-password` route.

---

## Recommended Implementation Plan

### Phase A — Immediate (no package installs, 1–2 hours)

**A1. Enable MustVerifyEmail** *(highest impact, 2-line change)*
- Uncomment the interface in `User.php`
- The `Registered` event already fires; email will send automatically
- The `verified` middleware on `routes/web.php` will then actually enforce it
- Side effect: existing unverified users will be redirected to `/verify-email`

**A2. Throttle forgot-password and reset-password routes**
- Add `->middleware('throttle:6,1')` to `POST /forgot-password`
- Add `->middleware('throttle:5,1')` to `POST /reset-password`

**A3. Add `registration_ip` to users table**
- New migration: `$table->string('registration_ip', 45)->nullable()`
- Log `request()->ip()` in `RegisterUserAction`

**A4. Add `last_login_at` to users table**
- New migration: `$table->timestamp('last_login_at')->nullable()`
- Log in `AuthenticatedSessionController` on success or via `Login` event listener (already wired in `ActivityLogServiceProvider`)

### Phase B — Short-term (package install, 2–4 hours)

**B1. Honeypot on register form** *(zero UX friction)*
- Package: `msurguy/honeypot` or `spatie/laravel-honeypot`
- Add hidden field to register form; reject if filled

**B2. Temporary email domain blocklist**
- Package: `propaganistas/laravel-disposable-email`
- Add `not_disposable` rule to `RegisterRequest` email field

**B3. Update UserResource**
- Add `email_verified_at` column (with `null` → danger badge)
- Add filter: unverified only
- Add `last_login_at` column
- Add `registration_ip` column

### Phase C — Medium-term (infrastructure change)

**C1. CAPTCHA on register form**
- Cloudflare Turnstile is recommended (free, GDPR-friendly, no image puzzles)
- Validate server-side token in `RegisterRequest`

**C2. Admin: bulk-delete unverified accounts by date**
- Add BulkAction in UserResource: "Delete unverified accounts older than 7 days"

---

## Priority Order

| # | Task | Impact | Effort | Sprint |
|---|---|---|---|---|
| 1 | Enable `MustVerifyEmail` | 🔴 Critical | 5 min | Now |
| 2 | Throttle forgot/reset-password routes | 🟠 High | 5 min | Now |
| 3 | Add `registration_ip` migration + logging | 🟠 High | 30 min | A |
| 4 | Add `last_login_at` migration + logging | 🟡 Medium | 30 min | A |
| 5 | Honeypot on register form | 🟠 High | 1 hr | B |
| 6 | Disposable email blocklist | 🟠 High | 1 hr | B |
| 7 | UserResource: verified badge + filters | 🟡 Medium | 1 hr | B |
| 8 | Cloudflare Turnstile CAPTCHA | 🟡 Medium | 2–3 hr | C |
| 9 | Bulk delete unverified accounts | 🟡 Medium | 1 hr | C |

---

*Report generated by SECURITY-01 audit. No files were modified during this audit.*
