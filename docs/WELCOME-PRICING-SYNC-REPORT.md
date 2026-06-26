# Welcome Pricing Sync Report

**Date:** 2026-06-26  
**Status:** ✅ Completed

---

## Files Modified

| File | Type |
|---|---|
| `resources/views/welcome.blade.php` | Blade template |
| `public/marketing/js/home.js` | Frontend JS (pricing toggle) |

No backend files touched. No migration. No config changes.

---

## Old Values Replaced

| Location | Old | New |
|---|---|---|
| Toggle badge | وفّر 20% | وفّر حتى 25% |
| Pro price (monthly) | 18 | `config('billing.plans.pro.monthly.price', '17')` |
| Pro price (yearly) in JS | 14 | 13 |
| Business/Team price (monthly) | 29 | `config('billing.plans.business.monthly.price', '45')` |
| Business/Team price (yearly) in JS | 23 | 34 |
| Card label | "الفريق" | `config('billing.plans.business.label', 'الأعمال')` |
| JS key | `team` | `business` |
| Starter: حتى 3 عملاء | removed | حتى 3 مشاريع |
| Starter: فواتير غير محدودة | removed | حتى 5 عملاء |
| Starter: إدارة المهام الأساسية | removed | 5 فواتير شهرياً |
| Starter: دعم عبر البريد | removed | 3 عروض أسعار شهرياً |
| — | — | 50 معاملة شهرياً (added) |

---

## Official Values Applied

Prices now read from `config('billing.plans')` — single source of truth in `config/billing.php`:

| Plan | Monthly | Annual |
|---|---|---|
| Pro | $17 | $13/month |
| Business | $45 | $34/month |

Discount: Pro saves 23.5%, Business saves 24.4% → badge says "وفّر حتى 25%" ✓

### Starter Features (official)
- حتى 3 مشاريع
- حتى 5 عملاء
- 5 فواتير شهرياً
- 3 عروض أسعار شهرياً
- 50 معاملة شهرياً

### Pro Features (official)
- مشاريع غير محدودة
- عملاء غير محدودين
- 1,000 معاملة شهرياً
- إرسال الفواتير بالبريد
- الصناديق المالية
- التقارير المتقدمة

### Business Features (official)
- كل مزايا الإحترافية
- أعضاء فريق أكثر
- API Access
- صلاحيات متقدمة
- دعم أولوية

---

## CTA Links

All `href="#"` removed from the pricing section.

| Card | Guest | Authenticated |
|---|---|---|
| البداية (Starter) | `route('register')` — "ابدأ مجاناً" | `route('dashboard')` — "انتقل للوحة التحكم" |
| الإحترافية (Pro) | `route('register')` — "ابدأ الآن" | `route('billing.upgrade')` — "اشترك الآن" |
| الأعمال (Business) | `route('register')` — "ابدأ الآن" | `route('billing.upgrade')` — "اشترك الآن" |

Implemented via `@auth / @else / @endauth` Blade directives — no JavaScript needed.

---

## Pricing Toggle

### Decision: Updated `data-plan="team"` → `data-plan="business"`

**Approach chosen:** Update both the blade attribute and JS key cleanly.

**Rationale:** Keeping `data-plan="team"` would require the JS object to still use key `"team"` even though the plan is now "business". This creates hidden coupling. Renaming both together is safer and self-documenting.

**Changes made:**

`welcome.blade.php`:
```html
<!-- Before -->
<span data-plan="team" class="pricing-price ...">29</span>

<!-- After -->
<span data-plan="business" class="pricing-price ...">{{ config('billing.plans.business.monthly.price', '45') }}</span>
```

`home.js`:
```js
// Before
const prices = {
  monthly: { pro: "18", team: "29" },
  yearly:  { pro: "14", team: "23" },
};

// After
const prices = {
  monthly: { pro: "17", business: "45" },
  yearly:  { pro: "13", business: "34" },
};
```

Toggle logic unchanged — still reads `data-billing` attribute and updates `.pricing-price` elements by `data-plan`.

---

## Currency Note

Added below the pricing grid:
> الأسعار بالدولار الأمريكي، والدفع السنوي يُحسب كقيمة سنوية كاملة.

---

## Remaining Risks

| Risk | Severity | Notes |
|---|---|---|
| `config('billing.plans')` not cached on production | 🟢 Low | Laravel caches config via `php artisan config:cache` — no runtime impact |
| `route('billing.upgrade')` unavailable in guest context | 🟢 None | Wrapped in `@auth` — guests see `route('register')` only |
| `route('dashboard')` redirect for unverified users | 🟢 Low | Handled by middleware; user is redirected to verify-email page as expected |
| Hero CTA "ابدأ تجربتك المجانية" still links to `href="#"` | 🟡 Out-of-scope | Task scope is pricing section only; Hero CTA is a separate concern |
| Final CTA section "ابدأ الآن" links to `href="#"` | 🟡 Out-of-scope | Same as above |
| `home.js` prices hardcoded (not read from config at runtime) | 🟡 Acceptable | JS runs client-side so cannot read PHP config; prices are correct as of this sync. If prices change in config, JS must be updated manually. Consider adding a `data-price-monthly` / `data-price-annual` attribute to the price spans in future. |

---

## Git Commit Message

```
fix(marketing): sync welcome.blade.php pricing with official Darahum plans

welcome.blade.php:
- Rename "الفريق" → "الأعمال" (Business plan), read label from config()
- Pro price: 18 → config('billing.plans.pro.monthly.price') = 17
- Business price: 29 → config('billing.plans.business.monthly.price') = 45
- Toggle badge: "وفّر 20%" → "وفّر حتى 25%"
- Starter features: replace 4 incorrect items with 5 official limits
  (3 projects, 5 clients, 5 invoices/mo, 3 quotes/mo, 50 transactions/mo)
- Pro features: replace 5 items with 6 official features
  (unlimited projects, unlimited clients, 1000 tx/mo, email invoices, wallets, advanced reports)
- Business features: replace 4 items with 5 official features
  (all-pro, more team, API access, advanced permissions, priority support)
- CTA links: remove all href="#", add @auth/@else routing
  Starter: guest→register / auth→dashboard
  Pro + Business: guest→register / auth→billing.upgrade
- Add currency disclaimer under pricing grid

home.js:
- prices.monthly: {pro: "18", team: "29"} → {pro: "17", business: "45"}
- prices.yearly:  {pro: "14", team: "23"} → {pro: "13", business: "34"}
- Rename key "team" → "business" (matches data-plan attribute in blade)

No backend changes. No migration. No config changes.
```
