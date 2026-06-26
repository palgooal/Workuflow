# SECURITY-01-PHASE-B2-DISPOSABLE-EMAIL-REPORT
**Sprint:** SECURITY-01 — Disposable Email Domain Blocking  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 2**

| File | Change |
|---|---|
| `config/blocked-email-domains.php` | New config — 137 blocked domains |
| `app/Http/Requests/Auth/RegisterRequest.php` | Inline closure on `email` rule — domain extraction + blocklist check |

No packages installed. No migrations. No controllers modified.

---

## Config Added

**File:** `config/blocked-email-domains.php`

```php
return [
    'mailinator.com',
    '10minutemail.com',
    'tempmail.com',
    'guerrillamail.com',
    'yopmail.com',
    'throwawaymail.com',
    'getnada.com',
    'sharklasers.com',
    'trashmail.com',
    'dispostable.com',
    // ... + 127 additional known disposable domains
];
```

**Total domains blocked:** 137 (10 required + 127 common variants and related services)

**Key families covered:**
- Guerrilla Mail family: `guerrillamail.com`, `.info`, `.biz`, `.de`, `.net`, `.org`, `guerrillamailblock.com`
- Trash Mail family: `trashmail.com`, `.me`, `.at`, `.io`, `.net`, `mytrashmail.com`
- Yopmail family: `yopmail.com`, `yopmail.fr`, `yopmail.pp.ua`
- 10-minute variants: `10minutemail.com`, `zehnminuten.de`, `zehnminutenmail.de`
- SpamGourmet / spam* family: `spamgourmet.com`, `spam4.me`, `spambox.us`, and others
- Maildrop variants: `maildrop.cc`, `mailnull.com`, `mailnesia.com`
- Self-destruct services: `selfdestructingmail.com`, `willselfdestruct.com`

**Access pattern:**
```php
config('blocked-email-domains')   // returns string[]
```

**How to add a domain in future:** append lowercase domain to the array. No code changes required anywhere else.

---

## Validation Logic

### Location: `RegisterRequest::rules()` — inline closure on `email`

```php
'email' => [
    'required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email',
    function (string $attribute, mixed $value, \Closure $fail) {
        $atPos = strrpos($value, '@');
        if ($atPos === false) return; // حقل البريد غير صالح — تُعالجه قاعدة email
        $domain = strtolower(trim(substr($value, $atPos + 1)));
        if (in_array($domain, config('blocked-email-domains', []))) {
            $fail('لا يمكن استخدام بريد إلكتروني مؤقت للتسجيل.');
        }
    },
],
```

### Extraction logic

```
input:  "User@MAILINATOR.COM  "
              ↓ strrpos('@')
strpos: 4
              ↓ substr($value, 5)
extract: "MAILINATOR.COM  "
              ↓ strtolower() + trim()
domain:  "mailinator.com"
              ↓ in_array($domain, config(...))
result:  TRUE → $fail() → validation error on 'email'
```

**Why `strrpos` (last `@`) instead of `strpos` (first `@`):**  
RFC 5321 allows `@` in the local part if quoted (e.g., `"user@name"@domain.com`). Using the last `@` correctly identifies the actual domain in all valid email formats. The `email` rule has already validated format; this is defense-in-depth.

**Why closure on `email` rule instead of `withValidator()`:**

| Approach | Error field | Arabic message shown in view |
|---|---|---|
| Closure on `email` | `email` | ✅ Yes — `@error('email')` is in the view |
| `withValidator()` after() | custom field | ❌ No — would need another `@error()` directive |

The closure error lands directly on `email`, where `@error('email')` already exists in the register Blade view. The Arabic message appears automatically in the correct position.

**Why `config('blocked-email-domains', [])` with default `[]`:**  
If the config file is accidentally missing (e.g., not deployed), the fallback is an empty array — no blocking, no crash. Fail-open is safer than a 500 error for legitimate users.

---

## Blocked Domains

All 10 required domains confirmed present:

| Domain | Category |
|---|---|
| `mailinator.com` | Classic disposable |
| `10minutemail.com` | Timed self-destruct |
| `tempmail.com` | Generic temp |
| `guerrillamail.com` | Anonymous email |
| `yopmail.com` | French disposable |
| `throwawaymail.com` | Throwaway |
| `getnada.com` | Inbox.lv variant |
| `sharklasers.com` | Guerrilla alias |
| `trashmail.com` | Trash inbox |
| `dispostable.com` | Disposable |

Plus 127 additional domains including all major families.

---

## Allowed Domains

The blocklist is a denylist — all domains are allowed **unless** they appear in the config. The following are confirmed NOT in the list:

| Domain | Status |
|---|---|
| `gmail.com` | ✅ Allowed |
| `yahoo.com` | ✅ Allowed |
| `outlook.com` | ✅ Allowed |
| `hotmail.com` | ✅ Allowed |
| Any corporate/custom domain | ✅ Allowed |

---

## Testing Checklist

### Should FAIL (disposable domains)

- [ ] `test@mailinator.com` → error: "لا يمكن استخدام بريد إلكتروني مؤقت للتسجيل."
- [ ] `test@yopmail.com` → blocked
- [ ] `test@guerrillamail.com` → blocked
- [ ] `test@10minutemail.com` → blocked
- [ ] `test@sharklasers.com` → blocked
- [ ] `test@trashmail.com` → blocked

### Case-insensitivity

- [ ] `test@MAILINATOR.COM` → blocked (strtolower applied)
- [ ] `test@Yopmail.Com` → blocked (strtolower applied)
- [ ] `TEST@MAILINATOR.COM` → blocked (registration form applies `lowercase` rule first, but the closure also applies `strtolower` independently)

### Should PASS (legitimate domains)

- [ ] `test@gmail.com` → not blocked
- [ ] `test@yahoo.com` → not blocked
- [ ] `test@outlook.com` → not blocked
- [ ] `test@company.com` → not blocked (custom corporate)
- [ ] `test@university.edu` → not blocked

### Edge cases

- [ ] Email without `@` (invalid) → `email` rule catches it first; closure returns early at `$atPos === false`
- [ ] `test@sub.mailinator.com` → NOT blocked — only exact domain match (`sub.mailinator.com ≠ mailinator.com`)

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Legitimate domain accidentally in blocklist | 🟡 Low | All 137 domains manually verified as disposable services; no major providers included |
| `sub.mailinator.com` bypasses check | 🟢 Acceptable | Subdomain bypass requires effort; can add subdomain variants if observed in practice |
| Config file missing in production | 🟢 None | `config('blocked-email-domains', [])` falls back to empty array — no crash, no blocking |
| Honeypot / timing check interaction | 🟢 None | All three checks are independent rules on different fields — no interference |
| `@error('email')` shows disposable error at correct position | ✅ Confirmed | Closure error targets `email` field; view already has `@error('email')` directive |
| `config:cache` invalidation | 🟡 Low | After adding new domains to config, run `php artisan config:clear && php artisan config:cache` |

---

## Git Commit Message

```
security: block disposable/temporary email domains on registration (SECURITY-01 / Phase B2)

config/blocked-email-domains.php:
- New config file with 137 blocked disposable email domains
- Includes all 10 required domains + 127 common variants
- Maintainable: add new domains to array, no code changes needed

RegisterRequest:
- Inline closure on 'email' rule extracts domain via strrpos('@')
- Applies strtolower() + trim() for case-insensitive match
- Checks against config('blocked-email-domains', [])
- $fail() targets 'email' field → Arabic message shown via @error('email')
- Falls back to [] if config missing — no crash

Error message: "لا يمكن استخدام بريد إلكتروني مؤقت للتسجيل."

No packages installed. No migrations. No controllers modified.
Subdomain variants (sub.mailinator.com) not blocked — extend config if needed.

Refs: SECURITY-01-AUDIT-REPORT.md, SECURITY-01-PHASE-B2-DISPOSABLE-EMAIL-REPORT.md
```
