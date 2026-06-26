# SECURITY-02-PHASE-P5-PHONE-NOTICE-REPORT
**Sprint:** SECURITY-02 — Phone Registration / Phase P5: Phone Completion Notice  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 1**

| File | Change |
|---|---|
| `resources/views/layouts/app.blade.php` | Added phone notice banner between impersonation bar and main layout div |

No routes added. No migrations. No new components. No DB columns.

---

## Display Conditions

The notice renders only when **both** conditions are true:

| Check | Layer | Condition |
|---|---|---|
| User has no phone | PHP / Blade | `@auth ... @if(! auth()->user()->phone)` |
| User has not dismissed | JS / Alpine | `! sessionStorage.getItem('phone_notice_dismissed')` |

```blade
@auth
@if(! auth()->user()->phone)
<div x-data="{ show: ! sessionStorage.getItem('phone_notice_dismissed') }"
     x-show="show" x-cloak ...>
```

**Where it appears:**
- All authenticated app pages that extend `layouts/app.blade.php`

**Where it does NOT appear:**
- `layouts/guest.blade.php` — login, register, verify-email (different layout, no `@auth` context)
- `layouts/marketing.blade.php` — marketing/landing pages (different layout)
- Filament admin panel — Filament has its own layout stack, completely separate
- Print view — `print:hidden` class suppresses it when printing
- Users with a phone — `@if(! auth()->user()->phone)` renders nothing for them

---

## UI Copy

| Element | Arabic |
|---|---|
| Notice message | أضف رقم جوالك لتسهيل التواصل معك واستلام تنبيهات الاشتراك والدعم. |
| CTA button | إضافة رقم الجوال |
| Dismiss aria-label | إغلاق الإشعار |

**CTA link target:** `{{ route('settings.index') }}#profile`

The settings page uses `window.location.hash` to activate tabs: `x-data="{ tab: window.location.hash.replace('#','') || 'profile' }"`. The `#profile` hash lands the user directly on the profile tab where the phone field is.

---

## Dismiss Behavior

**Mechanism:** `sessionStorage` (browser-native, no backend, no DB)

```javascript
// On dismiss button click:
sessionStorage.setItem('phone_notice_dismissed', '1');
show = false;
```

**Lifecycle:**
- Dismissed notice is hidden immediately via Alpine `x-show="show"`.
- Dismissal persists for the browser session — survives page navigation and reloads.
- Clears automatically when the browser tab is closed or the browser session ends.
- Opening a new tab will show the notice again (sessionStorage is per-tab) — this is acceptable per the requirement ("hide for current session only").
- Once the user adds a phone and saves, `auth()->user()->phone` is no longer null, so the PHP `@if` resolves to false and the banner never renders — regardless of sessionStorage state.

**No server round-trip.** No POST route needed. No `session()` helper on the backend.

---

## Visual Design

Styled to match the existing app's banner pattern (see impersonation bar and flash messages):

```html
class="bg-amber-50 border-b border-amber-200 text-amber-800 text-sm
       px-4 py-2.5 flex items-center justify-between gap-4 print:hidden"
```

| Token | Value | Rationale |
|---|---|---|
| `bg-amber-50` | Light amber background | Warning/notice — not an error, not a success |
| `border-amber-200` | Subtle amber border-bottom | Matches existing banner borders in the layout |
| `text-amber-800` | Dark amber text | Sufficient contrast on amber-50 |
| `bg-amber-500 hover:bg-amber-600` | CTA button | Prominent, matches amber theme |
| `text-white` on CTA | — | Ensures contrast on amber-500 |

The banner sits **above** the sidebar/navbar layout (`min-h-screen flex`) so it spans the full viewport width, consistent with the impersonation bar above it.

**RTL:** The `<html dir="rtl">` set on the document root applies to this element automatically. The flex layout (`justify-between`) works naturally in RTL. No extra RTL overrides needed.

**Responsive:** The message uses `truncate` to handle narrow screens gracefully. CTA and dismiss button are in a `shrink-0` wrapper so they never squeeze off-screen.

---

## Accessibility

| Requirement | Implementation |
|---|---|
| `role="status"` or `role="alert"` | `role="status"` — non-urgent, informational (not a critical alert) |
| `aria-live="polite"` | Screen reader announces when notice appears, non-interruptive |
| Keyboard-accessible dismiss | `<button type="button">` (focusable, no form submission risk) |
| Dismiss `aria-label` | `aria-label="إغلاق الإشعار"` — describes the action in Arabic |
| Icon decorative | `aria-hidden="true"` on all SVGs — icons are decorative alongside text |
| CTA focus ring | `focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1` |
| Dismiss focus ring | `focus:outline-none focus:ring-2 focus:ring-amber-400 rounded` |
| `x-cloak` | Prevents flash-of-unstyled-content before Alpine mounts |

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| Banner shows on admin panel | 🟢 None | Filament uses its own layout; `layouts/app.blade.php` is never loaded in admin routes |
| Banner shows on guest pages | 🟢 None | `@auth ... @endauth` guard prevents any rendering for unauthenticated requests |
| `auth()->user()->phone` throws on unauthenticated page | 🟢 None | `@auth` block wraps the entire banner; unauthenticated users skip the block entirely |
| `sessionStorage` not available (old browser / SSR) | 🟢 Low | Alpine's `x-data` init gracefully returns `null` for `sessionStorage.getItem(...)`, which is falsy — banner shows. No crash. |
| Notice appears inside print output | 🟢 None | `print:hidden` Tailwind class removes the element from print |
| Settings `#profile` hash broken if tab name changes | 🟡 Low | Tab name is `'profile'` in the `x-data` init and in the hash — only risk is if the profile tab is renamed. Currently correct and stable. |
| `x-transition:leave` on a `border-b` element causes layout shift | 🟢 Acceptable | Transition animates `opacity` and `max-h` — the layout shift is intentional (the bar disappears). No content jump below because the main content isn't absolutely positioned. |
| Users who dismiss then add a phone see the notice again after a new session | 🟢 None | If phone is added: PHP `@if` resolves false → banner never renders, sessionStorage state irrelevant. |

---

## Git Commit Message

```
feat(layout): add phone completion notice for users without phone (SECURITY-02 / Phase P5)

layouts/app.blade.php:
- Amber banner above main layout, below impersonation bar
- Visible only when auth()->user()->phone is null
- Dismissible via Alpine + sessionStorage (no DB column, no backend route)
- CTA links to /settings#profile (hash activates profile tab directly)
- role="status" aria-live="polite" for screen readers
- Keyboard-accessible dismiss button with Arabic aria-label
- print:hidden — suppressed on print
- Does not appear on guest/marketing/admin layouts
- Does not block any user flow

No routes. No migrations. No new components.

Refs: SECURITY-02-PHASE-P1-REPORT.md through SECURITY-02-PHASE-P4-USERRESOURCE-PHONE-REPORT.md
```
