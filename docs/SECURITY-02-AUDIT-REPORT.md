# SECURITY-02-AUDIT-REPORT
**Sprint:** SECURITY-02 — Phone Number Registration  
**Date:** 2026-06-26  
**Status:** 🔍 Audit Only — No Code Modified

---

## Executive Summary

There is **no phone field anywhere on the `users` table or model**. The concept of "phone" exists only in the `clients` table (CRM module). Every touch point — registration, settings, profile, billing, admin — will need updating. No phone-related package is installed. Alpine.js is available and can drive a country-code selector without additional packages.

---

## 1. Existing Fields — `users` Table

Current schema (reconstructed from all migrations):

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | bigint | NO | PK |
| `name` | string(255) | NO | |
| `email` | string(255) | NO | unique |
| `email_verified_at` | timestamp | YES | MustVerifyEmail (A1) |
| `password` | string | NO | hashed |
| `remember_token` | string(100) | YES | |
| `currency` | string(3) | NO | default SAR |
| `timezone` | string | NO | default Asia/Riyadh |
| `subscription_plan` | enum | NO | free/pro/business |
| `status` | enum | NO | active/suspended |
| `onboarding_dismissed_at` | timestamp | YES | |
| `payment_customer_id` | string | YES | Togo receiver ID |
| `target_margin_pct` | decimal | YES | profit margin % |
| `registration_ip` | string(45) | YES | SECURITY-01 |
| `registration_user_agent` | text | YES | SECURITY-01 |
| `last_login_at` | timestamp | YES | SECURITY-01 |
| `last_login_ip` | string(45) | YES | SECURITY-01 |
| `created_at` / `updated_at` | timestamps | YES | |

**`phone` column: DOES NOT EXIST.**

---

## 2. Registration Flow

### Entry points

```
GET  /register  → RegisteredUserController::create()
POST /register  → RegisteredUserController::store() → RegisterUserAction::execute()
```

### Files in play

| File | Role | Has phone? |
|---|---|---|
| `RegisteredUserController` | Builds view data (currencies, timezones, formToken) | ❌ |
| `RegisterRequest` | Validates registration input | ❌ |
| `RegisterUserAction` | Creates User row + fires Registered event + sends welcome email | ❌ |
| `resources/views/auth/register.blade.php` | Registration form | ❌ |

### What registration currently collects

- name, email, password, currency, timezone
- IP + user agent (SECURITY-01, server-side only)
- Honeypot + timing token (SECURITY-01, not stored)

**Phone is not collected, validated, or stored at registration.**

---

## 3. Profile / Settings Flow

There are **two overlapping profile pages**:

### 3a. SettingsController → `/settings` (primary — Blade)

**Route:** `GET /settings` → `SettingsController::index()`  
**Update route:** `PATCH /settings/profile` → `SettingsController::updateProfile()`  
**Request class:** `UpdateProfileRequest`

Current `UpdateProfileRequest::rules()`:
```php
'name'  => ['required', 'string', 'max:100'],
'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(...)],
```

The settings blade (`resources/views/settings/index.blade.php`) shows:
- الاسم الكامل (name)
- البريد الإلكتروني (email)
- العملة، المنطقة الزمنية، هامش الربح (preferences)
- إعدادات الفاتورة (invoice section — فري تكست لمعلومات التواصل)

**No phone field exists in the settings page.**

Note: The invoice "معلومات التواصل" textarea (`invoice_company_info`) is a freetext stored in `settings` table (not `users`) — users manually type their phone there for invoice display purposes. This is a workaround for the absence of a real phone field.

### 3b. ProfileController → `/profile` (Breeze default — secondary)

This is the Laravel Breeze default profile route. It also has no phone field. It appears to be a legacy Breeze leftover alongside the custom SettingsController.

### 3c. Client Portal Profile → `/portal/profile` (client-facing)

