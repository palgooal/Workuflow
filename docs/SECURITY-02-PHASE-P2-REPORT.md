# SECURITY-02-PHASE-P2-REPORT
**Sprint:** SECURITY-02 — Phone Registration / Phase P2: Registration Form  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 3**

| File | Change |
|---|---|
| `resources/views/auth/register.blade.php` | Added phone field: country-code select + local number input + hidden E.164 field |
| `app/Http/Requests/Auth/RegisterRequest.php` | Added `phone` validation rules + Arabic error messages |
| `app/Modules/Auth/Actions/RegisterUserAction.php` | Added `phone` to `User::create()` |

No packages installed. No migrations. No new files.

---

## Registration UI

### Component structure

```html
<div x-data="{
    dialCode: '{{ old('phone_code', '+970') }}',
    localNum: '{{ old('phone_local', '') }}',
    get phone() {
        let local = this.localNum.replace(/[\s\-]/g, '').replace(/\D/g, '');
        if (local.startsWith('0')) local = local.slice(1);
        return local.length ? this.dialCode + local : '';
    }
}">
    <label>رقم الهاتف</label>
    <div class="flex gap-2">
        <select name="phone_code" x-model="dialCode">…</select>
        <input type="tel" name="phone_local" x-model="localNum" placeholder="599123456">
        <input type="hidden" name="phone" :value="phone">
    </div>
    @error('phone') … @enderror
</div>
```

### Three-field pattern

| Field | `name` | Purpose |
|---|---|---|
| Country select | `phone_code` | Drives `dialCode` in Alpine; preserved via `old('phone_code')` on reload |
| Local number input | `phone_local` | Drives `localNum` in Alpine; preserved via `old('phone_local')` on reload |
| Hidden combined | `phone` | E.164 value built by Alpine getter; this is what the server validates and saves |

Only `phone` is validated. `phone_code` and `phone_local` are UI-only fields submitted to enable `old()` repopulation on validation failure — they pass through Laravel's validator without rules.

### Supported country codes (18 total)

| Flag | Country | Code |
|---|---|---|
| 🇵🇸 | فلسطين (default) | +970 |
| 🇸🇦 | السعودية | +966 |
| 🇯🇴 | الأردن | +962 |
| 🇦🇪 | الإمارات | +971 |
| 🇰🇼 | الكويت | +965 |
| 🇶🇦 | قطر | +974 |
| 🇧🇭 | البحرين | +973 |
| 🇴🇲 | عُمان | +968 |
| 🇪🇬 | مصر | +20 |
| 🇱🇧 | لبنان | +961 |
| 🇸🇾 | سوريا | +963 |
| 🇮🇶 | العراق | +964 |
| 🇾🇪 | اليمن | +967 |
| 🇲🇦 | المغرب | +212 |
| 🇹🇳 | تونس | +216 |
| 🇩🇿 | الجزائر | +213 |
| 🇬🇧 | المملكة المتحدة | +44 |
| 🇺🇸 | الولايات المتحدة | +1 |

**Default selected:** +970 (Palestine). Matches the project's primary user base.

### Validation failure repopulation

When the form fails on any field (e.g., email already taken), all fields reload. The phone component:
- `dialCode` ← `old('phone_code', '+970')` — country code restored
- `localNum` ← `old('phone_local', '')` — local digits restored
- Alpine's `phone` getter re-computes E.164 into the hidden field immediately on mount

The user sees their phone input exactly as they typed it. No data loss on reload.

---

## Phone Normalization

### Alpine getter logic (client-side, before submission)

```javascript
get phone() {
    // Step 1: strip spaces and hyphens (common user formatting)
    let local = this.localNum.replace(/[\s\-]/g, '');
    // Step 2: strip any remaining non-digit characters
    local = local.replace(/\D/g, '');
    // Step 3: strip leading zero (Palestinian/Arab convention: 0599... → 599...)
    if (local.startsWith('0')) local = local.slice(1);
    // Step 4: concatenate if non-empty, else return '' to fail required validation
    return local.length ? this.dialCode + local : '';
}
```

### Transformation examples

