# SECURITY-01-PHASE-B1-HONEYPOT-REPORT
**Sprint:** SECURITY-01 — Honeypot Protection on Registration  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 3**

| File | Change |
|---|---|
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Generate `$formToken` in `create()`, pass to view |
| `resources/views/auth/register.blade.php` | Add honeypot div + hidden timing token field |
| `app/Http/Requests/Auth/RegisterRequest.php` | Add `prohibited` rule, `_form_token` rule, `withValidator` timing check |

No packages installed. No new files. No migrations.

---

## Honeypot Fields Added

### 1. Honeypot input (`name="website"`)

```html
<div aria-hidden="true"
     style="position:absolute;left:-9999px;top:-9999px;width:0;height:0;overflow:hidden;"
     tabindex="-1">
    <label for="hp_website">Website (leave blank)</label>
    <input type="text" id="hp_website" name="website"
           tabindex="-1" autocomplete="off" value="">
</div>
```

**Why this approach:**
- `position:absolute` + off-screen coordinates → invisible without `display:none` (some bots detect and skip `display:none` fields)
- `width:0; height:0; overflow:hidden` → zero footprint in layout
- `aria-hidden="true"` on wrapper → screen readers skip the entire block
- `tabindex="-1"` on both the div and input → keyboard navigation never reaches it
- `autocomplete="off"` → browser autofill skips it
- `id="hp_website"` (not "website") → browser autofill less likely to match the label
- `value=""` → pre-set to empty; autofill must actively overwrite it

### 2. Timing token (`name="_form_token"`)

```html
<input type="hidden" name="_form_token" value="{{ $formToken }}">
```

Generated in `RegisteredUserController::create()`:

```php
$formToken = encrypt(now()->timestamp);
```

Laravel's `encrypt()` uses `APP_KEY` + AES-256-CBC with a MAC. The timestamp is sealed — any tampering causes `decrypt()` to throw `DecryptException`.

---

## Backend Validation

### `RegisterRequest::rules()` — new entries

```php
'website'      => ['prohibited'],         // Honeypot
'_form_token'  => ['required', 'string'], // Timing gate
```

**`prohibited` rule behavior:**  
Laravel's built-in rule passes if the field is **absent** or **empty string/null**. Fails if the field is present with any non-empty value. No custom message needed — the error lands on `'website'` which has no `@error('website')` in the view, so it is never displayed to users.

### `RegisterRequest::withValidator()` — timing check

```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        if ($validator->errors()->isNotEmpty()) {
            return; // Already failing — skip timing check
        }
        $this->validateFormTiming($validator);
    });
}

private function validateFormTiming(Validator $validator): void
{
    $token = $this->input('_form_token', '');

    try {
        $renderedAt = (int) decrypt($token);
    } catch (\Throwable) {
        $validator->errors()->add('_form_token', 'invalid');
        return;
    }

    if ((now()->timestamp - $renderedAt) < 2) {
        $validator->errors()->add('_form_token', 'too_fast');
    }
}
```

**Validation flow:**

```
POST /register
  → rules() phase:
      website    → prohibited → FAIL if non-empty (bot caught here)
      _form_token → required  → FAIL if missing (direct POST caught here)
      name/email/password/... → normal validation
  → withValidator() after() phase:
      if any error exists → return early (already rejecting)
      else → decrypt _form_token → check timestamp:
          decrypt fails → add error (tampered token)
          elapsed < 2s  → add error (too fast)
```

**Why `errors()->isNotEmpty()` short-circuit:**  
If basic validation already failed (wrong email format, password mismatch, honeypot filled), the timing check is irrelevant — the form is already rejected. Skipping it avoids unnecessary `decrypt()` computation and keeps the logic tight.

**Errors are not displayed:**  
Neither `'website'` nor `'_form_token'` have `@error()` directives in the register Blade view. Validation errors for these fields are present in the session `$errors` bag but never rendered. To users, the form simply reloads with no message for these specific failures.

---

## Bot Behavior

### Type A — Simple form-fill bot (fills all fields)

```
POST /register
  website = "https://botsite.com"   ← bot fills visible-looking field
  _form_token = "..."                ← bot may or may not include

rules(): website → prohibited → FAIL
→ Form rejected. No user created.
```

### Type B — Fast headless browser (does not fill hidden fields)

```
POST /register
  website = ""                       ← bot skips, default empty
  _form_token = "<valid token>"      ← bot copied from GET response
  Submitted 0.1s after GET request

rules(): prohibited passes, token present
withValidator(): decrypt OK, elapsed = 0.1s < 2s → FAIL
→ Form rejected. No user created.
```

### Type C — Direct API bot (no HTML parsing)

```
POST /register
  (no website field, no _form_token)

rules(): _form_token required → FAIL
→ Form rejected. No user created.
```

### Type D — Sophisticated bot (reuses old token)

```
POST /register
  _form_token = encrypt(timestamp from 10 mins ago)
  website = ""

withValidator(): decrypt OK, elapsed = 600s > 2s → PASS ← token passes
```