This is the **client's** profile view (CRM portal), not the user/merchant profile. It shows the `client->phone`, not any user phone.

---

## 4. Admin (Filament UserResource) Flow

`app/Filament/Resources/UserResource.php` — just updated in SECURITY-01 Phase B3.

### Form (create/edit user):
Current fields: name, email, password, subscription_plan, status, currency, timezone, roles.  
**No phone field in the Filament form.**

### Table columns:
name, email, email_verified_at, status, subscription_plan, projects_count, clients_count, transactions_count, last_login_at, last_login_ip, registration_ip, registration_user_agent, currency, created_at.  
**No phone column.**

### Searchable columns:
`name` and `email` are `->searchable()`.  
**Phone cannot be searched because it doesn't exist.**

---

## 5. Billing / Payment Flow

### TogoPaymentService

When creating a checkout (`createCheckoutUrl()`), the payload sent to Togo API includes:
```php
'receiver_email' => $user->email,
```

**No phone is sent to Togo.** The Togo API (togo.ps) accepts optional phone parameters for SMS notifications. This is a future enhancement opportunity.

### WhatsApp Manual Billing

The billing page has a manual upgrade CTA that links to owner's WhatsApp (`config('billing.owner_whatsapp')` — the **owner's** number, not the user's). This is used when payment gateway is disabled. The user's phone is not involved here.

### Invoice WhatsApp Sending

`resources/views/invoices/show.blade.php` generates a `wa.me` link using **`$invoice->client->phone`** (the client's phone, not the user/merchant's phone). The freelancer (user) sends invoices via WhatsApp to their clients.

**User phone is not currently used in any payment or billing flow.** This is a gap — for subscription renewal reminders, WhatsApp contact, and OTP it would be used.

---

## 6. Packages Audit

### composer.json (PHP)

| Package | Version | Phone-related? |
|---|---|---|
| `filament/filament` | 3.3 | Has built-in PhoneInput (Filament Extra) — NOT installed |
| `laravel/framework` | ^12.0 | Phone validation rule available via `phone:AUTO,INTERNATIONAL` |
| `mpdf/mpdf` | ^8.3 | PDF generation — no phone relevance |
| `maatwebsite/excel` | ^3.1 | Excel export — no phone relevance |
| `spatie/laravel-permission` | ^6.25 | Roles — no phone relevance |

**No phone-specific package is installed.** Packages to consider:

| Package | Purpose | Verdict |
|---|---|---|
| `propaganistas/laravel-phone` | Phone validation + E.164 formatting | Recommended — lightweight, no UI |
| `filament-phone-field` (3rd party) | Filament form component with intl-tel-input | Optional |
| `intl-tel-input` (npm) | Country code picker UI | Can avoid with Alpine.js |

### package.json (JS)

| Package | Version | Notes |
|---|---|---|
| `alpinejs` | ^3.15.12 | ✅ Available — can power a country-code dropdown |
| `tailwindcss` | ^3.1.0 | ✅ Available for styling |
| `axios` | ^1.11.0 | ✅ Available for future OTP API calls |

**Alpine.js is available** and sufficient to build an interactive country code selector without installing `intl-tel-input`.

---

## 7. Phone in Other Models (CRM comparison)

The `clients` table (`2026_05_15_100001_create_clients_table.php`) has:
```php
$table->string('phone')->nullable();
```

And `Client::$fillable` includes `'phone'`. The CRM forms (`crm/clients/create.blade.php`) use:
```html
<input type="text" name="phone" placeholder="+970501234567">
```

This is a plain text input — no country code picker, no validation beyond required/nullable. The pattern is simple and can be replicated for users with minor enhancements.

---

## 8. Pages That Will Need Updating

