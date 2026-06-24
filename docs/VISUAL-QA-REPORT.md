# VISUAL QA REPORT — Phase 2 UI Improvements
**Date:** 2026-06-25  
**Scope:** Static code audit (5 pages)  
**Method:** Full file read + cross-reference against layout, component APIs, and design tokens  
**Phases covered:** 2.1 / 2.2 / 2.3 / 2.4 / 2.5 / 2.6

---

## Summary Table

| Page | RTL | Mobile | Flash | Buttons | Alpine | CTAs | SVG | Verdict |
|---|---|---|---|---|---|---|---|---|
| `invoices/show` | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ | **BLOCKER** |
| `reports/index` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | PASS |
| `crm/clients/index` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | PASS |
| `quotes/index` | ✅ | ✅ | ✅ | ✅ | n/a | n/a | ✅ | PASS |
| `settings/index` | ✅ | ⚠ | ⚠ | ✅ | ✅ | n/a | ✅ | PASS (minor) |

---

## Launch Blockers

### ❌ BLOCKER — `invoices/show.blade.php` — Duplicate Flash (success + error)

**Root cause:** Phase 2.1 removed `sm:hidden` from the layout body flash, making it visible on ALL screen sizes. `invoices/show.blade.php` still has its own page-level flash blocks at lines 224–239.

**Effect:** After marking an invoice paid/sent/cancelled, BOTH flashes render simultaneously:
1. Layout flash (top of body, all screen sizes) — from `layouts/app.blade.php`
2. Page flash (inside content area, lines 224–239) — from `invoices/show.blade.php` itself

This affects `session('success')` AND `session('error')` independently.

**Fix:** Remove the page-level flash at `invoices/show.blade.php` lines 223–239. The `session('error')` for email failures is also handled by the layout error flash added in Phase 2.1 (line 66–73 of `app.blade.php`).

```diff
- {{-- رسائل النجاح والخطأ --}}
- @if(session('success'))
- <div class="flex items-center gap-2 bg-success-soft border border-success/30 text-success-700 rounded-xl px-4 py-3 text-sm print:hidden">
-     ...
- </div>
- @endif
- @if(session('error'))
- <div class="flex items-center gap-2 bg-error-soft border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm print:hidden">
-     ...
- </div>
- @endif
+ {{-- Flash handled by layouts/app.blade.php --}}
```

---

## Page-by-Page Findings

---

### 1. `resources/views/invoices/show.blade.php`

**Sections checked:** action bar, back button, status badges, send email gate, WhatsApp+PDF button, download PDF, mark-sent, pay modal, print, edit, cancel, invoice paper (header/client/table/totals/notes), pay modal (wallet selection), JavaScript.

#### RTL Alignment ✅
- Back button: `M9 5l7 7-7 7` → right-pointing chevron (correct for RTL "back" navigation).
- Tooltip anchors to `start-0` (line 64) → logical-start = right in RTL. Correct.
- Invoice table uses `text-start` on description header, `text-center` on qty, `text-end` on price/total. Correct.
- Totals block: `flex justify-end` — in RTL this pushes totals panel to the logical-start (right). Correct visual behavior.
- Modal layouts use `flex justify-between` without physical bias.

#### Mobile Responsiveness ✅
- Action bar: `flex flex-wrap items-center justify-between gap-3`. Buttons wrap cleanly on narrow screens.
- Inner button div: `flex items-center gap-2 flex-wrap`. Multiple action buttons wrap on mobile.
- Invoice client grid: `grid grid-cols-1 md:grid-cols-2`. Full-width on mobile.
- Invoice table: no horizontal scroll protection — could overflow on very narrow screens if amounts are long. Pre-existing, not a regression.

#### Duplicated Flash ❌ **LAUNCH BLOCKER** (see above)

#### Buttons ✅
- All 8 action buttons have `inline-flex items-center gap-1.5` + `aria-label`. Correct.
- Cancel uses `confirm()` dialog. Functional.

