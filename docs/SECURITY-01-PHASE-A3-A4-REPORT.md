# SECURITY-01-PHASE-A3-A4-REPORT
**Sprint:** SECURITY-01 — Registration IP + Last Login Tracking  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 4**

| File | Change |
|---|---|
| `database/migrations/2026_06_26_000004_add_security_metadata_to_users_table.php` | New migration — 4 columns |
| `app/Models/User.php` | Added 4 fields to `$fillable` + `last_login_at` cast |
| `app/Modules/Auth/Actions/RegisterUserAction.php` | Capture `registration_ip` + `registration_user_agent` on create |
| `app/Listeners/Auth/LogUserLogin.php` | Update `last_login_at` + `last_login_ip` on Login event |

---

## Migration Details

**File:** `database/migrations/2026_06_26_000004_add_security_metadata_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('registration_ip', 45)->nullable()->after('onboarding_dismissed_at');
    $table->text('registration_user_agent')->nullable()->after('registration_ip');
    $table->timestamp('last_login_at')->nullable()->after('registration_user_agent');
    $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
});
```

Column decisions:

| Column | Type | Nullable | Rationale |
|---|---|---|---|
| `registration_ip` | `string(45)` | Yes | IPv4 (15 chars) and IPv6 (39 chars) — 45 is the safe upper bound, matches `sessions` table convention |
| `registration_user_agent` | `text` | Yes | UA strings can exceed 255 chars (mobile browsers) |
| `last_login_at` | `timestamp` | Yes | Null = never logged in (spam indicator) |
| `last_login_ip` | `string(45)` | Yes | Same rationale as `registration_ip` |

All columns are nullable — safe to migrate on existing populated tables with no default value issues. `down()` drops all four in one call.

**Run after deploy:**
```bash
php artisan migrate
```

---

## Registration Metadata Capture

**File:** `app/Modules/Auth/Actions/RegisterUserAction.php`

```diff
  $user = User::create([
      'name'                    => $request->name,
      'email'                   => $request->email,
      'password'                => $request->password,
      'currency'                => $request->currency,
      'timezone'                => $request->timezone,
      'subscription_plan'       => SubscriptionPlan::Free,
+     'registration_ip'         => $request->ip(),
+     'registration_user_agent' => $request->userAgent(),
  ]);
```

`$request->ip()` uses Laravel's `Request::ip()` which respects `TrustProxies` middleware — if a proxy/load balancer is configured, the real client IP is returned from `X-Forwarded-For`. `$request->userAgent()` returns the raw `User-Agent` header.

These values are captured **at registration time** and never overwritten — permanent record of the originating client.

---

## Login Metadata Capture

**File:** `app/Listeners/Auth/LogUserLogin.php`

The existing `LogUserLogin` listener already handled the `Login` event for activity logging. Extended it to also update the user record:

```diff
+ use Illuminate\Support\Facades\Request;

  public function handle(Login $event): void
  {
+     // تحديث بيانات آخر تسجيل دخول
+     $event->user->update([
+         'last_login_at' => now(),
+         'last_login_ip' => Request::ip(),
+     ]);
+
      // تسجيل في سجل النشاط
      ActivityLog::record(
          eventType:  'auth.login',
          ...
      );
  }
```

**Why `Illuminate\Support\Facades\Request` instead of `request()`:**  
The listener runs in an event context where `request()` helper is available, but using the facade is explicit and survives queue dispatch (though this listener is synchronous). Both are equivalent here; the facade is cleaner in a non-controller context.

**Why extend the existing listener instead of creating a new one:**  
The `Login` event already has exactly one listener (`LogUserLogin`) registered in `ActivityLogServiceProvider`. Adding a second listener for the same event would require another `Event::listen()` call, another class, and more cognitive overhead. The single-responsibility split is between "audit trail" (ActivityLog) and "user record update" — both are security-related and belong together in this listener.

**Trigger coverage:**

| Login path | Login event fired | Listener runs |
|---|---|---|
| Web login via `POST /login` | ✅ `Auth::attempt()` → fires `Login` | ✅ |
| Auto-login after registration (`Auth::login()`) | ✅ `Auth::login()` → fires `Login` | ✅ |
| Admin impersonate (`loginAs` action) | ✅ if using `Auth::loginUsingId()` | ✅ |