| Page / File | Change Needed | Priority |
|---|---|---|
| `database/migrations/...` | New migration: `phone` + `phone_country_code` on `users` | 🔴 Required |
| `app/Models/User.php` | Add `phone`, `phone_country_code` to `$fillable` + `$casts` if needed | 🔴 Required |
| `app/Http/Requests/Auth/RegisterRequest.php` | Add validation rules for `phone_country_code` + `phone` | 🔴 Required |
| `app/Modules/Auth/Actions/RegisterUserAction.php` | Capture `phone` + `phone_country_code` on user creation | 🔴 Required |
| `resources/views/auth/register.blade.php` | Add phone field with country code selector | 🔴 Required |
| `app/Http/Requests/Settings/UpdateProfileRequest.php` | Add phone validation rules | 🔴 Required |
| `resources/views/settings/index.blade.php` | Add phone field to profile section | 🔴 Required |
| `app/Filament/Resources/UserResource.php` | Add phone column + searchable + form field | 🔴 Required |
| `app/Http/Requests/ProfileUpdateRequest.php` | Add phone (Breeze legacy profile) | 🟡 Optional |
| `resources/views/profile/partials/update-profile-information-form.blade.php` | Add phone (Breeze legacy) | 🟡 Optional |
| `app/Modules/Billing/Services/TogoPaymentService.php` | Pass phone to Togo API for SMS | 🟢 Future |
| Notification system | WhatsApp reminders using user phone | 🟢 Future |

---

## 9. Recommended Schema

### Option A — Single column, E.164 format (Recommended)

```php
$table->string('phone', 20)->nullable()->after('email');
```

Store phone in E.164 international format: `+9725012345678`. The country code is embedded. No separate column needed.

**Pros:** Simple, one column, works natively with wa.me links, no JOIN needed, can use `propaganistas/laravel-phone` to parse/validate the format.

**Cons:** Requires the user to supply the country code (+XXX prefix) at entry time.

### Option B — Two columns: country code + local number (Alternative)

```php
$table->string('phone_country_code', 5)->nullable()->after('email'); // e.g. "+966"
$table->string('phone_number', 15)->nullable()->after('phone_country_code'); // e.g. "501234567"
```

**Pros:** Easier to build a country selector UI; can store and display separately.

**Cons:** Two columns to maintain; must concat for wa.me links; more migration complexity; adds a computed `phone` accessor to User model.

### Verdict: Option A — single `phone` column, E.164

Consistent with how the `clients` table works today (`$table->string('phone')->nullable()`). The CRM precedent already exists. No package required for storage — only for validation optionally.

```php
// Final schema
$table->string('phone', 30)->nullable()->after('email');
```

Use `max:30` to accommodate E.164 + extension. Store with `+` prefix always.

---

## 10. Recommended UI

### Registration form

```html
<div>
    <label>رقم الهاتف <span class="text-gray-400 text-xs">(اختياري)</span></label>
    <div class="flex gap-2">
        <!-- Country code select (Alpine-powered) -->
        <select name="phone_country_code" x-model="dialCode"
                class="w-28 ...">
            <option value="+966">🇸🇦 +966</option>
            <option value="+971">🇦🇪 +971</option>
            <option value="+970">🇵🇸 +970</option>
            <option value="+962">🇯🇴 +962</option>
            ...
        </select>
        <!-- Local number input -->
        <input type="tel" name="phone_local" x-model="localNum"
               placeholder="501234567" class="flex-1 ...">
        <!-- Hidden combined E.164 field -->
        <input type="hidden" name="phone" :value="dialCode + localNum">
    </div>
    @error('phone')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
```

The Alpine component concatenates `dialCode + localNum` into the hidden `phone` field (submitted as E.164). No package needed.

**Country codes to include (Arab region focus, matching existing currency/timezone lists):**

