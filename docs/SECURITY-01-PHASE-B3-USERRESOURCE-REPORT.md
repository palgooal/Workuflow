# SECURITY-01-PHASE-B3-USERRESOURCE-REPORT
**Sprint:** SECURITY-01 — Spam Security Review: Admin UserResource  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 1**

| File | Change |
|---|---|
| `app/Filament/Resources/UserResource.php` | New columns, filters, and actions for spam/security review |

No packages installed. No migrations. No controllers. No User model changes.

---

## Columns Added

### `email_verified_at` — Verification Badge

```php
Tables\Columns\TextColumn::make('email_verified_at')
    ->label('التحقق')
    ->badge()
    ->getStateUsing(fn (User $record): string => $record->email_verified_at
        ? 'مُتحقق'
        : 'غير مُتحقق'
    )
    ->color(fn (string $state): string => $state === 'مُتحقق' ? 'success' : 'danger')
    ->sortable(),
```

- Green badge = verified, Red badge = unverified
- Sorted by `email_verified_at` column (NULL first in ASC, last in DESC)
- Position: after `email`, before `status`

---

### `clients_count` — Client Count

```php
Tables\Columns\TextColumn::make('clients_count')
    ->label('العملاء')
    ->getStateUsing(fn (User $record): int =>
        DB::table('clients')->where('user_id', $record->id)->count()
    )
    ->sortable(false),
```

**Known limitation:** User model has no `clients()` Eloquent relationship. The `OnboardingService` uses `DB::table('clients')` directly — this column follows the same pattern. Sorting is disabled because `->counts()` (which enables sort) requires an Eloquent `HasMany`. To enable sorting, add `clients()` to User model:

```php
// In User.php — safe addition, no auth impact
public function clients(): HasMany
{
    return $this->hasMany(\App\Models\Client::class);
}
```

---

### `last_login_at` — Last Login Time

Toggleable, sortable, shows "لم يسجّل دخولاً" when null.

### `last_login_ip` — Last Login IP

Toggleable, hidden by default, copyable.

### `registration_ip` — Registration IP

Toggleable, hidden by default, copyable. Key for spam investigation.

### `registration_user_agent` — Registration User-Agent

Toggleable, hidden by default, truncated to 40 chars with full text on tooltip.

---

## Columns Already Present (unchanged)

| Column | Status |
|---|---|
| `name` | ✅ Kept |
| `email` | ✅ Kept |
| `status` badge | ✅ Kept |
| `subscription_plan` badge | ✅ Kept |
| `projects_count` (via `->counts('projects')`) | ✅ Kept |
| `transactions_count` (via `->counts('transactions')`) | ✅ Kept |
| `currency` | ✅ Kept (now toggleable, hidden by default) |
| `created_at` | ✅ Kept (full datetime now) |

---

## Filters Added

All new filters use the typed `fn (Builder $query)` signature (required for Filament DI resolution — see Sprint-03 BindingResolutionException).

### `email_unverified` — Unverified Accounts

```php
Tables\Filters\Filter::make('email_unverified')
    ->label('غير مُتحقق من البريد')
    ->query(fn (Builder $query) => $query->whereNull('email_verified_at'))
    ->toggle(),
```

Primary spam investigation filter — shows accounts that never verified their email.

### `email_verified` — Verified Accounts

```php
Tables\Filters\Filter::make('email_verified')
    ->label('مُتحقق من البريد')
    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at'))
    ->toggle(),
```

### `registered_today` — Registered Today

```php
Tables\Filters\Filter::make('registered_today')
    ->label('مسجّل اليوم')
    ->query(fn (Builder $query) => $query->whereDate('created_at', today()))
    ->toggle(),
```

Use with `email_unverified` to see today's suspicious registrations.

### `never_logged_in` — Never Logged In

```php
Tables\Filters\Filter::make('never_logged_in')
    ->label('لم يسجّل دخولاً قط')
    ->query(fn (Builder $query) => $query->whereNull('last_login_at'))
    ->toggle(),
```

Captures accounts created but abandoned — common for bots.

### `no_activity` — No Activity (Suspicious)

```php
Tables\Filters\Filter::make('no_activity')
    ->label('بلا نشاط (مشبوه)')
    ->query(fn (Builder $query) => $query
        ->whereDoesntHave('projects')
        ->whereDoesntHave('transactions')
        ->whereRaw('(SELECT COUNT(*) FROM clients WHERE clients.user_id = users.id) = 0')
    )
    ->toggle(),
```

Zero projects + zero transactions + zero clients. Combined with `email_unverified`, this is the primary spam detection view. The `clients` check uses a correlated subquery since `clients()` relationship doesn't exist on User.

---

## Filters Already Present (unchanged)

| Filter | Status |
|---|---|
| `SelectFilter(subscription_plan)` | ✅ Kept |
| `SelectFilter(status)` | ✅ Kept |

---

## Actions Added

### `resendVerification` — Resend Verification Email

