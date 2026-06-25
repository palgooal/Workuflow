# TOGO-PAYMENT-INTEGRATION-AUDIT.md

> **Date:** 2026-06-25
> **Auditor:** Claude — Senior Laravel SaaS Engineer Review
> **Scope:** Togo.ps payment gateway integration — read-only audit, no code changes
> **Verdict:** State **B+** — Code infrastructure exists end-to-end, but UI doesn't trigger it and session-only order tracking makes automatic activation unreliable

---

## Executive Summary

The Togo payment integration is **further along than it appears from the outside**. The backend plumbing — routes, controller, service, callback handlers, and activatePlan() wiring — is all present and largely correct. However, two critical gaps block automatic activation:

1. **The UI never triggers the payment flow.** Every "Upgrade" button on `billing.index` and `billing.upgrade` links to the WhatsApp manual flow, not to `POST /billing/checkout`. The checkout route exists but is never reached by a real user.

2. **Order state is stored only in session.** If a user pays on Togo and their browser session expires before the redirect callback fires (or they close the tab after paying), the subscription is never activated — with no recovery mechanism.

Additionally, `billing.index` shows hardcoded pricing (99 SAR, 299 SAR, 2 projects, 500 transactions) that contradicts `PRICING-SOURCE-OF-TRUTH.md` and `SubscriptionPlan.php`. This is a T18 violation that was not caught in Sprint-01 because the audit only checked the marketing pricing and upgrade pages, not the billing management page.

**Priority for Sprint-02:** Add a `payment_orders` table, wire a checkout button in the UI, and update billing.index pricing — then the existing backend will work.

---

## Current Integration State

**Classification: B — Gateway partially integrated**

> The correct option is a combination of **B** and **D**:
> - **B**: Payment flow code exists (createCheckoutUrl → Togo → verifyOrder → activatePlan) and is syntactically correct.
> - **D**: The billing/upgrade page still exclusively uses WhatsApp; no button in the UI POSTs to `billing.checkout`.

The net effect is identical to **D** from the user's perspective — automatic activation never fires because no real user ever reaches the checkout route.

---

## Current Flow (As-Built)

### What a user sees today

```
billing.index
  → "الترقية إلى Pro" button
  → route('billing.upgrade')        ← links here, NOT to checkout
  → billing.upgrade (WhatsApp page)
  → user messages admin on WhatsApp
  → admin activates manually via Filament admin panel
```

### What the backend is capable of (if triggered)

```
POST /billing/checkout  {plan: 'pro'}
  └─ BillingController::checkout()
       └─ TogoPaymentService::createCheckoutUrl($user, 'pro')
            └─ POST https://api.togo.ps/api/v1/actions  {event: 'Create_Visa', ...}
            └─ stores session: {togo_order_id, togo_order_plan}
            └─ returns redirect URL to togo.ps/direct-pay?orderId=...
  └─ redirect()->away($url)

User pays on Togo → Togo redirects browser back to:
GET /billing/togo/callback
  └─ BillingController::togoCallback()
       └─ reads session: togo_order_id, togo_order_plan
       └─ TogoPaymentService::verifyOrder($orderId)  ← polls GET /api/v1/orders?id=...
       └─ if status === 'PAID':
            └─ SubscriptionService::activatePlan($user, $plan, $orderId) ✅
            └─ redirect → billing.success
       └─ if not PAID:
            └─ redirect → billing.upgrade with error message

GET /billing/togo/cancel  ← Togo redirects here if user clicks "cancel"
  └─ clears session
  └─ redirect → billing.upgrade with info message
```

The backend flow, if ever reached, **would work** — except for the fragile session dependency.

---

## Existing Files Found