#### Alpine Interactions ✅
- Send modal: `x-data="{ showSendModal: false }"` on outer div. `@click="showSendModal = true"` on trigger. `x-show="showSendModal"` + `x-cloak` on modal. Backdrop `@click` closes. All correct.
- Pay modal: `x-data` (empty) on trigger button + `$dispatch('open-pay-modal')`. Pay modal root `@open-pay-modal.window="open = true"` with `.window` modifier. Event dispatches from element, bubbles to window, listener fires. Correct Alpine v3 pattern.

#### SVG Icons ✅
- All 8+ action buttons have inline SVG (no emoji). Icons contextually correct: envelope (send), lock (locked send), WhatsApp brand icon, download arrow (PDF), send-paper (mark-sent), check-circle (pay), printer (print), pencil (edit).

#### Minor Issues
- **Tooltip arrow position** (line 67): `end-3` in RTL = 3px from logical-end (left side). Tooltip container anchors to `start-0` (right side). The caret could appear visually detached on small buttons. Low priority.
- **Modal FOUC pattern inconsistency**: Send modal uses `x-cloak` + `style="display:none"`. Pay modal uses only `style="display: none;"` (no `x-cloak`). Both functional. Cosmetic inconsistency.

---

### 2. `resources/views/reports/index.blade.php`

**Sections checked:** page-header, export gate (PDF+Excel, locked state + popover), period filter form, year buttons, KPI stat cards.

#### RTL Alignment ✅
- Export locked popover: `start-0` (corrected in Phase 2.2). In RTL = anchors to right. ✅
- Period filter: `flex items-center gap-2 flex-wrap` — items flow right-to-left in RTL. Dates, dash, apply button, year buttons. Natural RTL order.
- Year buttons active state: `border-brand bg-brand-50 text-brand-600`. No physical direction bias.

#### Mobile Responsiveness ✅
- `x-page-header` root `flex flex-wrap items-start justify-between gap-4` — title and export buttons wrap on mobile.
- Date filter `flex-wrap` — all inputs and buttons wrap. Usable on mobile.
- KPI cards: `x-stat-grid cols="4"` = `grid grid-cols-2 lg:grid-cols-4`. 2-col on mobile. ✅

#### Duplicated Flash ✅
No page-level `session('success')` or `session('error')`. Layout handles flash only.

#### Buttons ✅
- Export buttons: correct color coding (red for PDF, green for Excel).
- Locked export: `cursor-not-allowed` + Alpine click to show popover. Note: `cursor-not-allowed` combined with functional click interaction is semantically mixed but is an existing UX decision (intentionally disabled-looking but clickable to explain WHY).

#### Alpine Interactions ✅
- Locked export popover: `x-data="{ show: false }"`, `@click="show = !show"`, `@click.outside="show = false"`. Correct.

#### Upgrade CTA ✅
- `route('billing.upgrade')` in popover. Correct.

#### SVG Icons ✅
- PDF button: download + document icon. Excel: chart/bars icon. Lock icon for locked state. All consistent.

#### Minor Issues
- Date filter `<input type="date">` native inputs on mobile might have platform-specific width quirks (iOS Safari expands them). Pre-existing.

---

### 3. `resources/views/crm/clients/index.blade.php`

**Sections checked:** header (can/cannot branch), amber quota badge, upgrade card (heading, benefits, pricing, CTAs, trust bar), Alpine client list interactions.

#### RTL Alignment ✅
- Bulk toolbar: `ms-auto` (Phase 2.2). Correct — pushes group to logical-start (right) in RTL.
- Tag dropdown: `start-0` (Phase 2.2). Correct anchor.
- Upgrade card: `flex items-start gap-3` for heading row — icon and text flow correctly in RTL.
- Benefits list: `flex items-start gap-3` — icon left of text in LTR, but in RTL flex, icon appears to the right. Natural RTL reading: icon → text (right to left). Correct.

#### Mobile Responsiveness ✅
- Upgrade card: `p-5 sm:p-6` padding scales.
- CTA buttons: `flex flex-col sm:flex-row md:flex-col lg:flex-row` — stacks on mobile, inline on large screens.
- Benefits grid: `grid grid-cols-1 sm:grid-cols-2`. 1-col on mobile, 2-col on tablets+.

