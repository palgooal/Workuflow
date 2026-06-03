# Workuflow — CRM: Health Score & Segments Module
## Implementation Reference — V1.0

> **Document Type:** Implementation Documentation  
> **Module:** Client Health Score + Client Segmentation  
> **Platform:** Workuflow — Financial & Business Management SaaS  
> **Sprint:** Sprint 5 (S5.1 – S5.3)  
> **Status:** ✅ Implemented & Production-Ready  
> **Last Updated:** June 2026  
> **Related Docs:** `CLIENTS-CRM-SPEC-V2.md` (§9.3, §10.1 Sprint 5)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Client Health Score](#2-client-health-score)
3. [Client Segmentation Engine](#3-client-segmentation-engine)
4. [Routes & Controllers](#4-routes--controllers)
5. [Commands & Scheduler](#5-commands--scheduler)
6. [Database](#6-database)
7. [UI & Help Center](#7-ui--help-center)
8. [Configuration Reference](#8-configuration-reference)
9. [Implementation Notes & Known Gaps](#9-implementation-notes--known-gaps)

---

## 1. Overview

Sprint 5 delivered two interconnected features:

| Feature | Sprint Task | File |
|---------|-------------|------|
| Health Score Algorithm V2 | S5.1 | `app/Modules/CRM/Services/ClientHealthScoreService.php` |
| Smart Tag Suggestion | S5.2 | `app/Modules/CRM/Services/SmartTagSuggestionService.php` |
| Segment Engine | S5.3 | `app/Modules/CRM/Services/ClientSegmentEngine.php` |
| Recalculate Command | S5.2 | `app/Console/Commands/RecalculateHealthScoresCommand.php` |
| Segment Controller | — | `app/Modules/CRM/Http/Controllers/ClientSegmentController.php` |
| Segment View | — | `resources/views/crm/segments/index.blade.php` |
| Help Center Entry | — | `resources/views/help/index.blade.php` (tab: `crm`) |

---

## 2. Client Health Score

### 2.1 Algorithm Summary

Every client receives a score from **0–100** computed by `ClientHealthScoreService`. The formula:

```
Score = Σ (factor_value × factor_weight) × 100
```

All factor values are normalized to **[0.0 – 1.0]** before weighting.

### 2.2 Factors & Weights

Weights are loaded from `config/crm.php → health_score.weights`:

| Factor Key | Default Weight | What It Measures |
|---|---|---|
| `payment_rate` | **0.35** | `total_paid / total_revenue` (with recency bias) |
| `work_frequency` | **0.25** | Invoice/transaction count over 12 months |
| `revenue_value` | **0.20** | Revenue relative to `REVENUE_TOP` constant (10,000) |
| `contact_regularity` | **0.10** | Days since `last_contact_at` |
| `response_rate` | **0.10** | `completed_follow_ups / total_follow_ups` |

> **Sum of weights must equal 1.0.** Any deviation causes score drift. Validate in `config/crm.php` tests.

### 2.3 Recency Bias (V2)

Each factor is split into two windows and blended:

```
factor_value = (recent_score × recent_weight) + (historic_score × historic_weight)
```

Default split (from `config/crm.php → health_score.recency_bias`):

| Parameter | Default |
|---|---|
| `recent_months` | 3 |
| `recent_weight` | 0.70 |
| `historic_months` | 12 |
| `historic_weight` | 0.30 |

**Effect:** A client who paid on time for 2 years but stopped 3 months ago will see their score drop quickly. A previously poor client who improved recently will recover faster.

### 2.4 Factor Computation Details

#### `payment_rate`
```php
// Recent (3 months): paid / billed from transactions table
// Historic: total_paid / total_revenue from clients aggregate
// If total_revenue = 0: returns 0.5 (neutral — no invoices yet)
```

#### `work_frequency`
```php
// Counts rows in transactions table joined to projects table by client_id
// Recent count is annualized: recent_tx * (12 / recent_months)
// Cap: FREQ_TOP = 12 transactions/year = 1.0
```

#### `revenue_value`
```php
// Recent revenue annualized, then compared to REVENUE_TOP = 10,000
// Cap: min(1.0, annualized_revenue / 10_000)
```

#### `contact_regularity`
```php
// Based on clients.last_contact_at
// Step function (not linear):
//   ≤ 7 days  → 1.0
//   ≤ 30 days → 0.8
//   ≤ 60 days → 0.5
//   ≤ 90 days → 0.3
//   ≤ 180 days→ 0.1
//   > 180 days→ 0.0
// Historic portion: count of client_activities in last 12 months / 12
```

#### `response_rate`
```php
// Queries client_follow_ups table
// If total = 0: returns 0.6 (neutral — no follow-ups logged)
// Recent: same query limited to last N months
```

### 2.5 Score Grade Mapping

Defined in `app/Modules/CRM/Enums/HealthScoreGrade.php`:

| Score Range | Grade | Label (AR) | UI Color |
|---|---|---|---|
| 80–100 | `excellent` | ممتاز | Emerald |
| 60–79 | `good` | جيد | Blue |
| 40–59 | `fair` | متوسط | Amber |
| 0–39 | `poor` | ضعيف | Red |

### 2.6 Persistence

`ClientHealthScoreService::calculate(Client $client): int` does two things inside a DB transaction:

1. Updates `clients.health_score` (denormalized cache column)
2. Creates a row in `client_health_scores` (historical snapshot)

```php
// Return value: the computed integer score (0–100)
// Uses DB::transaction() — both writes succeed or neither does
```

`preview(Client $client): array` computes without writing to the database. Returns:
```php
['score' => int, 'grade' => string, 'label' => string, 'factors' => array]
```

### 2.7 Batch Recalculation

`recalculateForUser(int $userId, int $chunkSize = 200): array`

- Queries non-archived clients for the user
- Processes in chunks via `chunkById()` to avoid memory exhaustion
- Skips individual client failures (logs warning, continues)
- Returns `['processed' => int, 'avg_score' => float, 'duration_ms' => int]`

---

## 3. Client Segmentation Engine

### 3.1 Overview

`ClientSegmentEngine` translates a `filters` JSON array (stored in `saved_segments.filters`) into an Eloquent `Builder` query against the `clients` table.

### 3.2 Filter Schema

Each filter is an associative array:

```json
[
  { "field": "status",        "op": "equals",       "value": "active" },
  { "field": "health_score",  "op": "greater_than",  "value": 60 },
  { "field": "tag_ids",       "op": "in",            "value": [1, 2, 3] },
  { "field": "has_overdue_followup", "op": "equals", "value": true }
]
```

Multiple filters are combined with **AND** (no OR support in V1).

### 3.3 Supported Fields & Operators

| Field | Type | Available Operators |
|---|---|---|
| `status` | Scalar | `equals`, `not_equals`, `contains`, `in`, `not_in`, `is_empty`, `is_not_empty` |
| `source` | Scalar | same as above |
| `health_score` | Numeric | `equals`, `not_equals`, `greater_than`, `less_than`, `between`, `is_empty`, `is_not_empty` |
| `total_revenue` | Numeric | same as above |
| `total_paid` | Numeric | same as above |
| `invoice_count` | Numeric | same as above |
| `last_contact_at` | Date | `equals`, `not_equals`, `greater_than`, `less_than`, `between`, `is_empty`, `is_not_empty`, `last_30_days`, `last_90_days`, `last_year` |
| `last_payment_at` | Date | same as above |
| `created_at` | Date | same as above |
| `tag_ids` | Tags | `in`, `not_in`, `is_empty`, `is_not_empty`, `all_of` |
| `has_overdue_followup` | Boolean | `equals` (true/false) |
| `search` | Text | `contains` (searches name, email, company, phone) |

> **`tag_ids.all_of`:** Client must carry **every** tag in the array (one `whereHas` per tag). Use sparingly — generates N subqueries.

> **`has_overdue_followup`:** Checks `client_follow_ups` for rows where `status = 'pending'` AND `due_at < now()`.

### 3.4 Public API

```php
// Build query from a SavedSegment model
$query = $engine->evaluate(SavedSegment $segment): Builder;

// Build query from raw filter array (used for live preview, no saving)
$query = $engine->evaluateFilters(int $userId, array $filters): Builder;

// Update client_count on all dynamic segments for a user
$updated = $engine->refreshCountsForUser(int $userId): int;
```

Both `evaluate()` and `evaluateFilters()` scope results to `user_id` and `is_archived = false` automatically.

### 3.5 SavedSegment Model

Table: `saved_segments`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `user_id` | bigint | FK → users |
| `name` | varchar(80) | User-given name |
| `filters` | JSON | Array of filter objects |
| `is_pinned` | boolean | Pinned segments sort first |
| `is_dynamic` | boolean | If true, `client_count` is refreshed periodically |
| `client_count` | int | Denormalized count, updated by `refreshCountsForUser()` |
| `last_executed_at` | timestamp | Set on each `refreshCountsForUser()` call |

> **M-07 from CLIENTS-CRM-SPEC-V2.md:** `filters` JSON has no schema validation before saving. Invalid filters cause runtime exceptions in `ClientSegmentEngine`. **Fix pending** — add `validateFilterSchema()` in `SaveSegmentAction` before next sprint.

---

## 4. Routes & Controllers

### 4.1 Route Definitions

File: `routes/crm.php` — under `middleware(['auth', 'active.account'])`, prefix `clients`, name `clients.`

```
Prefix: clients/segments     Name prefix: clients.segments.
```

| Method | URI | Controller Method | Route Name |
|---|---|---|---|
| GET | `/clients/segments` | `ClientSegmentController@index` | `clients.segments.index` |
| POST | `/clients/segments` | `ClientSegmentController@store` | `clients.segments.store` |
| POST | `/clients/segments/preview` | `ClientSegmentController@preview` | `clients.segments.preview` |
| POST | `/clients/segments/{segment}/execute` | `ClientSegmentController@execute` | `clients.segments.execute` |
| PATCH | `/clients/segments/{segment}/pin` | `ClientSegmentController@pin` | `clients.segments.pin` |
| DELETE | `/clients/segments/{segment}` | `ClientSegmentController@destroy` | `clients.segments.destroy` |
| POST | `/clients/segments/recalculate-health` | `ClientSegmentController@recalculateHealth` | `clients.segments.recalculate-health` |

### 4.2 `recalculateHealth` Endpoint

**Added:** June 2026 (replaces artisan command requirement for end users)

```
POST /clients/segments/recalculate-health
Auth: required (active.account middleware)
Response: JSON { message: string, processed: int }
```

- Calls `ClientHealthScoreService::recalculateForUser($request->user()->id)`
- Authorization: `viewAny` on `Client::class`
- No request body required
- Used by the "احسب المؤشرات الآن" button in `crm/segments/index.blade.php`

### 4.3 `index` View Data

`ClientSegmentController@index` passes the following to `crm.segments.index`:

| Variable | Source | Description |
|---|---|---|
| `$segments` | `saved_segments` WHERE `user_id` | All saved segments for the user |
| `$distribution` | `clients` aggregate query | Count by grade (excellent/good/fair/poor/total) + avg_score |
| `$worstClients` | `clients` WHERE `health_score < 40` | Up to 10 lowest-scoring clients |
| `$bestClients` | `clients` WHERE `health_score >= 80` | Up to 10 highest-scoring clients |
| `$totalClients` | `clients` count | All non-archived clients |
| `$withoutScore` | `$totalClients - $distribution->total` | Clients with no health score yet |
| `$tags` | `client_tags` | For filter builder dropdown |
| `$statuses` | `ClientStatus::cases()` | Enum values |
| `$sources` | `ClientSource::cases()` | Enum values |

---

## 5. Commands & Scheduler

### 5.1 `crm:recalculate-health-scores`

File: `app/Console/Commands/RecalculateHealthScoresCommand.php`

```bash
# All users
php artisan crm:recalculate-health-scores

# Single user
php artisan crm:recalculate-health-scores --user=5

# With automatic tag application (applies high-confidence SmartTag suggestions)
php artisan crm:recalculate-health-scores --apply-tags

# Custom chunk size (default 200)
php artisan crm:recalculate-health-scores --chunk=100
```

**Scope:** Only processes users with `status = UserStatus::Active`. Skips archived clients.

**Output:** Progress bar + summary table (clients processed, users, tags applied, duration).

**Logging:** `storage/logs/crm-health-scores.log` (via scheduler's `appendOutputTo`).

### 5.2 Scheduler

File: `routes/console.php`

```php
Schedule::command('crm:recalculate-health-scores --apply-tags')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/crm-health-scores.log'));
```

Runs nightly at 02:00. The `--apply-tags` flag triggers `SmartTagSuggestionService::applyAutoRules()` after each user's scores are computed.

> **Prerequisite:** Laravel scheduler must be running via cron: `* * * * * php artisan schedule:run`

---

## 6. Database

### 6.1 `clients` Table — Relevant Columns

| Column | Type | Description |
|---|---|---|
| `health_score` | `tinyint unsigned` (nullable) | Denormalized score (0–100). NULL = not yet computed |
| `last_contact_at` | `timestamp` (nullable) | Used by `contact_regularity` factor |
| `last_payment_at` | `timestamp` (nullable) | Used for recency calculations |
| `total_revenue` | `decimal(12,2)` | Aggregate: sum of all invoiced amounts |
| `total_paid` | `decimal(12,2)` | Aggregate: sum of all paid amounts |
| `invoice_count` | `int unsigned` | Aggregate: total invoice count |

### 6.2 `client_health_scores` Table

Stores historical snapshots of every score computation.

| Column | Type | Description |
|---|---|---|
| `id` | bigint | PK |
| `client_id` | bigint | FK → clients |
| `score` | tinyint unsigned | Score at time of computation |
| `factors` | JSON | Raw factor breakdown `{payment_rate, work_frequency, ...}` |
| `scored_at` | timestamp | When this snapshot was taken |
| `created_at` | timestamp | |

> **N-01 from CLIENTS-CRM-SPEC-V2.md:** `updated_at` column is missing. Not critical (snapshots are immutable), but add in next schema migration for consistency.

### 6.3 `saved_segments` Table

| Column | Type | Description |
|---|---|---|
| `id` | bigint | PK |
| `user_id` | bigint | FK → users |
| `name` | varchar(80) | Segment label |
| `filters` | JSON | Array of filter objects (see §3.2) |
| `is_pinned` | boolean | Pinned = sorts to top |
| `is_dynamic` | boolean | Whether count is refreshed automatically |
| `client_count` | int | Denormalized count |
| `last_executed_at` | timestamp | Last refresh timestamp |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 6.4 DB Queries in Health Score Computation

The service hits these tables per client:

| Table | Operation | Purpose |
|---|---|---|
| `transactions` JOIN `projects` | `SUM(amount)` | Recent paid / billed |
| `transactions` JOIN `projects` | `COUNT(*)` | Work frequency |
| `client_activities` | `COUNT(*)` | Contact regularity (historic) |
| `client_follow_ups` | `COUNT(*) + COUNT(status='completed')` | Response rate |
| `client_health_scores` | `INSERT` | Historical snapshot |
| `clients` | `UPDATE health_score` | Denormalized cache |

> **Performance note:** For a user with 200 clients and chunk size 200, the above runs ~200 × 7 = ~1,400 queries in one chunk. Acceptable for a background job. Do NOT run this on a web request for large datasets.

---

## 7. UI & Help Center

### 7.1 Segments Page (`/clients/segments`)

File: `resources/views/crm/segments/index.blade.php`

Tabs (Alpine.js `x-data="{ tab: 'segments' }"`):
- **الشرائح** — Saved segments list + Filter Builder
- **صحة العملاء** — Health score dashboard (distribution, best/worst clients)

**"احسب المؤشرات الآن" Button** (shown when `$withoutScore > 0`):
- Sends `POST /clients/segments/recalculate-health` via `fetch()`
- Shows spinner during processing
- On success: displays count of processed clients + "تحديث" link to reload page
- On error: shows Arabic error message inline
- No page reload until user explicitly clicks "تحديث"

### 7.2 Help Center (`/help`)

File: `resources/views/help/index.blade.php`

New tab added: **🎯 الشرائح وصحة العملاء** (`tab = 'crm'`)

Content sections:
1. ما هو مؤشر صحة العميل؟
2. درجات التقييم (4 colored cards: ممتاز / جيد / متوسط / ضعيف)
3. كيف تُحسَب الدرجة؟ (5 factors with weight badges)
4. ما هي الشرائح؟
5. أمثلة على شرائح مفيدة (5 practical examples)
6. كيف تستخدم الميزة؟ (4-step walkthrough)
7. الفلاتر المتاحة (field reference grid)
8. Tip: daily auto-computation note

---

## 8. Configuration Reference

File: `config/crm.php` → `health_score` key:

```php
'health_score' => [
    'weights' => [
        'payment_rate'       => 0.35,
        'work_frequency'     => 0.25,
        'revenue_value'      => 0.20,
        'contact_regularity' => 0.10,
        'response_rate'      => 0.10,
    ],
    'recency_bias' => [
        'recent_months'   => 3,
        'recent_weight'   => 0.70,
        'historic_months' => 12,
        'historic_weight' => 0.30,
    ],
],
```

**Constants in `ClientHealthScoreService` (not config-driven — requires code change):**

| Constant | Value | Meaning |
|---|---|---|
| `REVENUE_TOP` | 10,000.0 | Revenue at which `revenue_value` factor = 1.0 |
| `FREQ_TOP` | 12 | Transactions/year at which `work_frequency` factor = 1.0 |

> **Recommendation:** Move `REVENUE_TOP` and `FREQ_TOP` to `config/crm.php` to allow tenant-specific tuning without code changes.

---

## 9. Implementation Notes & Known Gaps

### Implemented ✅

| Item | Status |
|---|---|
| Health score algorithm V2 (recency bias) | ✅ Implemented |
| Batch recalculation command | ✅ Implemented |
| Daily scheduler | ✅ Implemented |
| Segment engine (10 field types, 10+ operators) | ✅ Implemented |
| Saved segments CRUD | ✅ Implemented |
| Health dashboard in segments page | ✅ Implemented |
| In-app recalculate button (no CLI needed) | ✅ Implemented — June 2026 |
| Help center documentation | ✅ Implemented — June 2026 |

### Gaps & Pending Work ⚠️

| Gap | Ref | Priority |
|---|---|---|
| `saved_segments.filters` JSON has no schema validation before save | M-07 | High |
| `REVENUE_TOP` and `FREQ_TOP` are hardcoded constants, not config | — | Medium |
| `client_health_scores` missing `updated_at` column | N-01 | Low |
| No score trend tracking (previous_score, trend delta) | V2 spec §4.6 | Medium |
| `refreshCountsForUser()` not called on schedule — segment counts go stale | — | Medium |
| Filter builder OR logic not supported (all filters are AND) | — | Future |
| `tag_ids.all_of` generates N subqueries — no performance cap | — | Low |
| Health score not auto-recalculated when a new payment is recorded | — | Medium |

### Architectural Note: Health Score Recalculation Trigger

Currently, scores are only recomputed:
1. Nightly via scheduler
2. Manually via the "احسب المؤشرات الآن" button (or artisan command)

**Missing:** Event-driven recalculation when a payment is recorded or an invoice is marked paid. Per `CLIENTS-CRM-SPEC-V2.md §1.2` (C-02), the correct approach is:

```php
// After InvoicePaidEvent (afterCommit listener):
// 1. Atomic aggregate update (total_paid, last_payment_at)
// 2. Dispatch a queued job: RecalculateClientHealthScoreJob
// This gives near-real-time score updates without blocking the request
```

This is not yet implemented. Current behavior: score updates the following night.

---

*📁 Document: `docs/CRM-HEALTH-SEGMENTS.md`*  
*🏢 Workuflow — Financial & Business SaaS Platform*  
*🔗 Related: `CLIENTS-CRM-SPEC-V2.md`, `CLIENTS.md`*