| File | Status | Notes |
|------|--------|-------|
| `routes/web.php` | ✅ Present | POST /billing/checkout, GET /billing/togo/callback, GET /billing/togo/cancel |
| `app/Http/Controllers/BillingController.php` | ✅ Present | checkout(), togoCallback(), togoCancel(), success(), upgrade(), portal() |
| `app/Modules/Billing/Services/TogoPaymentService.php` | ✅ Present | createCheckoutUrl(), verifyOrder(), createReceiverAddress() |
| `app/Modules/Billing/Contracts/PaymentProviderInterface.php` | ✅ Present | createCheckoutUrl(), createPortalUrl(), parseWebhook() |
| `app/Modules/Billing/Services/SubscriptionService.php` | ✅ Present | activatePlan(), cancelPlan(), reactivatePlan(), downgradePlan() |
| `app/Filament/Pages/PaymentSettings.php` | ✅ Present | Full settings page with API key, receiver ID, currency, provider toggle |
| `app/Providers/AppServiceProvider.php` | ✅ Present | Reads DB settings at boot, binds PaymentProviderInterface → TogoPaymentService |
| `config/billing.php` | ✅ Present | Plans config, Togo credentials, owner_whatsapp |
| `database/migrations/..._create_subscriptions_table.php` | ✅ Present | ulid PK, user_id, plan, status, payment_provider, provider_subscription_id, starts_at, ends_at |
| `resources/views/billing/index.blade.php` | ⚠️ Present | Has pricing cards but hardcoded SAR prices, links to WhatsApp not checkout |
| `resources/views/billing/upgrade.blade.php` | ✅ Present | WhatsApp + manual upgrade — reads from config correctly |
| `resources/views/billing/success.blade.php` | ✅ Present | Shows plan name after successful payment |
| `app/Console/Commands/TogoSetupReceiverCommand.php` | ✅ Present | One-time receiver address setup |
| **`payment_orders` table / migration** | ❌ MISSING | No persistent record of payment attempts |
| **Stripe webhook handler** | 🟡 Stub only | Returns 200 OK, no processing — leftover from early scaffold |

---

## Payment Settings Page Analysis (`/admin/payment-settings`)

### What is stored in the `settings` table (group: `payment`)

| Key | Type | Source | Purpose |
|-----|------|--------|---------|
| `billing_provider` | string (`togo` or `''`) | Select dropdown | Enables/disables automatic gateway |
| `togo_api_key` | string | Text (password) | Togo API authentication |
| `togo_receiver_address_id` | string | Auto-filled after create | Target account for payments |
| `togo_currency` | string (`ILS` or `USD`) | Select | Currency sent to Togo API |
| `billing_price_pro` | numeric string | Text input | Price used in `getPlanPrice()` |
| `billing_price_business` | numeric string | Text input | Price used in `getPlanPrice()` |
| `billing_currency_display` | string | Select | Display label on UI |

### What `AppServiceProvider::applyPaymentSettings()` does on every request

Reads entire `payment` group from cache → sets matching `config()` values at runtime:
- `billing.provider` → controls `isPaymentProviderConfigured()` and DI binding
- `billing.togo.api_key`, `billing.togo.receiver_address_id`, `billing.togo.currency`
- `billing.plans.pro.price`, `billing.plans.business.price` ← used by `getPlanPrice()`
- `billing.plans.pro.currency`, `billing.plans.business.currency`