#### Duplicated Flash ✅
No page-level flash. Layout handles it.

#### Buttons ✅
- Primary CTA: `route('billing.upgrade')`. `aria-label="ترقية إلى Pro لإدارة عملاء غير محدودين"`. Correct.
- Secondary CTA: `route('billing.upgrade')`. Distinct styling (outline vs filled). Both functional.
- Amber quota badge: `aria-label="وصلت إلى الحد الأقصى: 5 عملاء في الخطة المجانية"`. Read-only — not a button. Correct `select-none`.

#### Upgrade CTA ✅
- Pricing from config: `config('billing.plans.pro.monthly.price', '17')`, `config('billing.plans.pro.monthly.currency', 'USD')`, `config('billing.plans.pro.annual.price', '13')`. Never hardcoded.
- Gate: `@cannot('create', App\Models\Client::class)`. Correct.
- Benefits listed: عملاء غير محدودين / CRM متقدم / المتابعات والتصنيفات / بوابة العميل. All four specified features present.
- Trust bar: لا يوجد عقد / ترقية خلال أقل من دقيقة / الاحتفاظ بجميع بياناتك. ✅

#### SVG Icons ✅
- Upgrade card heading: lock/people SVG — contextually appropriate.
- Benefit checkmarks: `✓` style SVG. All `aria-hidden="true"`.

#### Minor Issues
- `bg-gradient-to-l` top strip uses physical direction. In RTL the gradient runs from physical-right (violet) to physical-left (indigo). Looks fine — gradient direction doesn't need to be logical. Acceptable.

---

### 4. `resources/views/quotes/index.blade.php`

**Sections checked:** 5-column stats grid, x-stats-card props, SVG icon paths, table headers, empty state.

#### RTL Alignment ✅
- Stats grid wrapper: `grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4` — no physical bias.
- `x-stats-card` component internal layout: `flex items-start justify-between gap-3` — in RTL, icon appears on left, text on right. Correct.
- Table `align="left"` on total column: maps to `text-left` (physical). In RTL this is the end-side, which is correct for numeric columns. Not using logical property (`text-end`) but result is visually correct. Pre-existing component limitation.

#### Mobile Responsiveness ✅
- `grid-cols-2 lg:grid-cols-5`: on mobile, 5 cards display as 2+2+1 grid. The last card takes a full half-row. Acceptable.
- `x-stats-card` uses `p-5` — comfortable touch target area.

#### Duplicated Flash ✅
No page-level flash.

#### SVG Icons ✅
All 5 icon paths audited — split across lines in blade but SVG `d` attribute whitespace is valid. Paths:
- إجمالي العروض: document icon (correct)
- في الانتظار: clock icon (correct)
- مقبولة: check-circle (correct)
- مرفوضة: x-circle (correct)
- محوّلة لفاتورة: refresh/convert icon (correct)

#### Minor Issues
- `color="accent"` on "محوّلة لفاتورة" card maps to `bg-accent-50` / `text-accent-700` in `stats-card.blade.php`. These are custom design tokens — verified they exist in the component's color map. ✅
- `align="left"` in `x-table-th` emits `text-left` (physical). The component itself uses physical alignment values (`'left' => 'text-left'`). Should eventually be updated to use logical (`text-end`) but is pre-existing and out of scope.

---

### 5. `resources/views/settings/index.blade.php`

**Sections checked:** tab navigation (5 buttons), SVG icon paths, Alpine click handlers + hash routing, ARIA attributes, conditional invoice-template flash.

#### RTL Alignment ✅
- Tab buttons: `inline-flex items-center justify-center gap-1.5`. In RTL, flex reverses: icon appears to the right of text in the visual layout. For Arabic UI this is acceptable (icon on reading-start side).
- Tab panel content: no physical alignment bias in the tab switching logic itself.