Type D bypasses timing if it has a valid token. However, it must:
1. First make a GET request to `/register` to obtain a real token
2. Parse the token from the HTML response
3. Wait ≥ 2 seconds OR reuse an old token (always > 2s)

This type is indistinguishable from a slow legitimate browser and will pass. This is expected — sophisticated bots require CAPTCHA (Phase C).

---

## Legitimate User Behavior

### Normal registration

```
User navigates to /register
  → GET /register: server generates formToken = encrypt(now()->timestamp)
  → Page renders (~200–500ms load + render)
  → User reads form, fills fields (~30s to several minutes)
  → User clicks submit
  → POST /register:
      website = ""            ✅ prohibited passes
      _form_token = token     ✅ required passes
      elapsed ≥ 30s           ✅ timing check passes (>> 2s)
      → registration succeeds
```

### Password manager / autofill user

```
User navigates to /register
  → Page renders (~200ms)
  → Password manager autofills email + password instantly
  → User still must: read, verify, click submit (~3–5 seconds minimum)
  → Elapsed time ~3–5s > 2s  ✅ passes
```

**Minimum realistic elapsed time:**
- Network round-trip (GET): ~100ms
- Browser render: ~100ms
- DOM load + JavaScript: ~50ms
- Human click (fastest): ~1–2 seconds

Total minimum ≈ 1.5–2.5 seconds for the most extreme fast-click scenario. The 2-second threshold is at the absolute edge of human capability — no false positives expected in practice.

### Form reload (validation failed, user corrects)

```
First attempt fails (e.g., password mismatch)
→ Form reloads
→ Controller re-runs create() → new $formToken generated
→ View renders new token in hidden field (old token replaced)
→ User corrects fields, submits again
→ New token → timing check starts fresh from the reload
```

Old tokens from previous renders are discarded. Each form render generates a fresh token. No stale-token issues.

---

## Accessibility Notes

| Technique | Reason |
|---|---|
| `aria-hidden="true"` on wrapper div | Screen readers (NVDA, VoiceOver, JAWS) skip the entire block — no confusion for visually impaired users |
| `tabindex="-1"` on input | Keyboard-only users never tab into the field |
| `tabindex="-1"` on wrapper div | Belt-and-suspenders for the wrapper itself |
| `autocomplete="off"` on input | Prevents browser from offering autofill suggestions for this invisible field |
| No `required` attribute on honeypot input | Browsers would show HTML5 validation popup for a field the user can't see |
| Meaningful label text "Website (leave blank)" | If somehow encountered, the label tells users to leave it blank |

The hidden token input (`_form_token`) is `type="hidden"` — completely transparent to assistive technologies. No accessibility impact.

---

## Testing Checklist

### Manual tests

- [ ] **Normal registration** — fill all visible fields normally → registration succeeds
- [ ] **Password manager autofill** — let browser/1Password autofill email+password, submit immediately → registration succeeds (elapsed > 2s)
- [ ] **Honeypot filled via DevTools** — open browser DevTools, find `website` input, set value to "test", submit → form reloads with no user created (no visible error message)
- [ ] **Direct POST without website field** — use Postman/curl, omit `website` and `_form_token` → 422 response
- [ ] **Direct POST with invalid token** — send `_form_token=garbage` → form rejected
- [ ] **Direct POST with valid token submitted instantly** — curl GET /register, extract token, immediately POST → form rejected (< 2s)
- [ ] **Direct POST with valid token after 3 seconds** — same as above but with `sleep 3` → if real user fields valid, registration succeeds
- [ ] **Source inspection** — view page source → `website` field is not visible in rendered HTML viewport, no label visible

### Regression tests

- [ ] Existing registration flow unchanged for human users
- [ ] `old()` values still repopulate on validation failure (name, email, currency, timezone)
- [ ] Email verification email still sends after successful registration
- [ ] Welcome email still dispatched
- [ ] Default categories still created

---

## Git Commit Message

```
security: add honeypot + timing protection to registration (SECURITY-01 / Phase B1)

RegisteredUserController:
- Generate encrypted timestamp token (formToken) on GET /register
- Pass to view for timing validation

register.blade.php:
- Add off-screen honeypot field (name=website, aria-hidden, tabindex=-1)
- Add hidden _form_token field for timing validation

RegisterRequest:
- Add 'website' => ['prohibited'] rule (honeypot)
- Add '_form_token' => ['required', 'string'] rule
- Add withValidator() timing check: decrypt token, reject if < 2 seconds
- Errors on 'website' and '_form_token' not displayed (no @error directives)
- No packages installed — custom implementation only

Blocks: simple fill-all bots, direct POST bots, sub-2s automated submissions
Does not block: sophisticated bots with real browser rendering (→ Phase C CAPTCHA)

Refs: SECURITY-01-AUDIT-REPORT.md, SECURITY-01-PHASE-B1-HONEYPOT-REPORT.md
```