| Flag | Country | Dial Code |
|---|---|---|
| 🇸🇦 | السعودية | +966 |
| 🇦🇪 | الإمارات | +971 |
| 🇰🇼 | الكويت | +965 |
| 🇶🇦 | قطر | +974 |
| 🇧🇭 | البحرين | +973 |
| 🇴🇲 | عُمان | +968 |
| 🇯🇴 | الأردن | +962 |
| 🇪🇬 | مصر | +20 |
| 🇵🇸 | فلسطين | +970 |
| 🇱🇧 | لبنان | +961 |
| 🇸🇾 | سوريا | +963 |
| 🇮🇶 | العراق | +964 |
| 🇾🇪 | اليمن | +967 |
| 🇱🇾 | ليبيا | +218 |
| 🇹🇳 | تونس | +216 |
| 🇩🇿 | الجزائر | +213 |
| 🇲🇦 | المغرب | +212 |
| 🇸🇩 | السودان | +249 |
| 🇬🇧 | المملكة المتحدة | +44 |
| 🇺🇸 | الولايات المتحدة | +1 |

Default pre-selected: **+966** (Saudi Arabia, matching default currency SAR).

### Settings page

Same UI pattern as registration — country code select + local number input, with Alpine.js managing the hidden combined field.

### Filament UserResource

```php
Forms\Components\TextInput::make('phone')
    ->label('رقم الهاتف')
    ->tel()
    ->nullable()
    ->maxLength(30),
```

Filament's `->tel()` renders a `type="tel"` input. No country picker needed in admin (admin can type E.164 directly).

---

## 11. Validation Rules

### In RegisterRequest + UpdateProfileRequest + UpdateProfileRequest(Breeze)

```php
'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+[1-9]\d{6,19}$/'],
```

The regex enforces:
- Starts with `+` and a non-zero digit
- Total length 7–20 digits after `+` (E.164 standard: max 15 digits, but we allow 19 for safety)

**Why nullable:** Phone is optional at registration. Making it required is a UX decision — see Risks section.

**Optional enhancement:** Install `propaganistas/laravel-phone` for proper ITU-T E.164 validation:
```php
'phone' => ['nullable', 'phone:AUTO,INTERNATIONAL'],
```

This validates against a real phone number database. However, it adds a dependency. The regex approach is sufficient for now.

---

## 12. Existing User Impact

The `phone` column will be `nullable`. Migration runs without touching existing rows — all existing users get `phone = NULL`. No pre-migration tinker command needed (unlike email_verified_at in SECURITY-01).

Users with NULL phone:
- Can still use the app normally
- Will see a phone field in settings that they can fill later
- Will not be prompted at login (unless OTP is added in Phase C)

---

## 13. Risks

| Risk | Severity | Mitigation |
|---|---|---|
| **Making phone required at registration** reduces conversion | 🔴 High | Keep `nullable` at registration; encourage in settings/onboarding |
| **E.164 formatting inconsistency** (user types `00966` instead of `+966`) | 🟡 Medium | Alpine.js controls the `+prefix` by construction; local number field strips leading zeros |
| **Duplicate phones** — two users register with same number | 🟡 Medium | Consider `->unique()` constraint or leave nullable unique. For now: no unique constraint (not a login identifier) |
| **WhatsApp link generation** for wrong format | 🟡 Medium | Strip `+` for wa.me: `ltrim($user->phone, '+')` |
| **Old users have NULL phone** — breaks WhatsApp notification jobs | 🟡 Medium | Always null-check before sending WhatsApp; `if ($user->phone)` guard |
| **Existing `invoice_company_info` freetext** may contain phone | 🟢 Low | No migration needed — users can keep using it or move data manually |
| **settings.php blade** has two profile forms** (SettingsController + Breeze ProfileController) | 🟡 Medium | Update both to avoid inconsistency; or retire the Breeze one |
| **Togo API** does not receive phone today — no regression | 🟢 None | Phone is added to user record; Togo integration can be updated separately |

---

## 14. Implementation Plan

### Phase P1 — Database + Model (foundation)

1. Create migration: `add_phone_to_users_table`
   - `$table->string('phone', 30)->nullable()->after('email');`