#### Mobile Responsiveness ⚠ MINOR
- 5 tabs in `flex gap-1` row with `flex-1`. Each tab: `text-xs sm:text-sm font-medium py-2 px-3`. On a 375px screen, 5 tabs = ~71px each after padding/gaps. Arabic labels like "الملف الشخصي" (~68px at text-xs) may force text to wrap inside the button. This was the same constraint before Phase 2.6 (emoji + text were equally wide). Not a regression.
- Recommendation: Test on 375px device. If wrapping occurs, consider hiding text on the smallest breakpoint (`hidden xs:inline`) and showing icon only.

#### Duplicated Flash ⚠ MINOR
- Line 628: `@if(session('success') && str_contains(session('success') ?? '', 'فاتورة'))` — conditional inline flash inside the invoice template tab panel.
- The layout ALSO shows the full success flash. When saving invoice settings, the message appears twice: once at the top of the page (layout flash, auto-dismisses in 4s), once inside the tab panel (inline, persistent).
- This is partially intentional — the inline flash gives contextual feedback near the form. But it IS a duplicate after Phase 2.1. Not a launch blocker (the layout flash auto-dismisses, the inline one is persistent inline context). Track for Phase 3.

#### Alpine Interactions ✅
- All 5 tab buttons: `@click="tab = 'X'; window.location.hash = 'X'"`. Correct.
- `:class` bindings for active/inactive state verified on all 5 buttons.
- Hash-based initial tab state preserved (unmodified from before Phase 2.6).

#### SVG Icons ✅
- Profile: person path. ✅
- Invoice template: document path. ✅  
- Security: lock path. ✅
- Preferences: gear/cog path (long multi-line `d` attribute — valid SVG whitespace). ✅
- Plan: credit card path. ✅
- All icons: `aria-hidden="true"`, `class="w-3.5 h-3.5 shrink-0"`. ✅

#### ARIA ✅
- Tablist: `role="tablist"`, `aria-label="أقسام الإعدادات"`.
- Each tab: `role="tab"`, `:aria-selected="tab === 'X'"`, `aria-controls="X"`.
- All decorative SVGs: `aria-hidden="true"`.

---

## Findings Register

### Launch Blockers (must fix before launch)

| # | File | Issue | Fix |
|---|---|---|---|
| B1 | `invoices/show.blade.php` L223–239 | Duplicate success + error flash after Phase 2.1 layout consolidation | Remove page-level flash block; layout handles both |

### Minor Polish (can ship, fix in next cycle)

| # | File | Issue | Priority |
|---|---|---|---|
| P1 | `invoices/show.blade.php` L67 | Tooltip caret `end-3` may look detached from `start-0` anchor in RTL | Low |
| P2 | `invoices/show.blade.php` L360 | Pay modal uses `style="display:none"` instead of `x-cloak` | Low |
| P3 | `settings/index.blade.php` L628 | Conditional inline flash is a soft duplicate of layout flash after Phase 2.1 | Medium |
| P4 | `settings/index.blade.php` tabs | 5 tabs may force label text-wrap on 375px screens | Medium |
| P5 | `quotes/index.blade.php` L93 | `align="left"` on table-th emits `text-left` (physical). Should be `text-end` logically. Component-level fix needed. | Low |

### Pre-existing Issues (out of scope, documented)

| # | File | Issue |
|---|---|---|
| E1 | `crm/clients/index.blade.php` | Stat bar hover colors use `text-teal-500` (CL2 from PAGE-AUDIT-REPORT) |
| E2 | `quotes/index.blade.php` | `x-table-th` component uses physical alignment values (`text-left`, `text-right`) throughout |
| E3 | `settings/index.blade.php` | Tab panel section headings still use emoji (e.g. `👤`) — inside panel content, not tab labels |

---

## Recommended Action

**Before launch:**
1. Fix B1: Remove the page-level flash from `invoices/show.blade.php` (lines 223–239). 2-line change.

**Post-launch / Phase 3:**
2. Address P3 (settings inline flash) and P4 (tab mobile wrapping) together in a settings polish pass.
3. Update `x-table-th` component to use logical alignment values (`text-start` / `text-end`) — single-file component fix that benefits all pages using the component.