| User types | dialCode | local after clean | Stored `phone` |
|---|---|---|---|
| `0599 123 456` | +970 | 599123456 | `+970599123456` |
| `0599-123-456` | +970 | 599123456 | `+970599123456` |
| `599123456` | +970 | 599123456 | `+970599123456` |
| `0501234567` | +966 | 501234567 | `+966501234567` |
| `501234567` | +966 | 501234567 | `+966501234567` |
| `01001234567` | +20 | 1001234567 | `+201001234567` |
| ` ` (spaces only) | +970 | (empty) | `''` → fails required |
| `abc` | +970 | (empty after \D strip) | `''` → fails required |

### Why strip the leading zero?

In Arab countries, mobile numbers are conventionally written with a leading `0` locally (e.g., `0599123456` in Palestine, `0501234567` in Saudi Arabia). This `0` is a trunk prefix — it is dropped in international dialing. E.164 does not include it. The Alpine getter removes it before prefixing the country code.

---

## Validation Rules

### Added to `RegisterRequest::rules()`

```php
'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{5,14}$/', 'unique:users,phone'],
```

| Rule | Purpose |
|---|---|
| `required` | Phone is mandatory at registration |
| `string` | Type guard |
| `max:30` | Column length is 30; prevents overflow |
| `regex:/^\+[1-9]\d{5,14}$/` | Enforces E.164: `+` then 1-9 then 5-14 digits (6–15 total digits per ITU-T) |
| `unique:users,phone` | No duplicate phone numbers across accounts |

### Regex breakdown

```
^         — start of string
\+        — literal +
[1-9]     — first digit must be 1–9 (country codes never start with 0)
\d{5,14}  — 5 to 14 more digits (total digits: 6–15, matching E.164 max)
$         — end of string
```

**What the regex rejects:**
- Empty string → fails `required` first
- No `+` prefix → fails regex (`\+` is literal)
- Starts with `+0` → fails regex (`[1-9]`)
- Contains letters, spaces, hyphens, parentheses → fails regex (`\d` only)
- Fewer than 7 total chars → fails regex (too short for any real number)
- More than 16 chars (15 digits + `+`) → practically covered by regex; `max:30` is a DB safety net

### Arabic error messages added to `messages()`

```php
'phone.required' => 'رقم الهاتف مطلوب.',
'phone.regex'    => 'صيغة رقم الهاتف غير صحيحة. مثال: +970599123456',
'phone.unique'   => 'رقم الهاتف هذا مسجّل بالفعل.',
'phone.max'      => 'رقم الهاتف طويل جداً.',
```

Error displays on `@error('phone')` — already wired in the Blade.

### Interaction with existing anti-spam checks

The `phone` validation runs in the standard `rules()` phase — before `withValidator()` timing check. This means:
- If phone is invalid, the form rejects immediately (timing check is skipped per existing logic)
- If phone is valid but timing fails, the form still rejects cleanly
- No interference with honeypot (`website`) or timing token (`_form_token`)

---

## RegisterUserAction Change

```php
$user = User::create([
    'name'                    => $request->name,
    'email'                   => $request->email,
    'phone'                   => $request->phone,  // ← added: E.164 from hidden field
    'password'                => $request->password,
    ...
]);
```

`$request->phone` receives the Alpine-built E.164 string from the hidden `<input name="phone">`. At this point it has already passed regex + unique validation.

---

## Stored Format

All phones stored in `users.phone` will conform to:

```
^\+[1-9]\d{5,14}$
```

Examples:
```
+970599123456    ← Palestine 9 digits
+966501234567    ← Saudi Arabia 9 digits
+962791234567    ← Jordan 9 digits
+201001234567    ← Egypt 10 digits
+447911123456    ← UK 10 digits
+12025551234     ← US 10 digits
```

These are ready for:
- `wa.me` WhatsApp links: `ltrim($user->phone, '+')`
- SMS gateways: pass as-is
- Future OTP verification: pass as-is

---

## Testing Checklist

### Registration flow — should PASS