2. Update `User::$fillable`: add `'phone'`
3. No cast needed (string is fine)

**Files:** 1 new migration, 1 line in User.php

---

### Phase P2 — Registration Flow

1. Update `RegisterRequest::rules()`: add `'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+[1-9]\d{6,19}$/']`
2. Update `RegisterUserAction::execute()`: capture `'phone' => $request->phone`
3. Update `resources/views/auth/register.blade.php`: add Alpine-powered country-code selector + local number input + hidden `phone` field

**Files:** RegisterRequest, RegisterUserAction, register.blade.php

---

### Phase P3 — Settings Flow

1. Update `UpdateProfileRequest::rules()` (SettingsController): add phone rule
2. Update `resources/views/settings/index.blade.php`: add phone field (same Alpine pattern as register)
3. Update `ProfileUpdateRequest` (Breeze): add phone rule — OR retire the Breeze `/profile` route

**Files:** UpdateProfileRequest, settings/index.blade.php, optional: ProfileUpdateRequest

---

### Phase P4 — Admin (UserResource)

1. Add phone `TextInput` to the Filament form
2. Add `phone` TextColumn to table (searchable, copyable, toggleable)

**Files:** UserResource.php only

---

### Phase P5 (Future) — WhatsApp Integration

1. Subscription renewal reminders via WhatsApp using `$user->phone`
2. Pass phone to Togo API for SMS payment confirmations
3. OTP verification on phone number

**Dependencies:** Requires Phase P1–P4 to be complete first.

---

## 15. Recommended Phasing

| Phase | Scope | Files Changed | Estimate |
|---|---|---|---|
| P1 | DB + Model | 2 | Small |
| P2 | Registration | 3 | Medium |
| P3 | Settings | 2–3 | Small |
| P4 | Filament Admin | 1 | Small |
| P5 | WhatsApp / OTP | TBD | Future sprint |

Phases P1 → P4 can be approved and implemented in sequence or all at once (P1+P2+P3+P4 in a single sprint).

---

## 16. Files Summary

### Must modify (P1–P4)

```
database/migrations/2026_06_26_XXXXX_add_phone_to_users_table.php  ← NEW
app/Models/User.php
app/Http/Requests/Auth/RegisterRequest.php
app/Modules/Auth/Actions/RegisterUserAction.php
resources/views/auth/register.blade.php
app/Http/Requests/Settings/UpdateProfileRequest.php
resources/views/settings/index.blade.php
app/Filament/Resources/UserResource.php
```

### Optional (Breeze legacy)

```
app/Http/Requests/ProfileUpdateRequest.php
resources/views/profile/partials/update-profile-information-form.blade.php
```

### No change needed

```
app/Modules/Billing/Services/TogoPaymentService.php   ← future only
config/billing.php                                    ← unchanged
resources/views/invoices/show.blade.php               ← uses client.phone, not user.phone
```

---

## 17. Answer to Audit Questions

| Question | Answer |
|---|---|
| Is there already a phone field on users? | ❌ No — not in table, model, registration, settings, or admin |
| Is phone used anywhere on users? | ❌ No — phone exists only on `clients` table (CRM) |
| Are there packages for international phone inputs? | ❌ Not installed — `intl-tel-input` and `propaganistas/laravel-phone` are absent |
| Is Alpine.js available? | ✅ Yes — `alpinejs ^3.15.12` in `package.json`, already loaded in `app.js` and used in layouts |
| Is Livewire available? | ❌ No — project uses Blade + Alpine only |
| Which pages need updating? | Migration, User model, RegisterRequest, RegisterUserAction, register.blade.php, UpdateProfileRequest, settings/index.blade.php, UserResource (8 files minimum) |
| Is the phone field nullable or required? | Recommended: nullable (optional at registration) |
| Package recommendation? | No package needed — Alpine.js handles UI, regex handles validation |
