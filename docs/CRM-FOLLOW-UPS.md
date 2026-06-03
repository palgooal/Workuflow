# Workuflow — CRM: Follow-Ups Module
## Implementation Reference — V1.0

> **Document Type:** Implementation Documentation
> **Module:** Client Follow-Ups (المتابعات)
> **Sprint:** Sprint 6
> **Status:** Implemented & Production-Ready
> **Last Updated:** June 2026
> **Related Docs:** `CLIENTS-CRM-SPEC-V2.md`, `CRM-HEALTH-SEGMENTS.md`

---

## Table of Contents

1. [Overview](#1-overview)
2. [Data Model](#2-data-model)
3. [Enums](#3-enums)
4. [DTO & Validation](#4-dto--validation)
5. [Actions](#5-actions)
6. [Service Layer](#6-service-layer)
7. [Controller & Routes](#7-controller--routes)
8. [Views & UI](#8-views--ui)
9. [Known Bug Fix — Blade @show Conflict](#9-known-bug-fix--blade-show-conflict)

---

## 1. Overview

The Follow-Ups module enables users to schedule, track, and complete client follow-up tasks from a dedicated dashboard at `/clients/follow-ups`. Follow-ups are organized into three columns: overdue, today, and this week.

**File map:**

| File | Purpose |
|---|---|
| `app/Modules/CRM/Models/ClientFollowUp.php` | Eloquent model |
| `app/Modules/CRM/Enums/FollowUpStatus.php` | Status enum |
| `app/Modules/CRM/DTOs/CreateFollowUpDTO.php` | Input DTO |
| `app/Modules/CRM/Requests/StoreFollowUpRequest.php` | Form Request |
| `app/Modules/CRM/Actions/FollowUp/CreateFollowUpAction.php` | Create action |
| `app/Modules/CRM/Actions/FollowUp/CompleteFollowUpAction.php` | Complete action |
| `app/Modules/CRM/Actions/FollowUp/CancelFollowUpAction.php` | Cancel action |
| `app/Modules/CRM/Services/FollowUpService.php` | Service facade |
| `app/Modules/CRM/Http/Controllers/ClientFollowUpController.php` | Controller |
| `resources/views/crm/follow-ups/index.blade.php` | Dashboard view |
| `resources/views/components/crm-follow-up-card.blade.php` | Card component |

---

## 2. Data Model

Table: `client_follow_ups`

| Column | Type | Notes |
|---|---|---|
| `id` | `char(26)` ULID | PK — uses `HasUlids` trait |
| `client_id` | `bigint` | FK → clients |
| `user_id` | `bigint` | FK → users (owner) |
| `type` | `varchar` | `call`, `email`, `meeting`, `task`, `other` |
| `title` | `varchar(200)` | Required |
| `status` | `varchar(20)` | Cast to `FollowUpStatus` enum |
| `due_at` | `datetime` | Scheduled datetime |
| `completed_at` | `datetime` (nullable) | Set on completion |
| `reminder_at` | `datetime` (nullable) | Must be before `due_at` |
| `priority` | `integer` | 1 (high) – 5 (low). UI uses 1–3 |
| `notes` | `text` (nullable) | Optional details |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**Model casts:**

```php
'status'       => FollowUpStatus::class,
'due_at'       => 'datetime',
'completed_at' => 'datetime',
'reminder_at'  => 'datetime',
'priority'     => 'integer',
```

**Relations:**

```php
client()   → BelongsTo(Client::class)
assignee() → BelongsTo(User::class, 'user_id')
```

**Key model methods:**

| Method | Returns | Description |
|---|---|---|
| `isOverdue(): bool` | bool | `status === Pending && due_at->isPast()` |
| `actualStatus(): FollowUpStatus` | enum | Resolves 'pending' → 'overdue' if past due |
| `daysUntilDue(): int` | int | Negative = past due |

**Scopes:**

| Scope | Filters |
|---|---|
| `scopePending` | `status = pending` |
| `scopeOverdue` | `status = pending AND due_at < now()` |
| `scopeDueToday` | `status = pending AND date(due_at) = today` |
| `scopeDueThisWeek` | `status = pending AND due_at BETWEEN now AND endOfWeek` |
| `scopeWithReminder` | `reminder_at <= now AND status = pending` |

---

## 3. Enums

File: `app/Modules/CRM/Enums/FollowUpStatus.php`

| Case | Value | Terminal? |
|---|---|---|
| `Pending` | `'pending'` | No |
| `Overdue` | `'overdue'` | No |
| `Completed` | `'completed'` | Yes |
| `Cancelled` | `'cancelled'` | Yes |

**Key method:** `resolveActual(self $stored, Carbon $dueAt): self`
Converts `Pending` → `Overdue` at runtime if `due_at` is in the past. Does NOT write to DB — used only for display.

> **Note:** The DB can store `overdue` as an actual status (set by `FollowUpService::markOverdue()`), but `actualStatus()` resolves it dynamically in the model without a DB write.

---

## 4. DTO & Validation

### `CreateFollowUpDTO`

```php
final readonly class CreateFollowUpDTO
{
    userId, clientId, title, dueAt,
    notes = null, priority = 3, reminderAt = null
}
```

`toArray()` explicitly includes `'status' => 'pending'` so all new follow-ups start as pending.

### `StoreFollowUpRequest` validation rules

| Field | Rules |
|---|---|
| `client_id` | required, integer, exists in `clients` WHERE `user_id = auth()->id()` |
| `title` | required, string, max:200 |
| `notes` | nullable, string, max:2000 |
| `due_at` | required, date, after:now |
| `priority` | nullable, integer, min:1, max:5 |
| `reminder_at` | nullable, date, before:due_at |

> **Important:** `client_id` must be in the POST body for `storeGeneral()` (quick-add modal). For `store()` via client profile page, the client is resolved from the URL parameter and `client_id` is still required in the form body.

---

## 5. Actions

All actions wrap their DB operations in `DB::transaction()` and log to `client_activities` directly (pending Event-layer refactor in Sprint 6+).

### `CreateFollowUpAction`

1. `ClientFollowUp::create($dto->toArray())`
2. Creates `ClientActivity` record with type `FollowUpCreated`

### `CompleteFollowUpAction`

1. Guard: if already `Completed`, returns early
2. Updates `status = completed`, `completed_at = now()`
3. Updates `clients.last_contact_at = now()`
4. Creates `ClientActivity` with type `FollowUpCompleted`

### `CancelFollowUpAction`

Follows same pattern as Complete: updates status to `cancelled`, logs activity.

> **Pending (Sprint 6):** Actions should dispatch Events post-commit with `$afterCommit = true` instead of writing activity directly inside the transaction. See `CLIENTS-CRM-SPEC-V2.md §1.1`.

---

## 6. Service Layer

File: `app/Modules/CRM/Services/FollowUpService.php`

Public API:

| Method | Description |
|---|---|
| `create(CreateFollowUpDTO): ClientFollowUp` | Delegates to `CreateFollowUpAction` |
| `complete(followUp, actorId, notes): ClientFollowUp` | Delegates to `CompleteFollowUpAction` |
| `cancel(followUp, actorId): ClientFollowUp` | Delegates to `CancelFollowUpAction` |
| `upcoming(userId): Collection` | Pending+Overdue due within 7 days, with `actual_status` attribute |
| `forClient(Client, pendingOnly): Collection` | All follow-ups for a client |
| `markOverdue(): int` | Bulk-updates `pending` → `overdue` where `due_at < now()` |
| `pendingCount(userId): int` | Count for dashboard badge |
| `dueForReminder(): Collection` | Follow-ups with `reminder_at` within the last hour |

`dueForReminder()` uses a **1-hour window** (`reminder_at BETWEEN now()-1h AND now()`) to avoid sending duplicate reminders across repeated Scheduler runs.

---

## 7. Controller & Routes

File: `app/Modules/CRM/Http/Controllers/ClientFollowUpController.php`

### Route table

All routes are under `middleware(['auth', 'active.account'])`, prefix `clients`, name `clients.`.

**General follow-up routes** (prefix `follow-ups`, name `follow-ups.`):

| Method | URI | Controller | Route Name |
|---|---|---|---|
| GET | `/clients/follow-ups` | `index` | `clients.follow-ups.index` |
| GET | `/clients/follow-ups/upcoming` | `upcoming` | `clients.follow-ups.upcoming` |
| POST | `/clients/follow-ups/quick` | `storeGeneral` | `clients.follow-ups.quick-store` |

**Per-client follow-up routes** (prefix `/{client}/follow-ups`, name `client-follow-ups.`):

| Method | URI | Controller | Route Name |
|---|---|---|---|
| POST | `/clients/{client}/follow-ups` | `store` | `clients.client-follow-ups.store` |
| POST | `/clients/{client}/follow-ups/{followUp}/complete` | `complete` | `clients.client-follow-ups.complete` |
| POST | `/clients/{client}/follow-ups/{followUp}/cancel` | `cancel` | `clients.client-follow-ups.cancel` |

### `index()` — view data

| Variable | Source |
|---|---|
| `$overdue` | `status IN (pending, overdue) AND due_at < startOfDay()` |
| `$today` | `status IN (pending, overdue) AND date(due_at) = today` |
| `$thisWeek` | `status IN (pending, overdue) AND due_at BETWEEN tomorrow AND endOfWeek` |
| `$clients` | All non-archived clients (for quick-add modal dropdown) |

Both `$overdue` and `$today` can contain the same record if `due_at` was yesterday and today is the same calendar day — this is a minor edge case but not harmful.

### `storeGeneral()` vs `store()`

- `storeGeneral()`: Used by the quick-add modal on `/clients/follow-ups`. Always returns JSON. Resolves client from `client_id` in request body.
- `store()`: Used from client profile page. Returns JSON if `wantsJson()`, else redirects to `clients.show` with `#followups` fragment.

### Authorization

All actions use `$this->authorize('viewAny', Client::class)` or `$this->authorize('update', $client)`. The `ensureFollowUpBelongsToClient()` helper prevents IDOR (cross-client access).

---

## 8. Views & UI

### Dashboard: `crm/follow-ups/index.blade.php`

Uses `@extends('layouts.app')` with sections `title`, `breadcrumb`, `content`.

Three-column kanban layout:
- **متأخرة** (red): `$overdue` collection
- **اليوم** (amber): `$today` collection
- **هذا الأسبوع** (blue): `$thisWeek` collection

Each card is rendered via `<x-crm-follow-up-card :follow-up="$followUp" color="red|amber|blue" />`.

Quick-add modal is Alpine.js-driven (`addFollowUpModal()`), POSTs to `clients.follow-ups.quick-store` as JSON, then reloads the page.

### Card Component: `components/crm-follow-up-card.blade.php`

Props: `$followUp` (ClientFollowUp model with `client` relation), `$color`

Actions (via `fetch()` in Alpine.js):
- **إتمام** → `POST /clients/{clientPublicId}/follow-ups/{id}/complete`
- **إلغاء** → `POST /clients/{clientPublicId}/follow-ups/{id}/cancel`
- **تأجيل** → dispatches `open-add-modal` event to re-open quick-add modal

On complete/cancel: card shows done state inline (no page reload needed).

---

## 9. Known Bug Fix — Blade `@show` Conflict

**Bug discovered:** June 2026

**Symptom:** `/clients/follow-ups` rendered completely empty (no content area shown).

**Root cause — two layers:**

**Layer 1:** The original view used `<x-app-layout>` (a Blade component that does not exist in this project). Since the component is unknown, Blade silently renders nothing. All content inside `<x-app-layout>` was swallowed.

**Layer 2 (after fix attempt):** After converting to `@extends('layouts.app')`, a new error appeared:
```
InvalidArgumentException: Cannot end a section without first starting one.
resources/views/crm/follow-ups/index.blade.php:302
```

The Alpine.js attribute `@show-toast.window="..."` was being parsed by Blade's directive compiler as `@show` — a **built-in Blade directive** that calls `$__env->yieldSection()`, which terminates and outputs the current section. This silently closed `@section('content')` mid-template, leaving the final `@endsection` on line 302 with no open section to close.

**Fix:**
```blade
{{-- BEFORE (broken) --}}
<div @show-toast.window="show = true; msg = $event.detail.msg; ...">

{{-- AFTER (correct) --}}
<div x-on:show-toast.window="show = true; msg = $event.detail.msg; ...">
```

**Rule — applies to all Blade views in this project:**

> Any Alpine.js `@event` shorthand where the event name starts with a word that matches a Blade directive **MUST** use `x-on:event` instead. Known Blade directives to avoid as Alpine event names:
>
> | Blade directive | Would conflict with Alpine |
> |---|---|
> | `@show` | `@show-toast`, `@show-modal`, etc. |
> | `@open` | safe (not a Blade directive) |
> | `@click` | safe (not a Blade directive) |
> | `@submit` | safe (not a Blade directive) |
> | `@keydown` | safe (not a Blade directive) |
>
> The segments view (`crm/segments/index.blade.php`) already uses `x-on:show-toast.window` for this exact reason.

**Blade directives that could conflict with Alpine event names (reference):**
`@if`, `@else`, `@elseif`, `@unless`, `@for`, `@foreach`, `@forelse`, `@while`, `@switch`, `@case`, `@break`, `@continue`, `@include`, `@extends`, `@section`, `@yield`, `@show`, `@stop`, `@append`, `@prepend`, `@push`, `@stack`, `@once`, `@php`, `@verbatim`, `@csrf`, `@method`, `@error`, `@auth`, `@guest`, `@can`, `@cannot`, `@env`, `@production`, `@hasSection`, `@inject`, `@json`

---

*Document: `docs/CRM-FOLLOW-UPS.md`*
*Platform: Workuflow — Financial & Business SaaS*
*Related: `CLIENTS-CRM-SPEC-V2.md`, `CRM-HEALTH-SEGMENTS.md`, `CLIENTS.md`*