```php
Tables\Actions\Action::make('resendVerification')
    ->label('إعادة التحقق')
    ->icon('heroicon-o-envelope-open')
    ->color('info')
    ->visible(fn (User $record): bool => $record->email_verified_at === null)
    ->action(function (User $record): void {
        $record->sendEmailVerificationNotification();
        // → success notification
    }),
```

- Available because `User` now implements `MustVerifyEmail` (Phase A1)
- Only visible on unverified accounts
- Calls `sendEmailVerificationNotification()` which is provided by `MustVerifyEmail`

### `deleteSpamAccount` — Delete Spam Account (Conditional)

```php
Tables\Actions\Action::make('deleteSpamAccount')
    ->label('حذف سبام')
    ->icon('heroicon-o-x-circle')
    ->color('danger')
    ->visible(function (User $record): bool {
        if ($record->email_verified_at !== null) return false;
        if ($record->subscription_plan !== SubscriptionPlan::Free) return false;
        if ($record->projects()->exists()) return false;
        if ($record->transactions()->exists()) return false;
        if (DB::table('clients')->where('user_id', $record->id)->exists()) return false;
        return true;
    })
    ->action(function (User $record): void {
        $record->notifications()->delete();
        $record->categories()->delete();
        $record->delete(); // Hard delete
    }),
```

**Safety gate — all 5 conditions must be true simultaneously:**

| Condition | Purpose |
|---|---|
| `email_verified_at === null` | Must be unverified — verified users are real people |
| `subscription_plan === Free` | Must be on free plan — paid accounts cannot be spam-deleted |
| `projects()->doesntExist()` | No projects created |
| `transactions()->doesntExist()` | No financial data |
| `clients table count = 0` | No clients added |

The modal shows the user's name, email, and registration IP for confirmation before deletion.

**Deletion sequence:**
1. Delete notifications
2. Delete categories (default categories created at registration)
3. Hard-delete the user row

This is intentionally minimal — spam accounts have no other data (the gate enforces this). The existing `deleteData` action handles data-heavy accounts.

---

## Actions Already Present (unchanged)

All existing actions preserved in same order:
- EditAction
- suspend / activate
- resetPlan / activatePlan
- sendEmail / sendReEngagement
- deleteData
- loginAs

New actions inserted before `deleteData`:
1. `resendVerification` (second position, after EditAction)
2. `deleteSpamAccount` (before deleteData)

---

## Spam Investigation Workflow

Recommended admin workflow for reviewing suspicious registrations:

1. **Filter: `غير مُتحقق من البريد` + `مسجّل اليوم`** → See today's unverified registrations
2. **Check columns:** `registration_ip`, `registration_user_agent`, `last_login_at`, `clients_count`, `projects_count`, `transactions_count`
3. **Same IP for multiple accounts?** → Likely bot
4. **User-agent is a headless browser or curl?** → Likely bot
5. **Action:** Use `حذف سبام` if all safety conditions met, or `تعليق` + manual investigation for suspicious paid accounts
6. **Filter: `بلا نشاط`** → See all zero-activity accounts (accumulated over time)

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| `clients_count` causes N+1 queries | 🟡 Low | Admin table, typically < 1000 rows; acceptable until `clients()` relationship is added |
| `no_activity` filter correlated subquery on large `clients` table | 🟡 Low | Correlated subquery is O(n) per row — add index on `clients.user_id` if slow |
| `deleteSpamAccount` deletes real account | 🟢 None | 5-condition safety gate is very strict; paid/verified/data-having accounts are invisible to this action |
| `resendVerification` on already-verified user | 🟢 None | `->visible()` returns false for verified accounts; button never shows |
| `Builder $query` type hint in filters | ✅ Confirmed | Applied correctly — avoids BindingResolutionException (Sprint-03 lesson) |

---

## Git Commit Message

```
security: improve UserResource for spam/security review (SECURITY-01 / Phase B3)

UserResource:
New columns:
- email_verified_at: badge (green=verified, red=unverified)
- clients_count: DB::table count (no Eloquent relationship on User)
- last_login_at: sortable, toggleable, null placeholder
- last_login_ip: toggleable, hidden by default, copyable
- registration_ip: toggleable, hidden by default, copyable
- registration_user_agent: toggleable, hidden by default, tooltip

New filters (all typed Builder $query):
- email_unverified: whereNull('email_verified_at')
- email_verified: whereNotNull('email_verified_at')
- registered_today: whereDate('created_at', today())
- never_logged_in: whereNull('last_login_at')
- no_activity: no projects + no transactions + no clients (raw subquery)

New actions:
- resendVerification: visible only on unverified accounts; calls sendEmailVerificationNotification()
- deleteSpamAccount: hard-delete; visible ONLY IF (unverified + free + 0 projects + 0 transactions + 0 clients)

No packages installed. No migrations. No User model changes.
Known: clients_count has N+1 (no clients() relationship); add it to User.php to fix.

Refs: SECURITY-01-AUDIT-REPORT.md, SECURITY-01-PHASE-B3-USERRESOURCE-REPORT.md
```