This means `last_login_at` will be set immediately after registration as well (since `RegisterUserAction` calls `Auth::login($user)` at the end). That is correct — the user's first login IS their registration moment.

---

## Existing Users Impact

| Column | Value for existing users after migration |
|---|---|
| `registration_ip` | `NULL` — no backfill possible, data was not captured |
| `registration_user_agent` | `NULL` — same |
| `last_login_at` | `NULL` — will be populated on next login |
| `last_login_ip` | `NULL` — will be populated on next login |

Existing users are not broken. `NULL` values are valid and expected:
- In the admin, `NULL last_login_at` = "never logged in since this feature was added" (useful spam signal going forward)
- `NULL registration_ip` = "registered before tracking was enabled"

No backfill is needed or recommended — fabricating historical data provides false confidence.

---

## Verification

### Grep confirmation

```
app/Models/User.php $fillable includes:
  - registration_ip          ✅
  - registration_user_agent  ✅
  - last_login_at            ✅
  - last_login_ip            ✅

app/Models/User.php $casts includes:
  - last_login_at => 'datetime'  ✅

RegisterUserAction User::create() includes:
  - registration_ip         => $request->ip()          ✅
  - registration_user_agent => $request->userAgent()   ✅

LogUserLogin::handle() calls $event->user->update():
  - last_login_at => now()          ✅
  - last_login_ip => Request::ip()  ✅
```

### Logic flow after migration

**New registration:**
```
POST /register → RegisterUserAction::execute()
  → User::create([..., registration_ip, registration_user_agent])
  → Auth::login($user)
      → Login event fired
      → LogUserLogin::handle()
          → $user->update([last_login_at => now(), last_login_ip => IP])
```

Result: user row has all 4 columns populated on first registration.

**Subsequent login:**
```
POST /login → Auth::attempt()
  → Login event fired
  → LogUserLogin::handle()
      → $user->update([last_login_at => now(), last_login_ip => current IP])
```

Result: `last_login_at` and `last_login_ip` always reflect the most recent session.

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Migration fails on existing data | 🟢 None | All columns are nullable — no default constraint issues |
| `update()` in Login listener breaks login flow | 🟢 None | `update()` failure would throw, but columns exist after migration; silent failure not possible |
| `registration_ip` captures proxy IP instead of real IP | 🟡 Low | Depends on `TrustProxies` config — if `APP_TRUSTED_PROXIES` is set correctly in production, real IP is captured. If not configured, load balancer IP is stored. Must set `TRUSTED_PROXIES` in production `.env`. |
| `last_login_at` set on registration (not just explicit login) | 🟢 Intended | `RegisterUserAction` calls `Auth::login()` which fires the event. First login = registration moment. Correct behaviour. |
| ActivityLog `metadata` loses guard info | 🟢 None | ActivityLog `record()` call unchanged — `['guard' => $event->guard]` still logged |
| Any test that mocks `Auth::login()` may skip the listener | 🟡 Low | Tests using `actingAs()` do not fire the Login event. Tests that call `Auth::login()` directly will fire it. Not a regression — consistent with existing test patterns. |

---

## Git Commit Message

```
security: add registration IP/UA and last-login tracking (SECURITY-01 / Phase A3+A4)

Migration:
- Add registration_ip string(45) nullable to users
- Add registration_user_agent text nullable to users
- Add last_login_at timestamp nullable to users
- Add last_login_ip string(45) nullable to users

RegisterUserAction:
- Capture registration_ip and registration_user_agent on User::create()

LogUserLogin listener:
- Update last_login_at and last_login_ip on every Login event
- ActivityLog entry unchanged

User model:
- Add 4 new fields to $fillable
- Add last_login_at => datetime cast

Existing users: all 4 columns NULL until next login / new registration.
No backfill. No auth behaviour change.

Refs: SECURITY-01-AUDIT-REPORT.md, SECURITY-01-PHASE-A3-A4-REPORT.md
```