### Settings page capabilities
- ✅ Enable/disable gateway (provider field)
- ✅ API key storage (encrypted at rest if `ENCRYPTED=true` is set; currently plain text in DB)
- ✅ Receiver address creation flow with validation
- ✅ Connection test against Togo API
- ❌ No sandbox/live mode toggle (Togo only has one environment)
- ❌ No webhook secret/token field (Togo doesn't use webhooks — uses redirect callbacks)
- ❌ Callback URLs are NOT displayed to admin (useful to show for Togo dashboard config)

---

## Missing Pieces

### Critical (blocks automatic activation)

| # | What's Missing | Impact |
|---|---------------|--------|
| 1 | **Checkout button in UI** — no form anywhere POSTs to `POST /billing/checkout` | Users never reach Togo |
| 2 | **`payment_orders` DB table** — order state lives only in PHP session | Payment lost if browser closes or session expires |
| 3 | **Idempotency guard on activatePlan()** — nothing prevents double-activation if callback fires twice | Duplicate subscriptions possible |

### Important (reliability + operations)

| # | What's Missing | Impact |
|---|---------------|--------|
| 4 | **Togo "poll on return" fallback** — if redirect fires before Togo confirms PAID, activation fails | Timing race condition |
| 5 | **Admin payment logs screen** — no way to see all payment attempts, statuses, or failed orders | Zero visibility |
| 6 | **User-facing failure page** — on failure, user is redirected to upgrade with a flash message; no dedicated failure view | Poor UX |
| 7 | **Callback URL display in admin** — admin should see the exact callback URLs to configure in Togo dashboard | Deployment confusion |

### Minor (consistency)

| # | What's Missing | Impact |
|---|---------------|--------|
| 8 | **`billing.index` pricing is hardcoded and wrong** — shows "99 SAR", "2 مشروع", "500 معاملة" which contradicts PRICING-SOURCE-OF-TRUTH and SubscriptionPlan.php | Misleads users |
| 9 | **`getPlanPrice()` depends on admin-set DB key** — if admin never visits `/admin/payment-settings` and saves, the key `billing.plans.pro.price` doesn't exist → throws RuntimeException "سعر الخطة [pro] غير مضبوط" | Checkout silently fails |
| 10 | **Stripe webhook stub** (`POST /stripe/webhook`) — leftover scaffolding, always returns 200 OK | Dead code; confusing |
| 11 | **`billing.index` FAQ says "via bank transfer"** — still manual language even if gateway is enabled | User confusion |

---

## Risks

### High Risk

**Session-based order tracking (current design)**

The `togo_order_id` and `togo_order_plan` are stored in PHP session. Togo's redirect callback requires these values to be present. The session will be missing if:
- User pays on a different device
- User opens checkout in incognito, pays, then restores session
- Server restarts between checkout and callback
- Session TTL expires (default: 120 minutes)
- Load balancer routes callback to a different server

**Result:** User is charged, subscription is NOT activated. User has no way to recover except contacting admin.

### Medium Risk

**Double-activation / race condition**

If user refreshes the `/billing/togo/callback` URL after paying, `togoCallback()` runs again. The second call to `verifyOrder()` will still return `PAID`, and `activatePlan()` will run again. `activatePlan()` uses `updateOrCreate(['provider_subscription_id' => ...])` — but only when `$providerSubscriptionId` is non-null. If `provider_subscription_id` is already set from the first activation, it would find and update the existing record. This is partially idempotent but not fully safe.

**Result:** Possible duplicate subscription records depending on timing.

### Medium Risk

**`getPlanPrice()` depends on admin setup**

If `billing_price_pro` is never saved via admin panel, `config('billing.plans.pro.price')` is not set (the config file uses `monthly.price`, not a top-level `price`). The checkout will throw RuntimeException and redirect back with an error. This is silent — admin would not know unless they check logs.

### Low Risk

**API key stored as plain text in `settings` table**

If DB is compromised, the Togo API key is exposed. Mitigation: encrypt the column, or use `.env` + `config()` only (not DB). This is a pre-existing design decision, acceptable for MVP.

---

## Required Database Support

### New Table: `payment_orders`

```sql
CREATE TABLE payment_orders (
    id          CHAR(26) PRIMARY KEY,          -- ULID
    user_id     BIGINT UNSIGNED NOT NULL,
    plan        ENUM('pro', 'business') NOT NULL,
    provider    VARCHAR(50) NOT NULL DEFAULT 'togo',
    provider_order_id   VARCHAR(255) NULLABLE,   -- Togo internal order ID (id)
    provider_hashed_id  VARCHAR(255) NULLABLE,   -- Togo hashed_id (used in redirect URL)
    amount      DECIMAL(10, 2) NOT NULL,
    currency    CHAR(3) NOT NULL DEFAULT 'ILS',
    status      ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    paid_at     TIMESTAMP NULLABLE,
    failed_at   TIMESTAMP NULLABLE,
    metadata    JSON NULLABLE,                   -- full Togo API response
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,

    INDEX idx_user_status (user_id, status),
    INDEX idx_provider_order (provider, provider_order_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Why this matters:**
- Allows recovery if session expires
- Enables admin visibility into all payment attempts
- Provides idempotency (`WHERE provider_order_id = ? AND status = 'paid'` before activating)
- Enables future reconciliation with Togo dashboard

---

## Required Routes

| Route | Method | Current State | Action Needed |
|-------|--------|--------------|---------------|
| `POST /billing/checkout` | POST | ✅ Exists | Wire UI to post here |
| `GET /billing/togo/callback` | GET | ✅ Exists | Refactor to read from DB, not session |
| `GET /billing/togo/cancel` | GET | ✅ Exists | Update to mark DB record as cancelled |
| `GET /billing/success` | GET | ✅ Exists | No change needed |
| `GET /billing/failed` | GET | ❌ Missing | Add dedicated failure page |

No new routes needed — existing routes are sufficient. Minor refactoring of callback handler to use DB instead of session.

---

## Required Services

### Changes to `TogoPaymentService`

1. **`createCheckoutUrl()`** — after creating Togo order, create a `PaymentOrder` DB record (status: pending) and store `payment_order_id` in session (just an ID, not the full order data).
2. **`verifyOrder()`** — no change needed.
3. **`getPlanPrice()`** — fix to read from `config('billing.plans.pro.monthly.price')` as fallback if admin-set key is missing.

### Changes to `BillingController::togoCallback()`

1. Look up `PaymentOrder` by `payment_order_id` from session (or `provider_order_id` from query param if Togo sends it).
2. Call `verifyOrder()` as now.
3. On PAID: update `payment_order.status = paid`, set `paid_at`, call `activatePlan()`.
4. Guard against double-processing: `if ($order->status === 'paid') → redirect to success`.
5. On failure: update `payment_order.status = failed`.

### New: `PaymentOrder` Model

Simple Eloquent model for the `payment_orders` table with scopes: `->pending()`, `->paid()`.

---

## Required Admin Screens

| Screen | Location | What it shows |
|--------|----------|---------------|
| Payment Orders list | `/admin/payment-orders` | All payment attempts: user, plan, amount, status, date, provider_order_id |
| Payment Settings improvement | `/admin/payment-settings` | Add: display of callback URLs, copy-to-clipboard |

### Callback URLs Display (add to PaymentSettings page)

```
Togo Success Callback: https://darahum.com/billing/togo/callback
Togo Cancel Callback:  https://darahum.com/billing/togo/cancel
```

These should be shown to the admin so they can verify the URLs match what's configured in the Togo merchant dashboard.

---

## Required User Screens

| Screen | Current State | Action |
|--------|--------------|--------|
| Checkout trigger (Pro/Business) | ❌ Links to WhatsApp | Add `<form method="POST" action="/billing/checkout"><input name="plan" value="pro">` button |
| Payment processing / loading | ❌ Missing | Optional: interstitial "Redirecting to payment..." page |
| Success page (`billing.success`) | ✅ Exists | No change |
| Failure page (`billing.failed`) | ❌ Missing | New view: explain what happened, options to retry or contact WhatsApp |
| `billing.index` pricing | ⚠️ Wrong values | Fix hardcoded 99 SAR / 2 projects / 500 transactions to match PRICING-SOURCE-OF-TRUTH |

---

## Recommended MVP Flow

```
1. User on billing.index clicks "الترقية إلى Pro"
   → POST /billing/checkout  {plan: 'pro'}

2. BillingController::checkout()
   → validates user not already on Pro
   → calls TogoPaymentService::createCheckoutUrl($user, 'pro')

3. TogoPaymentService::createCheckoutUrl()
   → calls Togo POST /api/v1/actions  {event: 'Create_Visa', value: 17, currency: 'ILS', ...}
   → receives {id, hashed_id, ...}
   → creates PaymentOrder {user_id, plan: 'pro', provider_order_id: id,
                           provider_hashed_id: hashed_id, amount: 17,
                           currency: 'ILS', status: 'pending'}
   → stores in session: {payment_order_id: <local ULID>}  ← just the local ID
   → returns redirect URL: togo.ps/direct-pay?orderId=<hashed_id>&receiverEmail=...

4. BillingController → redirect()->away($url)
   User pays on Togo's hosted page

5A. Payment SUCCESS → Togo redirects browser to:
   GET /billing/togo/callback

   BillingController::togoCallback()
   → reads payment_order_id from session
   → loads PaymentOrder from DB
   → guards: if order.status == 'paid' → redirect to success (idempotent)
   → calls TogoPaymentService::verifyOrder(order.provider_order_id)
   → Togo returns {status: 'PAID', ...}
   → updates PaymentOrder {status: 'paid', paid_at: now(), metadata: response}
   → calls SubscriptionService::activatePlan($user, 'pro', $order->provider_order_id)
   → clears session payment_order_id
   → redirect → billing.success  ✅

5B. Payment CANCELLED → Togo redirects browser to:
   GET /billing/togo/cancel

   BillingController::togoCancel()
   → reads payment_order_id from session
   → updates PaymentOrder {status: 'cancelled'}
   → clears session
   → redirect → billing.upgrade with info message

5C. Payment status ambiguous (Togo says not PAID yet):
   → updates PaymentOrder {status: 'failed', failed_at: now()}
   → redirect → billing.failed with clear message + WhatsApp fallback link

5D. Session expired (user closed browser):
   → PaymentOrder remains 'pending' in DB
   → Admin can verify manually via /admin/payment-orders
   → Admin can manually activate via SubscriptionResource::reactivate() action
```

---

## Compatibility With Manual WhatsApp Flow

The WhatsApp flow is preserved and continues to work unchanged. The integration is **additive**:

```
billing.provider = '' (empty)      →  isPaymentProviderConfigured() = false
                                   →  billing.index shows WhatsApp CTA
                                   →  billing.upgrade shows WhatsApp buttons
                                   →  checkout() returns early with 'info' flash

billing.provider = 'togo'          →  isPaymentProviderConfigured() = true
                                   →  billing.index shows checkout form buttons
                                   →  billing.upgrade still shows WhatsApp as secondary option
                                   →  checkout() proceeds to Togo
```

The admin can toggle between modes from `/admin/payment-settings` with no code changes. WhatsApp stays visible on `billing.upgrade` as a permanent fallback regardless of gateway status — important for users who prefer offline payment or whose automatic payment fails.

---

## Implementation Plan

### Phase 1 — Foundation (DB + Model)
**Files:** new migration, new `PaymentOrder` model
**Work:**
- Migration: `create_payment_orders_table`
- Model: `App\Models\PaymentOrder` with fillable, casts, scopes, user relation
- Estimated time: ~30 minutes

### Phase 2 — Payment Initiation (UI + Service fix)
**Files:** `billing/index.blade.php`, `TogoPaymentService.php`
**Work:**
- Fix `getPlanPrice()` to read `billing.plans.{plan}.monthly.price` as config fallback
- Add checkout form buttons to `billing.index` (when `$providerReady = true`)
- Update `createCheckoutUrl()` to create `PaymentOrder` record before redirect
- Fix `billing.index` pricing display (99 SAR → $17, 2 projects → 3, etc.)
- Estimated time: ~60 minutes

### Phase 3 — Callback Handler (DB-based, idempotent)
**Files:** `BillingController.php`, `TogoPaymentService.php`
**Work:**
- Refactor `togoCallback()` to load `PaymentOrder` from DB (not just session)
- Add idempotency guard
- Update `togoCancel()` to mark order cancelled in DB
- Add `billing.failed` view
- Estimated time: ~45 minutes

### Phase 4 — Auto Subscription Activation
**Files:** `SubscriptionService.php`, `BillingController.php`
**Work:**
- `activatePlan()` already exists and works correctly ✅
- Add activation timestamp to `PaymentOrder` (already in `paid_at`)
- Verify `provider_subscription_id` stored correctly on `Subscription`
- Estimated time: ~15 minutes (mostly validation/testing)

### Phase 5 — Admin Payment Logs
**Files:** new `PaymentOrderResource.php`
**Work:**
- Filament resource: list of all payment orders
- Columns: user.name + email, plan, amount + currency, status badge, provider_order_id (copyable), paid_at, created_at
- Filters: status, plan, date range
- Action: "Mark as Paid + Activate" (manual recovery for lost callbacks)
- Update `PaymentSettings` page to display callback URLs
- Estimated time: ~60 minutes

### Phase 6 — End-to-End Testing
**Work:**
- Test with Togo sandbox/test credentials
- Test session-expiry recovery path
- Test double-callback idempotency
- Test WhatsApp fallback when provider = ''
- Test billing.index pricing display matches PRICING-SOURCE-OF-TRUTH
- Estimated time: ~90 minutes

**Total estimated implementation time: ~5 hours**

---

## Final Recommendation

**Build in this exact order:**

1. **Phase 1 first** — create the `payment_orders` table. Everything else depends on it. Without a DB record, the backend is inherently fragile.

2. **Phase 2 + 3 together** — they're tightly coupled. The checkout creates the order; the callback reads it. Don't wire the UI until the callback handler is DB-based.

3. **Fix `billing.index` pricing in Phase 2** — it currently shows wrong values (99 SAR, 2 projects) that contradict `SubscriptionPlan.php`. This is a user-facing T18 violation.

4. **Phase 4 requires zero new code** — `activatePlan()` already works. It just needs to be called by a reliable (DB-backed) callback.

5. **Phase 5 (admin logs) is operationally critical** — without it, a failed payment has no recovery path except raw DB access.

6. **Keep WhatsApp as permanent fallback** — even after Togo is live. Some users in Palestine/Jordan/KSA will prefer manual transfer. The current architecture already supports this via the `billing_provider` toggle.

**The shortest path to a working automatic payment:**
> Phase 1 (migration) → Phase 2 (UI button + getPlanPrice fix) → Phase 3 (DB-backed callback) → Phase 4 (verification) → go live with gateway enabled.

Admin logs (Phase 5) can ship one sprint later as operations tooling.

---

*Audit performed via static code analysis — 2026-06-25*
*No code was modified during this audit.*
