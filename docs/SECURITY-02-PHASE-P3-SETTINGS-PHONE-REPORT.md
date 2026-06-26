# SECURITY-02-PHASE-P3-SETTINGS-PHONE-REPORT
**Sprint:** SECURITY-02 — Phone Registration / Phase P3: Settings Page  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 2**

| File | Change |
|---|---|
| `app/Http/Requests/Settings/UpdateProfileRequest.php` | Added `phone` validation rule with `ignore(current user)` |
| `resources/views/settings/index.blade.php` | Added phone field (country-code select + local input + hidden E.164) in profile tab |

**No controller change.** `SettingsController::updateProfile()` already does:
```php
$user->update($request->validated());
```
Since `phone` is now in `validated()` and `User::$fillable` (added in P1), it saves automatically.

---

## Settings UI

### Location

Profile tab → between email field and "حفظ التغييرات" button.

### PHP pre-population (server-side, page load)

```php
@php
    $settingsPhoneCode  = '+970';   // default
    $settingsPhoneLocal = '';

    if ($user->phone) {
        // Codes sorted longest-first to avoid greedy matching (+44 before +4, +20 before +2, +1 last)
        $knownCodes = [
            '+970','+966','+962','+971','+965','+974','+973','+968',
            '+961','+963','+964','+967','+212','+216','+213','+44','+20','+1',
        ];
        foreach ($knownCodes as $code) {
            if (str_starts_with($user->phone, $code)) {
                $settingsPhoneCode  = $code;
                $settingsPhoneLocal = substr($user->phone, strlen($code));
                break;
            }
        }
    }

    // old() wins on validation failure reload
    $settingsPhoneCode  = old('phone_code',  $settingsPhoneCode);
    $settingsPhoneLocal = old('phone_local', $settingsPhoneLocal);
@endphp
```

**Three-state priority:**
1. `old('phone_code')` / `old('phone_local')` — present on validation failure reload → user sees exactly what they typed
2. Parsed from `$user->phone` — present on first load when user has a phone → correct dial code pre-selected, local digits populated
3. Defaults: `+970` / `''` — first load when user has no phone → blank field with Palestinian default

### Alpine component (same pattern as registration)

```html
<div x-data="{
    dialCode: '{{ $settingsPhoneCode }}',
    localNum: '{{ $settingsPhoneLocal }}',
    get phone() {
        let local = this.localNum.replace(/[\s\-]/g, '').replace(/\D/g, '');
        if (local.startsWith('0')) local = local.slice(1);
        return local.length ? this.dialCode + local : '';
    }
}">
    <select name="phone_code" x-model="dialCode">…18 options…</select>
    <input type="tel" name="phone_local" x-model="localNum" placeholder="599123456">
    <input type="hidden" name="phone" :value="phone">
</div>
```

Identical normalization: strips spaces/hyphens/non-digits, strips leading zero, concatenates dial code.

### "غير مُضاف بعد" hint

When `$user->phone` is NULL, the label shows a subtle amber badge:

```html
@if(! $user->phone)
    <span class="text-xs font-normal text-amber-600 mr-1">(غير مُضاف بعد)</span>
@endif
```

This nudges existing users to fill the field without blocking any flow. Login and dashboard are unaffected.

### Supported country codes

Same 18 codes as registration: Palestine (+970 default), Saudi Arabia, Jordan, UAE, Kuwait, Qatar, Bahrain, Oman, Egypt, Lebanon, Syria, Iraq, Yemen, Morocco, Tunisia, Algeria, UK, US.

---

## Validation

### Added to `UpdateProfileRequest::rules()`

```php
'phone' => [
    'required',
    'string',
    'max:30',
    'regex:/^\+[1-9]\d{5,14}$/',
    Rule::unique('users', 'phone')->ignore($this->user()->id),
],
```

| Rule | Purpose |
|---|---|
| `required` | Phone must be provided when saving profile |
| `string` / `max:30` | Type + DB column length guard |
| `regex:/^\+[1-9]\d{5,14}$/` | Enforces E.164 — identical to registration |
| `Rule::unique('users', 'phone')->ignore($this->user()->id)` | Allows user to save their **own** phone without a uniqueness conflict; rejects if another user has the same number |

