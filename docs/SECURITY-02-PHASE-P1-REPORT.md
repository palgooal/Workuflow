# SECURITY-02-PHASE-P1-REPORT
**Sprint:** SECURITY-02 — Phone Registration / Phase P1: Database + Model  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 2**

| File | Change |
|---|---|
| `database/migrations/2026_06_26_000005_add_phone_to_users_table.php` | New migration — adds `phone` column to `users` |
| `app/Models/User.php` | Added `'phone'` to `$fillable` |

No packages installed. No controllers. No views. No validation changes.

---

## Migration Details

**File:** `database/migrations/2026_06_26_000005_add_phone_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 30)->nullable()->unique()->after('email');
});
```

### Column spec

| Property | Value | Reason |
|---|---|---|
| Type | `string` | E.164 is a formatted string, not numeric |
| Length | 30 | E.164 max is 15 digits + `+` = 16 chars; 30 gives safe room for future extensions |
| Nullable | `true` | Existing users have no phone; new users won't be required until P2 |
| Unique | `true` | Prevents two accounts sharing the same phone number |
| Position | after `email` | Logical grouping with other contact fields |

### Unique index behaviour with NULL

In MySQL/MariaDB and PostgreSQL, `NULL` values do not violate a `UNIQUE` constraint — multiple rows with `NULL` are allowed. This is standard SQL behaviour. Existing users with `phone = NULL` will not conflict with each other.

### E.164 format reference (for future validation)

```
+966501234567   ← Saudi Arabia
+971501234567   ← UAE
+970599123456   ← Palestine
+201001234567   ← Egypt
+441234567890   ← UK
```

Format: `+` followed by country code followed by subscriber number. No spaces, dashes, or parentheses. Total: 7–20 characters including `+`.

### Rollback (down)

```php
$table->dropUnique(['phone']);
$table->dropColumn('phone');
```

`dropUnique` must run before `dropColumn` — MySQL will throw if you try to drop a column that has an active unique index.

---

## User Model Changes

**File:** `app/Models/User.php`

Added `'phone'` to `$fillable`:

```php
protected $fillable = [
    'name',
    'email',
    'phone',      // ← added (E.164 format: +966501234567)
    'password',
    'currency',
    ...
];
```

**No cast added** — `phone` is a plain string. No date/enum/boolean casting needed.

**No accessor/mutator added** — E.164 format is stored and retrieved as-is. A future formatter (strip `+` for wa.me links) can be added as a helper method when needed.

---

## Existing Users Impact

- All existing users will have `phone = NULL` after migration
- `NULL` passes the `UNIQUE` constraint in all supported databases
- No existing feature reads `$user->phone` — the field is new and currently unused in all controllers, views, and jobs
- No tinker command needed before or after migration

**Run to apply:**
```bash
php artisan migrate
```

---

## Verification

After running the migration, verify with tinker:

```php
// Check column exists and is nullable
Schema::getColumnListing('users');  // should include 'phone'

// Check a user's phone is null
User::first()->phone;  // should return null

// Check unique index exists
DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_phone_unique'");
```

Or via MySQL directly:
```sql
DESCRIBE users;
-- phone | varchar(30) | YES | UNI | NULL |
```

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Existing users get `phone = NULL` | 🟢 None | Column is nullable; no feature reads it yet |
| `phone` not in `$fillable` breaking mass assignment | 🟢 None | Added to `$fillable` in this phase |
| UNIQUE constraint on NULL (existing users) | 🟢 None | SQL standard: multiple NULLs are allowed in UNIQUE columns |
| `dropUnique` before `dropColumn` in `down()` | ✅ Handled | `dropUnique(['phone'])` runs first |
| Migration ordering conflict with 000004 | 🟢 None | Named 000005, runs after 000004 (security metadata migration) |
| RegisterUserAction creates users without phone | 🟢 None | Column is nullable; mass assignment won't fail if `phone` is absent |

---

## Git Commit Message

```
feat(users): add phone column to users table (SECURITY-02 / Phase P1)

Migration 2026_06_26_000005_add_phone_to_users_table:
- phone string(30) nullable unique after email
- E.164 format: +966501234567, +970599123456
- NULL is UNIQUE-safe: existing users unaffected
- down() drops unique index before column

User model:
- Added 'phone' to $fillable
- No cast needed (plain string)
- No accessor/mutator (E.164 stored as-is)

No views, no validation, no packages.
Next: P2 (registration form), P3 (settings), P4 (admin)

Refs: SECURITY-02-AUDIT-REPORT.md, SECURITY-02-PHASE-P1-REPORT.md
```