- [ ] `dialCode=+970`, `localNum=0599123456` → stored: `+970599123456`
- [ ] `dialCode=+970`, `localNum=599123456` → stored: `+970599123456`
- [ ] `dialCode=+966`, `localNum=0501234567` → stored: `+966501234567`
- [ ] `dialCode=+20`, `localNum=01001234567` → stored: `+201001234567`
- [ ] `dialCode=+1`, `localNum=2025551234` → stored: `+12025551234`
- [ ] Phone with hyphens: `localNum=599-123-456` → stored: `+970599123456`
- [ ] Phone with spaces: `localNum=599 123 456` → stored: `+970599123456`

### Registration flow — should FAIL (validation error shown)

- [ ] Empty local number → "رقم الهاتف مطلوب."
- [ ] Local number = spaces only → "رقم الهاتف مطلوب." (Alpine builds `''`)
- [ ] Local number = letters only (`abc`) → "رقم الهاتف مطلوب." (no digits remain)
- [ ] Phone too short (e.g. `+97059`) → regex fails → "صيغة رقم الهاتف غير صحيحة."
- [ ] Duplicate phone (already registered) → "رقم الهاتف هذا مسجّل بالفعل."

### Validation failure repopulation

- [ ] Submit form with duplicate email → phone field reloads with original country code and local number intact
- [ ] Submit with empty password → phone still intact on reload

### Alpine.js rendering

- [ ] Default country code is +970 (Palestine) on fresh page load
- [ ] Switching country code updates `dialCode` immediately (x-model)
- [ ] Typing in local number updates hidden `phone` field in real-time (inspect DevTools → hidden input value)
- [ ] Hidden `phone` input correctly reflects `dialCode + cleaned localNum`

### Existing fields — no regression

- [ ] Name, email, password, currency, timezone still submit and validate correctly
- [ ] Honeypot (`website`) still silently rejects bots
- [ ] Timing token still rejects sub-2s submissions
- [ ] Disposable email blocker (Phase B2) still active

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Alpine.js not loaded on guest layout | 🟡 Medium | Alpine is in `app.js` — must verify `@vite` is included in `x-guest-layout`. If guest layout doesn't load Alpine, the hidden `phone` field stays empty and `required` validation catches it immediately |
| Bot submits form without Alpine-built `phone` | 🟢 None | `required` validation catches empty `phone`; regex catches malformed values |
| `old('phone')` repopulates hidden field but UI fields show wrong state | 🟢 None | Alpine reads from `old('phone_code')` and `old('phone_local')` independently, not from `old('phone')` |
| Unique index on `phone` rejects existing users on profile update | 🟢 None | Profile update (P3) is not changed in this phase; existing users have `phone = NULL` |
| `phone_code` and `phone_local` not in `$fillable` | 🟢 None | These fields are never mass-assigned; only `phone` is mass-assigned via RegisterUserAction |
| Regex `/^\+[1-9]\d{5,14}$/` rejects a valid edge-case number | 🟢 Low | Covers all E.164 numbers for the supported country list; short test numbers may fail (expected) |

### Alpine.js guest layout check

If the register form's `x-guest-layout` does not load `app.js`, Alpine will not run and the hidden `phone` field will always be empty. Verify the guest layout includes:
```html
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

## Git Commit Message

```
feat(registration): add phone field with country code selector (SECURITY-02 / Phase P2)

register.blade.php:
- Alpine.js component: dialCode (select) + localNum (tel input) + phone (hidden E.164)
- Default country: +970 (Palestine)
- 18 countries in dropdown (Arab region + UK + US)
- Normalization: strip spaces/hyphens/non-digits, strip leading zero
- old('phone_code') + old('phone_local') repopulate UI on validation failure

RegisterRequest:
- phone required|string|max:30|regex E.164|unique:users,phone
- Arabic messages for all phone error states
- No interference with existing honeypot/timing checks

RegisterUserAction:
- phone: $request->phone (E.164) added to User::create()

No packages. No migrations (P1 already added the column).
Existing users unaffected (phone = NULL, not collected by this form for them).

Next: P3 (settings), P4 (admin)

Refs: SECURITY-02-AUDIT-REPORT.md, SECURITY-02-PHASE-P1-REPORT.md,
      SECURITY-02-PHASE-P2-REPORT.md
```