### Arabic error messages added

```php
'phone.required' => 'رقم الهاتف مطلوب.',
'phone.regex'    => 'صيغة رقم الهاتف غير صحيحة. مثال: +970599123456',
'phone.unique'   => 'رقم الهاتف هذا مستخدم من قِبل حساب آخر.',
'phone.max'      => 'رقم الهاتف طويل جداً.',
```

### Key difference from registration

Registration uses `unique:users,phone` (string form, no ignore).  
Settings uses `Rule::unique('users', 'phone')->ignore($this->user()->id)` — the current user is excluded so saving the same phone they already have doesn't fail.

---

## Existing Users Behavior

| Scenario | What happens |
|---|---|
| User has `phone = NULL`, opens settings | Phone field shows empty, label shows "(غير مُضاف بعد)" in amber. They must fill it to save. |
| User has `phone = NULL`, tries to save without filling | Validation fails: "رقم الهاتف مطلوب." |
| User has `phone = +966501234567`, opens settings | Select shows 🇸🇦 +966, local input shows `501234567`. Pre-populated automatically via PHP parser. |
| User changes phone to one already taken by another account | Validation fails: "رقم الهاتف هذا مستخدم من قِبل حساب آخر." |
| User saves their existing phone unchanged | Passes (Rule::unique ignores their own ID). |
| Existing users trying to log in | ✅ Unaffected — login flow not touched |
| Existing users using dashboard | ✅ Unaffected — no middleware gates on phone |

**Existing users are not locked out.** They are gently asked to fill their phone when they visit settings. No forced redirect or blocking middleware has been added.

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| `$user->phone` starts with an unlisted country code | 🟡 Low | Parser falls through without match → defaults to `+970` / empty local. User sees wrong pre-selection but can correct it manually. |
| `+1` matches `+12025551234` before `+12` (if +12 were a code) | 🟢 None | No +12 code in list; `+1` is last in the parser loop (all 3-char codes checked first) |
| Saving profile without changing phone fails uniqueness | 🟢 None | `->ignore($this->user()->id)` handles this |
| `phone_code` and `phone_local` accidentally mass-assigned | 🟢 None | Neither is in `User::$fillable`; only `phone` is. `$request->validated()` only returns keys in `rules()`, and `phone_code`/`phone_local` have no rules. |
| Controller `$request->validated()` returning `phone` | ✅ Confirmed | `phone` is now in `UpdateProfileRequest::rules()` → included in `validated()` → saved via `$user->update($data)` |
| Alpine not available on `layouts/app.blade.php` | 🟢 None | The authenticated dashboard layout already uses Alpine (`x-data` on the root div); `app.js` is loaded. |
| `settings/index.blade.php` has outer `x-data` | 🟢 None | The outer `x-data="{ tab: ... }"` wraps tabs. The phone `x-data` is a nested child — Alpine supports nested components correctly. |

---

## Git Commit Message

```
feat(settings): add phone field to profile settings (SECURITY-02 / Phase P3)

UpdateProfileRequest:
- phone required|string|max:30|regex E.164|Rule::unique ignore current user
- Arabic messages for all phone error states
- Allows user to save existing phone without uniqueness conflict

settings/index.blade.php (profile tab):
- PHP pre-parser: splits $user->phone (E.164) into dial code + local number
- old() wins on validation failure reload
- Alpine component: identical pattern to registration form
- Amber "(غير مُضاف بعد)" hint when user has no phone
- Same 18 country codes, default +970

No controller change — SettingsController::updateProfile() already does
$user->update($request->validated()) which now includes phone.

Existing users not blocked — phone field is required only on settings save,
no middleware gate on login or dashboard.

Refs: SECURITY-02-AUDIT-REPORT.md, SECURITY-02-PHASE-P1-REPORT.md,
      SECURITY-02-PHASE-P2-REPORT.md, SECURITY-02-PHASE-P3-SETTINGS-PHONE-REPORT.md
```
