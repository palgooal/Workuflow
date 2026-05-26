# دراهم — CRM Module
## Architecture Review & Hardened Specification — V2.0

> **Document Type:** Senior Architect Review + Hardened Engineering Specification  
> **Module:** Client Relationship Management (CRM)  
> **Platform:** دراهم — Financial & Business Management SaaS  
> **Version:** 2.0.0 (Hardened from V1.0)  
> **Status:** ✅ Implementation-Ready — Pre-Engineering Sign-off  
> **Reviewer Role:** Enterprise SaaS Architect / CTO-level  
> **Last Updated:** May 2026  
> **Prerequisite:** Read `CLIENTS-CRM-SPEC.md` (V1) first

---

## Architecture Review Summary

> This document is not a rewrite. It is a **senior architect's review pass** over V1.
> Every section below identifies findings from V1 and provides hardened replacements.

### Critical Findings (must fix before engineering begins)

| # | Finding | Risk | Section |
|---|---------|------|---------|
| C-01 | Observer pattern fires inside DB transactions — activity logging can fail silently and roll back parent transaction | 🔴 Data Integrity | §5 |
| C-02 | Concurrent invoice payments may corrupt denormalized aggregates due to race conditions | 🔴 Data Corruption | §6, §13 |
| C-03 | `ENUM` column types will cause painful zero-downtime migration issues at scale | 🔴 Operational | §6 |
| C-04 | Portal token brute-force attack surface not addressed | 🔴 Security | §10 |
| C-05 | `client_activities` table has no partitioning/archiving strategy — will become a billion-row table | 🔴 Scalability | §6 |
| C-06 | Import Job has no idempotency key — double-submit creates duplicate clients | 🔴 Data Integrity | §14 |

### Major Findings (fix before Phase 2)

| # | Finding | Risk |
|---|---------|------|
| M-01 | No DTO layer — data contracts between layers are loose strings/arrays | 🟠 Maintainability |
| M-02 | No cursor-based pagination — offset pagination breaks on large datasets | 🟠 Performance |
| M-03 | FULLTEXT index on `(name, company_name, email, phone)` with `user_id` scope = full-table scan for each tenant | 🟠 Performance |
| M-04 | Health Score TINYINT for `avg_payment_days` — misleading; TINYINT max=255 is fine but semantics are wrong | 🟠 Correctness |
| M-05 | Automation Rules Engine is synchronous — will block request threads for large tenants | 🟠 Performance |
| M-06 | No API error contract defined — clients can't build reliably against undefined error shapes | 🟠 API Quality |
| M-07 | `saved_segments.filters` JSON has no schema validation — corrupt filters will cause runtime crashes | 🟠 Stability |

### Minor Findings (fix before V1 launch)

| # | Finding |
|---|---------|
| N-01 | Missing `updated_at` on `client_health_scores` |
| N-02 | `client_tag_assignments` name breaks Laravel pivot convention (should be `client_tag` or use `->using()`) |
| N-03 | Signed URL expiry for attachment download set at 10 min — too short for slow connections on large files |
| N-04 | `DELETE /api/v1/clients/{ulid}` uses hard ID in route — should consistently use ULID |
| N-05 | Import preview endpoint accepts file upload but has no max-size enforcement defined |

---

## Table of Contents

1. [Enterprise Architecture Review](#1-enterprise-architecture-review)
2. [Domain-Driven Design Boundaries](#2-domain-driven-design-boundaries)
3. [Laravel Architecture Hardening](#3-laravel-architecture-hardening)
4. [Database Engineering Review](#4-database-engineering-review)
5. [API Engineering Review](#5-api-engineering-review)
6. [Frontend & UX System](#6-frontend--ux-system)
7. [Security Audit](#7-security-audit)
8. [SaaS Monetization Strategy](#8-saas-monetization-strategy)
9. [AI & Intelligence Layer](#9-ai--intelligence-layer)
10. [Engineering Execution Readiness](#10-engineering-execution-readiness)
11. [Architectural Decision Records (ADRs)](#11-architectural-decision-records)

---

## 1. Enterprise Architecture Review

### 1.1 Critical Fix: Observer Pattern & Transaction Safety

**Finding C-01 — Severity: Critical**

V1 proposes auto-logging via Observers:
```php
// V1 — DANGEROUS
class InvoiceObserver {
    public function updated(Invoice $invoice): void {
        if ($invoice->status === 'paid') {
            LogClientActivityAction::run(...); // This runs INSIDE the parent transaction
        }
    }
}
```

**Problem:** If the `LogClientActivityAction` throws, it rolls back the entire invoice update. If the invoice update throws after the activity log, the activity is orphaned. Either way: data inconsistency.

**Hardened Solution — Defer to Post-Transaction via Event:**

```php
// app/Observers/InvoiceObserver.php — V2
class InvoiceObserver
{
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
            // Fire event — listener runs AFTER transaction commits
            // Using: $dispatchesEvents OR afterCommit dispatcher
            event(new InvoicePaidEvent($invoice));
        }
    }
}

// app/Listeners/Clients/LogInvoicePaidActivity.php
class LogInvoicePaidActivity implements ShouldQueue
{
    public $afterCommit = true;  // ← KEY: Laravel 8+ — listener runs after DB commit
    public $queue = 'crm-default';

    public function handle(InvoicePaidEvent $event): void
    {
        // Safe — parent transaction already committed
        LogClientActivityAction::run($event->invoice->client, 'invoice_paid', [...]);
    }
}
```

**Rule:** All activity logging MUST use `$afterCommit = true` on listeners. Never log inside Observers directly.

---

### 1.2 Critical Fix: Race Condition in Aggregate Updates

**Finding C-02 — Severity: Critical**

V1 proposes updating client financial aggregates on payment events:
```php
// V1 — RACE CONDITION RISK
DB::statement("UPDATE clients SET total_paid = (SELECT SUM...) WHERE id = ?");
```

**Problem:** Two concurrent payments arrive within milliseconds → both read the same old `total_paid` → both add their amount → one update overwrites the other → final total is wrong.

**Hardened Solution — Use Atomic Increment + Scheduled Full Recalculation:**

```php
// app/Listeners/Clients/UpdateClientAggregatesOnPayment.php
class UpdateClientAggregatesOnPayment implements ShouldQueue
{
    public $afterCommit = true;

    public function handle(InvoicePaidEvent $event): void
    {
        // ATOMIC increment — safe under concurrency
        Client::where('id', $event->invoice->client_id)->update([
            'total_paid'      => DB::raw("total_paid + {$event->invoice->total_paid}"),
            'last_payment_at' => now(),
        ]);

        // Invalidate health score cache
        Cache::tags(["client:{$event->invoice->client_id}"])->flush();
    }
}

// Scheduled full reconciliation — runs nightly at 03:00
// Corrects any drift that accumulated during the day
class ReconcileClientAggregatesCommand extends Command
{
    protected $signature = 'crm:reconcile-aggregates {--user-id=}';

    public function handle(): void
    {
        // Full recalculation from source-of-truth tables
        DB::statement("
            UPDATE clients c
            SET
                total_revenue     = COALESCE((SELECT SUM(total_amount) FROM invoices WHERE client_id = c.id AND deleted_at IS NULL), 0),
                total_paid        = COALESCE((SELECT SUM(total_paid) FROM invoices WHERE client_id = c.id AND deleted_at IS NULL), 0),
                total_outstanding = COALESCE((SELECT SUM(amount_due) FROM invoices WHERE client_id = c.id AND status != 'paid' AND deleted_at IS NULL), 0),
                invoice_count     = (SELECT COUNT(*) FROM invoices WHERE client_id = c.id AND deleted_at IS NULL),
                project_count     = (SELECT COUNT(*) FROM projects WHERE client_id = c.id AND deleted_at IS NULL)
            WHERE c.deleted_at IS NULL
        ");
    }
}
```

**Rule:** Never use subquery-based full recalculation on hot paths. Use atomic increments for real-time; full recalculation for nightly reconciliation.

---

### 1.3 Automation Rules Engine: Async Concern

**Finding M-05**

V1 suggests a synchronous Automation Rules Engine. This is fine for evaluation but the **actions** must be queued:

```php
// V2 — Evaluation is sync, actions are async
class AutomationRuleEngine
{
    public function evaluate(Client $client): void
    {
        $actions = collect($this->rules)
            ->flatMap(fn($ruleClass) => app($ruleClass)->evaluate($client))
            ->filter();

        foreach ($actions as $action) {
            // Actions are dispatched to queue — never executed inline
            dispatch($action)->onQueue('crm-automation');
        }
    }
}

// AutomationAction is a queueable Job
abstract class AutomationAction implements ShouldQueue
{
    public string $queue = 'crm-automation';
    public int $tries = 3;
    abstract public function handle(): void;
}
```

---

### 1.4 Caching: Tag-Based Cache Invalidation

V1 mentions basic cache keys. This is insufficient for a multi-model invalidation scenario.

```php
// Use cache tags for grouped invalidation (requires Redis/Memcached)

// STORE
Cache::tags(["user:{$userId}", "client:{$client->id}"])
     ->remember("client_profile:{$client->id}", 300, fn() => ...);

Cache::tags(["user:{$userId}", "segments"])
     ->remember("clients_list:{$userId}:" . md5(serialize($filters)), 900, fn() => ...);

// INVALIDATE on client update:
Cache::tags(["client:{$client->id}"])->flush();

// INVALIDATE on any client change for user (list views):
Cache::tags(["user:{$userId}"])->flush();
```

**Warning:** Cache tags require a non-file cache driver. Ensure Redis is configured in production. Document this as a hard infrastructure requirement.

---

### 1.5 Service Layer Boundary Refinement

V1 service definitions are good but lack clear **input/output contracts**:

```
V1:  ClientService::create(array $data): Client       ← loose
V2:  ClientService::create(CreateClientDTO $dto): ClientResource ← strict
```

All public service methods must accept **DTOs** and return **API Resources or primitive values**. No raw `array` passing between layers.

---

## 2. Domain-Driven Design Boundaries

### 2.1 Bounded Contexts Map

```
┌─────────────────────────────────────────────────────────────────┐
│                        دراهم Platform                           │
│                                                                  │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────────┐    │
│  │   CRM        │   │   Billing    │   │  Communication   │    │
│  │              │◄──┤              │◄──┤                  │    │
│  │  Client      │   │  Invoice     │   │  Email/WhatsApp  │    │
│  │  Tag         │   │  Payment     │   │  Reminder        │    │
│  │  Activity    │   │  Subscription│   │  Follow-up       │    │
│  │  HealthScore │   │              │   │                  │    │
│  └──────┬───────┘   └──────┬───────┘   └─────────┬────────┘    │
│         │                  │                     │              │
│  ┌──────▼───────┐   ┌──────▼───────┐   ┌─────────▼────────┐    │
│  │  Automation  │   │   Portal     │   │   Analytics      │    │
│  │              │   │              │   │                  │    │
│  │  Rules       │   │  Token       │   │  Segmentation    │    │
│  │  Triggers    │   │  ClientView  │   │  HealthEngine    │    │
│  │  Actions     │   │  Payment     │   │  Reporting       │    │
│  └──────────────┘   └──────────────┘   └──────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Context Boundaries & Anti-Corruption Rules

| Context | Owns | Must NOT | Communicates via |
|---------|------|----------|-----------------|
| **CRM** | Client, Tag, Activity, HealthScore | Query Invoice table directly | Events from Billing |
| **Billing** | Invoice, Payment, Subscription | Manage client contact data | Events → CRM Listeners |
| **Communication** | Template, Message, Channel | Know invoice structure | Events from CRM + Billing |
| **Automation** | Rule, Trigger, Action | Execute business logic | Dispatches Actions as Jobs |
| **Portal** | Token, PortalSession | Store client PII | Read-only via ClientPortalDTO |
| **Analytics** | Segment, SavedFilter, Score | Mutate client data | Read from CRM aggregate tables |

### 2.3 Context Communication Rules

```
ALLOWED cross-context communication:
  Billing  → CRM:       via Events (InvoicePaid, InvoiceCreated, InvoiceOverdue)
  CRM      → Analytics: via shared read model (clients table aggregates)
  CRM      → Automation: via Events (ClientBecameInactive, TagAssigned)
  Automation → Communication: via Events (FollowUpDue, ReminderRequired)

FORBIDDEN:
  CRM Service importing Billing\InvoiceService (direct dependency)
  Analytics querying invoices table directly (bypass CRM aggregate)
  Portal reading from clients table without ClientPortalDTO (PII exposure)
```

### 2.4 Aggregate Root Definition

```php
// The Client is the Aggregate Root of the CRM context.
// All mutations to tags, notes, activities must go through Client.
// Nothing outside CRM context mutates the Client entity directly.

class Client extends Model  // Aggregate Root
{
    // Entities within aggregate:
    // - ClientTag (via pivot)
    // - ClientActivity
    // - ClientFollowUp
    // - ClientAttachment

    // Value Objects:
    // - ClientHealthScore (immutable snapshot)
    // - ClientFinancials (computed view)
    // - ContactInfo (email + phone + website — validated on set)
}
```

---

## 3. Laravel Architecture Hardening

### 3.1 DTO Layer (Required — V1 Missing)

```php
// app/DTO/Clients/CreateClientDTO.php
final readonly class CreateClientDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly string  $name,
        public readonly ?string $companyName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $website,
        public readonly ?string $countryCode,
        public readonly ?string $city,
        public readonly string  $currency = 'SAR',
        public readonly string  $source   = 'manual',
    ) {}

    public static function fromRequest(StoreClientRequest $request): self
    {
        return new self(
            userId:      auth()->id(),
            name:        $request->validated('name'),
            companyName: $request->validated('company_name'),
            email:       $request->validated('email'),
            phone:       $request->validated('phone'),
            website:     $request->validated('website'),
            countryCode: $request->validated('country_code'),
            city:        $request->validated('city'),
            currency:    $request->validated('currency', 'SAR'),
        );
    }

    public static function fromImportRow(array $row, int $userId): self
    {
        return new self(
            userId:  $userId,
            name:    $row['name'],
            email:   $row['email'] ?? null,
            phone:   $row['phone'] ?? null,
            source:  'import',
        );
    }
}

// app/DTO/Clients/UpdateClientDTO.php — similar pattern
// app/DTO/Clients/ClientFiltersDTO.php — for segmentation queries
final readonly class ClientFiltersDTO
{
    public function __construct(
        public readonly ?array  $tags        = null,
        public readonly ?string $status      = null,
        public readonly ?int    $healthMin    = null,
        public readonly ?int    $healthMax    = null,
        public readonly ?int    $lastActiveMaxDays = null,
        public readonly ?float  $revenueMin   = null,
        public readonly ?string $sort         = 'last_activity_at',
        public readonly string  $sortDir      = 'desc',
        public readonly int     $perPage      = 25,
        public readonly ?string $cursor       = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            tags:              $request->validated('filters.tags'),
            status:            $request->validated('filters.status'),
            healthMin:         $request->validated('filters.health_score.min'),
            healthMax:         $request->validated('filters.health_score.max'),
            lastActiveMaxDays: $request->validated('filters.last_activity.max_days'),
            revenueMin:        $request->validated('filters.revenue.min'),
            sort:              $request->validated('sort', 'last_activity_at'),
            sortDir:           $request->validated('sort_dir', 'desc'),
            perPage:           min($request->validated('per_page', 25), 100),
            cursor:            $request->validated('cursor'),
        );
    }
}
```

### 3.2 Enums (Replace All String Constants)

```php
// app/Enums/Clients/ClientStatus.php
enum ClientStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Prospect = 'prospect';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'نشط',
            self::Inactive => 'غير نشط',
            self::Prospect => 'محتمل',
            self::Archived => 'مؤرشف',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active   => '#2DCEA8',
            self::Inactive => '#6B7280',
            self::Prospect => '#F59E0B',
            self::Archived => '#94A3B8',
        };
    }
}

// app/Enums/Clients/ActivityType.php
enum ActivityType: string
{
    case ClientCreated          = 'client_created';
    case ClientUpdated          = 'client_updated';
    case TagAssigned            = 'tag_assigned';
    case TagRemoved             = 'tag_removed';
    case InvoiceCreated         = 'invoice_created';
    case InvoiceSent            = 'invoice_sent';
    case InvoicePaid            = 'invoice_paid';
    case InvoiceOverdue         = 'invoice_overdue';
    case InvoiceCancelled       = 'invoice_cancelled';
    case ProjectStarted         = 'project_started';
    case ProjectCompleted       = 'project_completed';
    case NoteAdded              = 'note_added';
    case FileAttached           = 'file_attached';
    case ReminderSent           = 'reminder_sent';
    case FollowUpCreated        = 'follow_up_created';
    case FollowUpCompleted      = 'follow_up_completed';
    case PortalViewed           = 'portal_viewed';
    case PortalInvoiceDownloaded = 'portal_invoice_downloaded';
    case Custom                 = 'custom';

    public function isUserCreatable(): bool
    {
        return in_array($this, [self::NoteAdded, self::Custom]);
    }

    public function isDeletable(): bool
    {
        return in_array($this, [self::NoteAdded, self::Custom]);
    }
}

// app/Enums/Clients/ClientSource.php
enum ClientSource: string
{
    case Manual   = 'manual';
    case Import   = 'import';
    case Referral = 'referral';
    case Portal   = 'portal';
    case Api      = 'api';
}

// app/Enums/Clients/FollowUpStatus.php
enum FollowUpStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Snoozed   = 'snoozed';
    case Cancelled = 'cancelled';
}

// app/Enums/Portal/PortalPermission.php
enum PortalPermission: string
{
    case InvoicesView         = 'invoices.view';
    case InvoicesDownload     = 'invoices.download';
    case ProjectsView         = 'projects.view';
    case PaymentsInitiate     = 'payments.initiate';
}
```

### 3.3 Model Casts (Replace Raw DB Types)

```php
// app/Models/Client.php
class Client extends Model
{
    use SoftDeletes, HasUlid;

    protected $casts = [
        'status'            => ClientStatus::class,
        'source'            => ClientSource::class,
        'total_revenue'     => 'decimal:2',
        'total_paid'        => 'decimal:2',
        'total_outstanding' => 'decimal:2',
        'health_score'      => 'integer',
        'is_archived'       => 'boolean',
        'last_activity_at'  => 'datetime',
        'last_invoice_at'   => 'datetime',
        'last_payment_at'   => 'datetime',
        'last_contacted_at' => 'datetime',
        'archived_at'       => 'datetime',
        'health_computed_at'=> 'datetime',
    ];
}

// app/Models/ClientActivity.php
class ClientActivity extends Model
{
    protected $casts = [
        'type'       => ActivityType::class,
        'metadata'   => 'array',
        'is_pinned'  => 'boolean',
        'is_private' => 'boolean',
    ];
}

// app/Models/ClientPortalToken.php
class ClientPortalToken extends Model
{
    protected $casts = [
        'permissions' => 'array',  // Cast to PortalPermission[] in accessor
        'expires_at'  => 'datetime',
        'revoked_at'  => 'datetime',
        'last_used_at'=> 'datetime',
    ];

    public function isValid(): bool
    {
        return is_null($this->revoked_at)
            && $this->expires_at->isFuture();
    }

    public function hasPermission(PortalPermission $permission): bool
    {
        return in_array($permission->value, $this->permissions ?? []);
    }
}
```

### 3.4 Form Requests

```php
// app/Http/Requests/Clients/StoreClientRequest.php
class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'email'        => ['nullable', 'email:rfc,dns', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'website'      => ['nullable', 'url', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2', Rule::in(Countries::codes())],
            'city'         => ['nullable', 'string', 'max:100'],
            'currency'     => ['nullable', 'string', Rule::in(['SAR', 'AED', 'EGP', 'JOD', 'KWD', 'USD'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // At least one contact method required
            if (empty($this->email) && empty($this->phone)) {
                $validator->errors()->add('contact', 'يجب إدخال الإيميل أو رقم الهاتف على الأقل.');
            }

            // Check plan limit
            $limit = config("features.max_clients.{$this->user()->plan}");
            if (Client::where('user_id', $this->user()->id)->count() >= $limit) {
                $validator->errors()->add('limit', "وصلت للحد الأقصى ({$limit} عملاء) على خطتك الحالية.");
            }
        });
    }
}

// app/Http/Requests/Clients/ClientListRequest.php
class ClientListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filters.status'                   => ['nullable', Rule::enum(ClientStatus::class)],
            'filters.tags'                     => ['nullable', 'array'],
            'filters.tags.*'                   => ['integer', 'exists:client_tags,id'],
            'filters.health_score.min'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'filters.health_score.max'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'filters.last_activity.max_days'   => ['nullable', 'integer', 'min:1', 'max:3650'],
            'filters.revenue.min'              => ['nullable', 'numeric', 'min:0'],
            'sort'    => ['nullable', Rule::in(['name', 'health_score', 'last_activity_at', 'total_revenue', 'created_at'])],
            'sort_dir'=> ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'=> ['nullable', 'integer', 'min:10', 'max:100'],
            'cursor'  => ['nullable', 'string'],
            'q'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
```

### 3.5 Query Builder Class (Replaces scattered filter logic)

```php
// app/QueryBuilders/ClientQueryBuilder.php
class ClientQueryBuilder
{
    private Builder $query;

    public function __construct(private readonly int $userId)
    {
        $this->query = Client::query()
            ->where('user_id', $this->userId)
            ->where('is_archived', false);
    }

    public function applyFilters(ClientFiltersDTO $filters): self
    {
        if ($filters->status) {
            $this->query->where('status', $filters->status);
        }

        if ($filters->tags) {
            $this->query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('client_tags.id', $filters->tags);
            });
        }

        if ($filters->healthMin !== null) {
            $this->query->where('health_score', '>=', $filters->healthMin);
        }

        if ($filters->healthMax !== null) {
            $this->query->where('health_score', '<=', $filters->healthMax);
        }

        if ($filters->lastActiveMaxDays !== null) {
            $this->query->where('last_activity_at', '>=',
                now()->subDays($filters->lastActiveMaxDays)
            );
        }

        if ($filters->revenueMin !== null) {
            $this->query->where('total_revenue', '>=', $filters->revenueMin);
        }

        return $this;
    }

    public function applySearch(string $q): self
    {
        $this->query->whereFullText(
            ['name', 'company_name', 'email', 'phone'],
            $q,
            ['mode' => 'boolean']
        );
        return $this;
    }

    public function applySort(string $column, string $dir = 'desc'): self
    {
        $allowed = ['name', 'health_score', 'last_activity_at', 'total_revenue', 'created_at'];
        if (in_array($column, $allowed)) {
            $this->query->orderBy($column, $dir);
        }
        return $this;
    }

    public function withListRelations(): self
    {
        $this->query
            ->with(['tags:id,name,color,icon,slug'])
            ->select([
                'id', 'ulid', 'name', 'company_name', 'email', 'avatar_url',
                'status', 'health_score', 'last_activity_at',
                'total_revenue', 'total_outstanding', 'currency',
            ]);
        return $this;
    }

    public function cursorPaginate(int $perPage = 25): CursorPaginator
    {
        return $this->query->cursorPaginate($perPage);
    }

    public function get(): Collection
    {
        return $this->query->get();
    }
}
```

### 3.6 API Resources (Missing from V1)

```php
// app/Http/Resources/Clients/ClientListResource.php
class ClientListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->ulid,
            'name'           => $this->name,
            'company_name'   => $this->company_name,
            'email'          => $this->email,
            'avatar_url'     => $this->avatar_url,
            'status'         => $this->status,
            'health_score'   => $this->health_score,
            'health_label'   => $this->healthLabel(),
            'tags'           => ClientTagResource::collection($this->whenLoaded('tags')),
            'financials'     => [
                'total_revenue'     => $this->total_revenue,
                'total_outstanding' => $this->total_outstanding,
                'currency'          => $this->currency,
            ],
            'last_activity_at' => $this->last_activity_at?->diffForHumans(),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}

// app/Http/Resources/Clients/ClientProfileResource.php — full 360° profile
class ClientProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->ulid,
            'name'         => $this->name,
            'company_name' => $this->company_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'website'      => $this->website,
            'avatar_url'   => $this->avatar_url,
            'currency'     => $this->currency,
            'country_code' => $this->country_code,
            'city'         => $this->city,
            'status'       => $this->status,
            'source'       => $this->source,
            'tags'         => ClientTagResource::collection($this->whenLoaded('tags')),
            'health'       => new ClientHealthResource($this->whenLoaded('healthScore')),
            'financials'   => [
                'total_revenue'     => $this->total_revenue,
                'total_paid'        => $this->total_paid,
                'total_outstanding' => $this->total_outstanding,
                'invoice_count'     => $this->invoice_count,
                'project_count'     => $this->project_count,
                'avg_payment_days'  => $this->avg_payment_days,
                'currency'          => $this->currency,
            ],
            'engagement'    => [
                'last_activity_at'  => $this->last_activity_at?->toIso8601String(),
                'last_invoice_at'   => $this->last_invoice_at?->toIso8601String(),
                'last_payment_at'   => $this->last_payment_at?->toIso8601String(),
                'last_contacted_at' => $this->last_contacted_at?->toIso8601String(),
            ],
            'custom_fields' => $this->when(
                $this->relationLoaded('fieldValues'),
                fn() => ClientCustomFieldResource::collection($this->fieldValues)
            ),
            'referred_by'   => new ClientRefResource($this->whenLoaded('referredByClient')),
            'referrals_count' => $this->when(
                isset($this->referrals_count),
                $this->referrals_count
            ),
            'meta' => [
                'follow_up_count'   => $this->when(isset($this->pending_follow_ups_count), $this->pending_follow_ups_count),
                'attachment_count'  => $this->when(isset($this->attachments_count), $this->attachments_count),
                'portal_active'     => $this->when(isset($this->has_active_portal), $this->has_active_portal),
            ],
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### 3.7 Transaction Boundaries in Actions

```php
// app/Actions/Clients/CreateClientAction.php
class CreateClientAction
{
    public function handle(CreateClientDTO $dto): Client
    {
        return DB::transaction(function () use ($dto) {
            $client = Client::create([
                'user_id'      => $dto->userId,
                'ulid'         => (string) Str::ulid(),
                'name'         => $dto->name,
                'company_name' => $dto->companyName,
                'email'        => $dto->email,
                'phone'        => $dto->phone,
                'currency'     => $dto->currency,
                'source'       => $dto->source,
            ]);

            // Seed empty health score row
            $client->healthScore()->create([
                'score'      => 50,  // neutral default
                'computed_at'=> now(),
            ]);

            // Event fires AFTER transaction — listener has $afterCommit = true
            event(new ClientCreatedEvent($client));

            return $client;
        });
    }
}
```

---

## 4. Database Engineering Review

### 4.1 Critical Fix: Replace ENUM with VARCHAR + Check Constraints

**Finding C-03 — Severity: Critical**

MySQL `ENUM` modifications require an `ALTER TABLE` full rebuild. On a table with millions of rows, adding a new enum value = minutes of downtime.

```sql
-- ❌ V1 — DANGEROUS in production
status ENUM('active','inactive','prospect','archived')

-- ✅ V2 — Use VARCHAR with application-level enforcement via Enum class
status VARCHAR(20) NOT NULL DEFAULT 'active',
CONSTRAINT chk_client_status CHECK (status IN ('active','inactive','prospect','archived'))
```

Application Enum handles validation. DB constraint is a safety net. Adding new values only requires:
1. Add to PHP Enum (deploy)
2. Update CHECK constraint (fast ALTER, no rebuild)

Apply this to ALL `ENUM` columns across all CRM tables.

---

### 4.2 Critical Fix: client_activities Partitioning

**Finding C-05 — Severity: Critical**

With 5,000 users, each generating ~50 activity events/day, the `client_activities` table accumulates:
- **Year 1:** ~90M rows
- **Year 3:** ~270M rows

Unpartitioned, this becomes a full-table-scan nightmare.

```sql
-- V2: Partition by RANGE on created_at (yearly partitions)
CREATE TABLE client_activities (
    id           BIGINT UNSIGNED NOT NULL,
    client_id    BIGINT UNSIGNED NOT NULL,
    user_id      BIGINT UNSIGNED NOT NULL,
    type         VARCHAR(50) NOT NULL,
    title        VARCHAR(255) NOT NULL,
    body         TEXT NULL,
    subject_type VARCHAR(100) NULL,
    subject_id   BIGINT UNSIGNED NULL,
    metadata     JSON NULL,
    is_pinned    TINYINT(1) DEFAULT 0,
    is_private   TINYINT(1) DEFAULT 1,
    source       VARCHAR(10) DEFAULT 'system',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, created_at),  -- ← partition key MUST be in PK
    INDEX idx_client_type_date (client_id, type, created_at),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_pinned  (client_id, is_pinned, created_at)
)
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p2027 VALUES LESS THAN (2028),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

**Note:** Implement a scheduled command to add next year's partition in Q4 each year.

---

### 4.3 FULLTEXT Index Scoping Problem

**Finding M-03**

MySQL FULLTEXT search does NOT support combined regular + fulltext WHERE clauses efficiently:

```sql
-- ❌ V1: This forces a full-table FULLTEXT scan, then filters by user_id
SELECT * FROM clients
WHERE user_id = 5
  AND MATCH(name, company_name, email, phone) AGAINST ('ahmed' IN BOOLEAN MODE);
```

**Solutions (choose based on scale):**

**Option A (< 50k clients/tenant):** Keep FULLTEXT, add composite index for post-filter:
```sql
-- Application-level: FULLTEXT first, then filter in PHP for small tenants
-- Acceptable when avg tenant has < 1,000 clients
```

**Option B (> 50k clients/tenant — future):** Move search to Meilisearch/Algolia:
```php
// app/Models/Client.php
use Laravel\Scout\Searchable;

class Client extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id'           => $this->ulid,
            'user_id'      => $this->user_id,  // Filter key in Meilisearch
            'name'         => $this->name,
            'company_name' => $this->company_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
}
```

**Recommendation:** Implement FULLTEXT for Phase 1-3. Switch to Scout+Meilisearch when any tenant exceeds 5,000 clients.

---

### 4.4 Missing Indexes on Hot Query Paths

```sql
-- Follow-ups daily dashboard query (fires every morning for every user)
-- Query: "pending follow-ups due today or overdue, for user X"
ALTER TABLE client_follow_ups
    ADD INDEX idx_followup_dashboard (user_id, status, due_at);
-- V1 has this ✓ — good

-- Client list sorted by last_activity_at (most common sort)
-- V1 has idx_clients_last_activity (user_id, last_activity_at) ✓

-- MISSING: Covering index for client list query
ALTER TABLE clients
    ADD INDEX idx_client_list_cover
    (user_id, is_archived, status, health_score, last_activity_at, id);
-- This allows the list query to be served entirely from the index

-- MISSING: Portal token lookup with expiry check
ALTER TABLE client_portal_tokens
    ADD INDEX idx_portal_valid (token, expires_at, revoked_at);
-- Replaces the simple idx_portal_token (token only)

-- MISSING: Activity timeline per client, recent first
-- V1 has idx_activities_client_type (client_id, type, created_at) ✓
-- But for unfiltered timeline (all types), need:
ALTER TABLE client_activities
    ADD INDEX idx_activities_timeline (client_id, created_at DESC);
```

### 4.5 JSON Column Strategy

V1 uses JSON columns in several places. Hardened rules:

| Column | Usage | Risk | Mitigation |
|--------|-------|------|------------|
| `client_activities.metadata` | Event payload | Schema drift | Add JSON Schema validation in Action |
| `saved_segments.filters` | Filter state | Crash on corrupt data | Validate before save; version the schema |
| `client_portal_tokens.permissions` | Access control | Invalid permissions | Cast to PortalPermission[] enum in model |
| `client_field_values.value` | User data | All-text; no type | Field type stored in definition; cast in accessor |

```php
// Validate segment filters JSON before storing
class SaveSegmentAction
{
    private const FILTER_SCHEMA_VERSION = 1;

    public function handle(array $filters): SavedSegment
    {
        // Validate filter structure before persisting
        $this->validateFilterSchema($filters);

        return SavedSegment::create([
            'filters' => array_merge($filters, ['_version' => self::FILTER_SCHEMA_VERSION]),
        ]);
    }

    private function validateFilterSchema(array $filters): void
    {
        $validator = Validator::make($filters, [
            '*.field'    => ['required', 'string', Rule::in(ClientFiltersDTO::ALLOWED_FIELDS)],
            '*.operator' => ['required', 'string', Rule::in(['eq','gt','lt','gte','lte','in','not_in'])],
            '*.value'    => ['required'],
        ]);

        if ($validator->fails()) {
            throw new InvalidSegmentFilterException($validator->errors()->first());
        }
    }
}
```

### 4.6 Corrected Schema: Missing Fields

```sql
-- FIX N-01: Add updated_at to client_health_scores
ALTER TABLE client_health_scores
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- FIX: Add score_trend to health scores (track improvement/decline)
ALTER TABLE client_health_scores
    ADD COLUMN previous_score TINYINT UNSIGNED NULL,
    ADD COLUMN trend TINYINT DEFAULT 0;  -- positive = improving, negative = declining

-- FIX: Add ulid to client_follow_ups (for API addressing)
ALTER TABLE client_follow_ups
    ADD COLUMN ulid CHAR(26) NOT NULL UNIQUE AFTER id;

-- FIX: Import log — add file size and mime type tracking
ALTER TABLE client_import_logs
    ADD COLUMN file_size INT UNSIGNED NULL AFTER filename,
    ADD COLUMN mime_type VARCHAR(100) NULL AFTER file_size,
    ADD COLUMN idempotency_key CHAR(64) NULL UNIQUE;  -- C-06 fix
```

---

## 5. API Engineering Review

### 5.1 Critical Fix: Idempotency for Import

**Finding C-06**

Client re-submission or network retry must not create duplicate import jobs:

```php
// POST /api/v1/clients/import/confirm
// Header: X-Idempotency-Key: <uuid>

class ConfirmImportController
{
    public function __invoke(ConfirmImportRequest $request): JsonResponse
    {
        $key = $request->header('X-Idempotency-Key');

        // Check if this key was already processed
        $existing = ClientImportLog::where('idempotency_key', $key)->first();
        if ($existing) {
            return response()->json([
                'data' => new ImportLogResource($existing),
                'meta' => ['idempotent_replay' => true],
            ]);
        }

        $log = dispatch(new ImportClientsJob(
            userId:          $request->user()->id,
            rows:            $request->session()->get("import_preview_{$request->preview_id}"),
            idempotencyKey:  $key,
        ));

        return response()->json(['data' => new ImportLogResource($log)], 202);
    }
}
```

### 5.2 Cursor-Based Pagination (Replace Offset)

**Finding M-02**

Offset pagination (`LIMIT 25 OFFSET 500`) performs a full index scan to skip 500 rows. With 10k clients, this becomes slow.

```php
// V2: All list endpoints use cursor pagination
// Controller
return ClientListResource::collection(
    (new ClientQueryBuilder(auth()->id()))
        ->applyFilters($dto)
        ->applySort($dto->sort, $dto->sortDir)
        ->withListRelations()
        ->cursorPaginate($dto->perPage)
);

// Response shape
{
    "data": [...],
    "meta": {
        "per_page": 25,
        "has_more": true
    },
    "links": {
        "next": "/api/v1/clients?cursor=eyJpZCI6MTAwfQ==",
        "prev": null
    }
}
```

**Exception:** Export, import preview, and segment builder use `->get()` with explicit limits.

### 5.3 Standard Error Contract

**Finding M-06 — All API errors must follow one shape:**

```json
// 4xx/5xx unified error response
{
    "error": {
        "code": "CLIENT_LIMIT_EXCEEDED",
        "message": "وصلت للحد الأقصى (١٠ عملاء) على الخطة المجانية.",
        "details": null,
        "trace_id": "01HXZABC123",
        "docs_url": "https://docs.darahum.com/errors/CLIENT_LIMIT_EXCEEDED"
    }
}

// 422 Validation error
{
    "error": {
        "code": "VALIDATION_FAILED",
        "message": "تحقق من البيانات المدخلة.",
        "details": {
            "email": ["صيغة الإيميل غير صحيحة."],
            "name":  ["هذا الحقل مطلوب."]
        }
    }
}
```

```php
// app/Exceptions/Handler.php — standardize all API errors
public function render($request, Throwable $e): Response
{
    if ($request->expectsJson()) {
        return response()->json([
            'error' => [
                'code'     => $this->getErrorCode($e),
                'message'  => $this->getLocalizedMessage($e),
                'details'  => $e instanceof ValidationException ? $e->errors() : null,
                'trace_id' => (string) Str::ulid(),
            ],
        ], $this->getStatusCode($e));
    }
    // ...
}
```

### 5.4 Rate Limiting Strategy

```php
// config/api_rate_limits.php
return [
    'clients.list'        => ['free' => '60/min', 'pro' => '200/min', 'business' => '600/min'],
    'clients.store'       => ['free' => '10/min', 'pro' => '60/min',  'business' => '200/min'],
    'clients.import'      => ['free' => '0/day',  'pro' => '3/day',   'business' => '20/day'],
    'clients.export'      => ['free' => '1/day',  'pro' => '10/day',  'business' => '100/day'],
    'portal.access'       => '30/min per token',  // brute force protection
    'portal.download'     => '20/min per token',
    'search'              => ['free' => '30/min', 'pro' => '120/min', 'business' => '400/min'],
];

// app/Http/Middleware/ThrottleByPlan.php
class ThrottleByPlan
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        $plan = $request->user()?->plan ?? 'free';
        $limit = config("api_rate_limits.{$key}.{$plan}", '60/min');

        [$maxAttempts, $decayUnit] = explode('/', $limit);
        $decaySeconds = match($decayUnit) {
            'min'  => 60,
            'hour' => 3600,
            'day'  => 86400,
        };

        return RateLimiter::attempt(
            key:          "{$key}:{$plan}:{$request->user()?->id}",
            maxAttempts:  (int) $maxAttempts,
            callback:     fn () => $next($request),
            decaySeconds: $decaySeconds,
        ) ?: response()->json(['error' => ['code' => 'RATE_LIMIT_EXCEEDED']], 429);
    }
}
```

### 5.5 Async Export Pattern (Fix V1 Ambiguity)

V1 defines `GET /api/v1/clients/export` without clarifying sync vs. async behavior. Hardened:

```
Small export (< 500 clients):
  GET /api/v1/clients/export?format=csv
  → 200 OK + file stream (synchronous)
  → Header: Content-Disposition: attachment; filename="clients-2026-05-24.csv"

Large export (> 500 clients OR xlsx/pdf):
  POST /api/v1/clients/export
  → 202 Accepted + job ID
  → { "data": { "export_id": "01HXZ...", "status": "processing", "estimated_seconds": 30 } }

  GET /api/v1/clients/export/{export_id}
  → 200 + status + download URL when ready
  → { "data": { "status": "ready", "download_url": "https://...", "expires_at": "..." } }
```

### 5.6 Webhook Architecture (Future V2)

```
Event Types for Webhooks:
  client.created
  client.updated
  client.deleted
  client.tag_assigned
  client.health_score_changed
  client.became_inactive
  follow_up.due
  portal.invoice_downloaded
  import.completed

Delivery:
  POST to user-configured URL
  Signed with HMAC-SHA256: X-Darahum-Signature header
  Retry: 3 attempts with exponential backoff (1m, 5m, 30m)
  Dead letter: After 3 failures, mark webhook as failed + notify user

Signature verification (recipient side):
  hash_equals(
      hash_hmac('sha256', $payload, $webhookSecret),
      $request->header('X-Darahum-Signature')
  );
```

---

## 6. Frontend & UX System

### 6.1 Component Architecture (Blade + Alpine.js / Livewire)

```
resources/views/clients/
├── index.blade.php              # Client list + sidebar
├── show.blade.php               # Profile 360°
├── create.blade.php             # Create form
├── edit.blade.php               # Edit form
├── partials/
│   ├── _list-item.blade.php     # Single client row (reusable)
│   ├── _tag-picker.blade.php    # Tag selection dropdown
│   ├── _health-badge.blade.php  # Score + color indicator
│   ├── _activity-item.blade.php # Single timeline event
│   ├── _follow-up-card.blade.php
│   └── _segment-builder.blade.php
└── portal/
    ├── layout.blade.php
    ├── invoices.blade.php
    └── projects.blade.php

resources/js/components/
├── TagPicker.js         # Alpine: tag selection with search
├── SegmentBuilder.js    # Alpine: filter builder state
├── ActivityTimeline.js  # Infinite scroll timeline
└── ClientSearch.js      # Debounced live search
```

### 6.2 Interaction Flows — Hardened UX Rules

**Tag Assignment (Zero-friction target: 1 click)**

```
❌ V1 approach: User opens dropdown → searches → clicks → saves separately

✅ V2 approach: Optimistic UI
1. User clicks tag chip on client row
2. Tag state changes IMMEDIATELY in UI (no loading)
3. PATCH request fires in background
4. If request fails: revert tag + show toast error
5. If success: no visual change needed (already correct)
```

**Activity Log Entry (Target: < 10 seconds)**

```
1. Click "+ إضافة حدث" in timeline header
2. Inline form appears below header (no modal)
3. Select type (dropdown: ملاحظة / مكالمة / اجتماع / أخرى)
4. Text area (autofocus)
5. Cmd+Enter to submit (keyboard shortcut)
6. Entry appears at top of timeline immediately (optimistic)
```

**Follow-up Creation (Target: < 15 seconds)**

```
1. Click "متابعة ←" on client row or from 3-dot menu
2. Sheet/drawer from right (not full page)
3. Fields: Title, Date, Time, Priority, Notes (optional)
4. Smart date suggestions: "غداً", "الأسبوع القادم", "بعد أسبوعين"
5. Save → sheet closes → counter badge on client updates
```

### 6.3 Keyboard Shortcuts

```
Global:
  / or Cmd+K       → Open client search spotlight
  G then C         → Go to Clients list
  G then F         → Go to Follow-ups

Client List:
  J / K            → Navigate rows up/down
  Enter            → Open selected client
  T                → Open tag picker on selected client
  N                → Create new follow-up for selected
  Cmd+Enter        → Open client in new tab

Client Profile:
  Tab              → Cycle through profile tabs
  N                → Add note inline
  F                → Schedule follow-up
  E                → Edit client info
  Esc              → Close any open panel
```

### 6.4 Client Profile Tab State Management

```
// URL reflects tab state for deep linking and back navigation
/clients/01HXZ.../activity     # Default
/clients/01HXZ.../invoices
/clients/01HXZ.../projects
/clients/01HXZ.../files
/clients/01HXZ.../follow-ups

// This allows:
- Shareable links to specific tabs (team accounts future)
- Browser back/forward works correctly
- Page refresh preserves state
```

### 6.5 RTL-Specific UX Rules

| Element | RTL Rule |
|---------|----------|
| Timeline | Events flow from right (newest) to left (oldest) in horizontal layout |
| Health score bar | Fills from right to left |
| Tag pills | Right-aligned, overflow hides on left |
| Arrow indicators | ← for "next", → for "previous" (flipped from LTR) |
| Monetary amounts | Always `٤٥,٢٠٠ ر.س` not `SAR 45,200` |
| Date format | `الثلاثاء، ٢٤ مايو ٢٠٢٦` — no ambiguous `05/24/26` |
| Phone numbers | Display with leading `+966` but input accepts bare numbers |

### 6.6 Dark Mode Readiness

```css
/* Design tokens — defined once, work in both modes */
:root {
    --clr-bg-primary:   #FAFAF9;
    --clr-bg-secondary: #FFFFFF;
    --clr-text-primary: #0F172A;
    --clr-text-muted:   #64748B;
    --clr-brand:        #3730A3;
    --clr-accent:       #2DCEA8;
    --clr-border:       rgba(15, 23, 42, 0.08);
}

[data-theme="dark"] {
    --clr-bg-primary:   #0F0D2A;
    --clr-bg-secondary: #1A1836;
    --clr-text-primary: #F1F5F9;
    --clr-text-muted:   #94A3B8;
    --clr-brand:        #6366F1;
    --clr-accent:       #2DCEA8;
    --clr-border:       rgba(255, 255, 255, 0.07);
}

/* Rule: NO hardcoded colors in component CSS. Use variables only. */
```

### 6.7 Accessibility (WCAG 2.1 AA)

```
Required:
  - All form fields: associated <label> (not placeholder-only)
  - Health score bar: aria-valuenow, aria-valuemin, aria-valuemax, aria-label
  - Tag pills: role="listitem", keyboard removable via Backspace/Delete
  - Timeline items: role="list" + role="listitem"
  - Empty states: aria-live="polite" for dynamic updates
  - Color-only indicators: always paired with text/icon
    ✅ "🔴 يتأخر في الدفع" — not just a red dot
  - Contrast ratios: 4.5:1 minimum for all text
```

---

## 7. Security Audit

### 7.1 Critical Fix: Portal Token Brute Force

**Finding C-04 — Severity: Critical**

V1 generates a 64-char token but has no protection against systematic guessing:

```php
// V2 — Multi-layer protection

// 1. Rate limit portal endpoint aggressively
Route::middleware(['throttle:portal-access'])->group(function () {
    Route::get('/portal/{token}', [PortalController::class, 'entry']);
});

// 2. Token format: include a checksum to invalidate random guesses
//    Format: base62(client_id) + '-' + random_64_chars
//    Even a valid-looking random token fails if prefix doesn't match client

// 3. Exponential backoff on failed token lookups
class ValidatePortalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $rawToken = $request->route('token');
        $ip = $request->ip();
        $cacheKey = "portal_attempts:{$ip}";

        $attempts = Cache::get($cacheKey, 0);
        if ($attempts >= 10) {
            abort(429, 'Too many invalid portal attempts.');
        }

        $token = ClientPortalToken::where('token', hash('sha256', $rawToken))
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at')
            ->first();

        if (!$token) {
            Cache::put($cacheKey, $attempts + 1, now()->addHour());
            // Constant-time response — don't reveal if token format is valid
            usleep(random_int(50000, 150000)); // 50-150ms artificial delay
            abort(404);
        }

        Cache::forget($cacheKey);  // Reset on success
        $token->update(['last_used_at' => now()]);

        return $next($request);
    }
}

// 4. Store token hash, not plaintext
// On generation:
$plaintext = Str::random(64);
$hash = hash('sha256', $plaintext);
ClientPortalToken::create(['token' => $hash, ...]);
// Return $plaintext to user once — never stored in plaintext
```

### 7.2 Import Security

```php
// File upload validation
class ImportClientsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls',
                'max:5120',  // 5MB limit
                // Custom rule: check actual MIME type, not just extension
                new ValidFileMimeType(['text/csv', 'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
            ],
        ];
    }
}

// Virus scan (production)
class ScanUploadedFile
{
    public function handle(UploadedFile $file): void
    {
        if (config('app.env') === 'production') {
            $result = ClamAV::scan($file->path());
            if ($result->isInfected()) {
                Log::warning('Infected file upload attempt', [
                    'user_id' => auth()->id(),
                    'filename'=> $file->getClientOriginalName(),
                    'virus'   => $result->virusName(),
                ]);
                throw new InfectedFileException();
            }
        }
    }
}

// Temp file cleanup — always
// Files stored in temporary disk, deleted after 24h regardless
Event::listen(ImportCompleted::class, fn() =>
    Storage::disk('temp')->delete($importLog->temp_path)
);
```

### 7.3 Mass Assignment Protection

```php
// app/Models/Client.php
class Client extends Model
{
    // NEVER use $guarded = []. Always explicit $fillable.
    protected $fillable = [
        'user_id', 'ulid', 'name', 'company_name', 'email', 'phone',
        'website', 'country_code', 'city', 'currency', 'source',
        'referred_by_client_id', 'notes', 'status',
    ];

    // These are NEVER mass-assignable:
    // total_revenue, total_paid, total_outstanding (set via Events only)
    // health_score (set via HealthScoreService only)
    // is_archived, archived_at (set via ArchiveClientAction only)
    // deleted_at (Eloquent SoftDeletes only)
}
```

### 7.4 Audit Logging (Production-Grade)

```php
// Every sensitive operation creates an audit record
// Separate from client_activities (which is user-facing)

// app/Models/AuditLog.php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->unsignedBigInteger('user_id')->nullable();  // null = system
    $table->string('event');    // created|updated|deleted|exported|portal_generated|imported
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamp('created_at');

    $table->index(['auditable_type', 'auditable_id']);
    $table->index(['user_id', 'created_at']);
});

// Must audit:
// - Client PII changes (email, phone)
// - Client deletion
// - Export operations (who exported what data)
// - Portal token generation/revocation
// - Import operations
// - Bulk tag changes
```

### 7.5 Attachment Security

```php
// NEVER serve attachments from public disk
// ALWAYS use temporary signed URLs

class AttachmentController
{
    public function download(Client $client, ClientAttachment $attachment): RedirectResponse
    {
        $this->authorize('view', $client);  // Policy check

        // Verify attachment belongs to this client (IDOR prevention)
        abort_unless($attachment->client_id === $client->id, 403);

        // Log the download
        AuditLog::record($attachment, 'downloaded', $request->user());

        // Signed URL (15 min expiry for large files)
        $url = Storage::disk($attachment->disk)
            ->temporaryUrl($attachment->path, now()->addMinutes(15));

        return redirect($url);
    }
}
```

---

## 8. SaaS Monetization Strategy

### 8.1 Feature Gate Matrix (Hardened)

```php
// config/plan_features.php
return [
    // ─── Client Limits ───
    'max_clients'            => ['free' => 10,   'pro' => -1,    'business' => -1],
    'max_custom_tags'        => ['free' => 0,    'pro' => 20,    'business' => 100],
    'max_custom_fields'      => ['free' => 0,    'pro' => 5,     'business' => 20],
    'max_saved_segments'     => ['free' => 0,    'pro' => 10,    'business' => 50],
    'max_attachments_per_client' => ['free' => 0, 'pro' => 5,   'business' => 25],
    'max_import_rows_per_day'=> ['free' => 0,    'pro' => 500,   'business' => 5000],

    // ─── Features ───
    'client_import'          => ['free' => false, 'pro' => true,  'business' => true],
    'client_export_csv'      => ['free' => true,  'pro' => true,  'business' => true],
    'client_export_excel'    => ['free' => false, 'pro' => true,  'business' => true],
    'client_export_pdf'      => ['free' => false, 'pro' => true,  'business' => true],
    'client_portal'          => ['free' => false, 'pro' => false, 'business' => true],
    'client_health_score'    => ['free' => false, 'pro' => true,  'business' => true],
    'client_activity_timeline'=> ['free' => false,'pro' => true,  'business' => true],
    'client_attachments'     => ['free' => false, 'pro' => true,  'business' => true],
    'client_custom_fields'   => ['free' => false, 'pro' => true,  'business' => true],
    'client_segmentation'    => ['free' => false, 'pro' => true,  'business' => true],
    'client_follow_ups'      => ['free' => false, 'pro' => true,  'business' => true],
    'automation_rules'       => ['free' => false, 'pro' => false, 'business' => true],
    'bulk_actions'           => ['free' => false, 'pro' => true,  'business' => true],
    'api_access'             => ['free' => false, 'pro' => true,  'business' => true],
    'webhooks'               => ['free' => false, 'pro' => false, 'business' => true],
    'team_crm_access'        => ['free' => false, 'pro' => false, 'business' => true],
];
```

### 8.2 Upgrade Trigger Moments

These are in-product moments that should show an upgrade prompt:

```
Moment 1: User tries to add 11th client on Free plan
  → Modal: "أضف عملاء غير محدودين مع خطة Pro — ٩٩ ر.س/شهر"
  → CTA: "ابدأ تجربة ١٤ يوم مجاناً"

Moment 2: User tries to import clients on Free plan
  → Tooltip: "استيراد العملاء متاح على خطة Pro"

Moment 3: User views client profile, activity timeline is blurred/locked
  → "سجل النشاط الكامل متاح على Pro — شاهد كل تفاعل مع عملائك"

Moment 4: User tries to tag more than 0 custom tags on Free
  → Show system tags as teaser; "أنشئ وسومك الخاصة على Pro"

Moment 5: User searches saved segments, sees empty state
  → "احفظ شرائح عملائك للوصول السريع — Pro فقط"

Moment 6: Business plan upsell — on Pro when user mentions "فريق" or "موظف"
  → Tooltip: "أضف أعضاء فريق لإدارة العملاء معاً — Business Plan"
```

### 8.3 Additional Monetization Opportunities

| Feature | Plan | Business Logic |
|---------|------|----------------|
| **Client Portal Branding** | Business+ | Remove "Powered by دراهم" from portal, add custom logo | White-label upcharge |
| **Portal Custom Domain** | Enterprise | portal.youragency.com instead of darahum.com/portal/... |
| **Bulk SMS/WhatsApp Credits** | Add-on | Pay-per-use on top of any plan |
| **Extra Import Quota** | Add-on | Buy 5,000 extra import rows |
| **Data Backup Export** | Business | Full account data export (GDPR compliance tool) |
| **Advanced Analytics** | Business | Client LTV trends, revenue forecasting, cohort analysis |
| **API Rate Limit Increase** | Add-on | 10x rate limit for integration-heavy users |

### 8.4 Activation Mechanics (Free → Paid conversion path)

```
Day 1:  User adds 1st client manually
Day 2:  User sends 1st invoice from client profile (AHA moment)
Day 3:  User sees "timeline is locked on free" nudge
Day 7:  Automated email: "٧ أيام مع دراهم — إليك ما فاتك على الخطة المجانية"
Day 14: Trial of Pro starts if user clicks any Pro-locked feature
Day 28: "تجربتك تنتهي خلال يومين" notification
```

---

## 9. AI & Intelligence Layer

### 9.1 Practical Implementation Levels

| Level | Technology | Phase | Effort |
|-------|-----------|-------|--------|
| L1 — Rule-based | PHP logic | Phase 1 | Low |
| L2 — Statistical | SQL aggregates + scoring | Phase 2 | Medium |
| L3 — ML inference | Python microservice / AWS SageMaker | Phase 4 | High |
| L4 — LLM integration | Claude/OpenAI API | Phase 5 | Medium |

### 9.2 L1: Smart Tag Auto-Suggestion (Phase 1)

```php
// Rule-based. No ML. 100% explainable. Ship now.
class SmartTagSuggestionService
{
    public function analyze(Client $client): array
    {
        $suggestions = [];

        // Rule 1: VIP qualification (hard criteria)
        if ($client->invoice_count >= 3
            && $client->avg_payment_days <= 5
            && $client->total_outstanding == 0) {
            $suggestions[] = [
                'tag_slug'   => 'vip',
                'confidence' => 94,
                'reason'     => "يدفع في أقل من ٥ أيام، ٣+ فواتير مكتملة، لا مبالغ متأخرة",
            ];
        }

        // Rule 2: Slow payer (statistical evidence required)
        if ($client->invoice_count >= 2 && $client->avg_payment_days >= 20) {
            $suggestions[] = [
                'tag_slug'   => 'slow-payer',
                'confidence' => min(99, 60 + ($client->avg_payment_days - 20) * 2),
                'reason'     => "متوسط {$client->avg_payment_days} يوم للدفع عبر {$client->invoice_count} فواتير",
            ];
        }

        // Rule 3: High-value prospect (has projects, no invoices sent yet)
        if ($client->project_count >= 1 && $client->invoice_count === 0) {
            $suggestions[] = [
                'tag_slug'   => 'prospect',
                'confidence' => 72,
                'reason'     => "لديه مشاريع لكن لم يُرسل له أي فاتورة بعد",
            ];
        }

        return $suggestions;
    }
}
```

### 9.3 L2: Dynamic Health Score V2 (Phase 2)

Enhance V1's static weights with **recency bias** — recent behavior matters more than old behavior:

```php
class ClientHealthScoreService
{
    public function compute(Client $client): HealthScoreResult
    {
        // Payment Score — weighted by recency
        $recentInvoices  = $this->getInvoices($client, months: 3);
        $historicInvoices= $this->getInvoices($client, months: 12);

        $recentPaymentScore  = $this->scorePaymentSpeed($recentInvoices);
        $historicPaymentScore= $this->scorePaymentSpeed($historicInvoices);

        // Recent behavior counts 70%, historic 30%
        $paymentScore = ($recentPaymentScore * 0.7) + ($historicPaymentScore * 0.3);

        // Trend calculation
        $previousScore = $client->healthScore?->score ?? 50;
        $newScore = $this->computeComposite([...]);
        $trend = $newScore - $previousScore;  // positive = improving

        return new HealthScoreResult(
            score:          $newScore,
            paymentScore:   $paymentScore,
            revenueScore:   $revenueScore,
            projectScore:   $projectScore,
            engagementScore:$engagementScore,
            reliabilityScore:$reliabilityScore,
            trend:          $trend,
            version:        2,
        );
    }
}
```

### 9.4 L2: Anomaly Detection — Churn Signal

```php
// Detect churn signals without ML — statistical deviation
class ChurnSignalDetector
{
    public function detect(Client $client): ?ChurnSignal
    {
        // Signal 1: Invoice frequency dropped significantly
        $avgFrequency30d = $this->avgInvoiceFrequency($client, days: 90);
        $currentFrequency= $this->avgInvoiceFrequency($client, days: 30);

        if ($avgFrequency30d > 0 && $currentFrequency < ($avgFrequency30d * 0.3)) {
            return new ChurnSignal(
                type:        'invoice_frequency_drop',
                severity:    'high',
                description: 'انخفض معدل الفواتير بأكثر من ٧٠٪ مقارنة بالمتوسط',
                suggested_action: 'تواصل مع العميل لمعرفة سبب انخفاض النشاط',
            );
        }

        // Signal 2: Payment delay increasing trend
        $last3Payments = $this->getLastNPaymentDurations($client, 3);
        if (count($last3Payments) === 3 && $this->isIncreasing($last3Payments)) {
            return new ChurnSignal(
                type:        'payment_delay_trend',
                severity:    'medium',
                description: 'أوقات الدفع تتزايد باطّراد',
            );
        }

        return null;
    }
}
```

### 9.5 L4: AI Follow-up Message Generator (Phase 5)

```php
// Uses Claude API (Anthropic) — Arabic-native LLM

class AiFollowUpMessageService
{
    public function generate(Client $client, FollowUpContext $context): string
    {
        $prompt = $this->buildPrompt($client, $context);

        $response = Http::withHeaders(['x-api-key' => config('services.anthropic.key')])
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',  // Fast + cheap for this use case
                'max_tokens' => 300,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        return $response->json('content.0.text');
    }

    private function buildPrompt(Client $client, FollowUpContext $context): string
    {
        return <<<PROMPT
        أنت مساعد كتابة محترف للمستقلين العرب.
        اكتب رسالة متابعة قصيرة ومهنية باللغة العربية.

        معلومات العميل:
        - الاسم: {$client->name}
        - مدة العلاقة: {$context->relationshipMonths} شهر
        - آخر تواصل: {$context->daysSinceLastContact} يوم مضت
        - السياق: {$context->reason}

        القواعد:
        - لا تزيد عن ٣ جمل
        - أسلوب ودي ومهني (ليس رسمياً جداً)
        - لا تذكر الأرقام والمبالغ
        - اترك مسافة [للاسم] للتخصيص
        PROMPT;
    }
}
```

### 9.6 L3: Payment Risk Prediction (Phase 4 — ML)

```
Input features:
  - avg_payment_days (last 3, 6, 12 months)
  - invoice_count
  - total_outstanding / total_revenue ratio
  - days_since_last_payment
  - has_slow_payer_tag
  - number_of_reminder_emails sent
  - client_health_score

Model: Gradient Boosted Trees (XGBoost)
       Trained on historical paid/unpaid invoice data

Output: probability 0-1 of invoice being paid within 30 days

Integration:
  - Python microservice via HTTP (or Laravel Octane with embedded Python via subprocess)
  - Score cached per client for 6 hours
  - Displayed as "احتمال الدفع في الموعد: ٨٥٪" on invoice creation
```

---

## 10. Engineering Execution Readiness

### 10.1 Implementation Sequence (Sprint-by-Sprint)

```
Sprint 1 (Week 1-2): Database + Core Layer
  ✓ All migrations with V2 schema (VARCHAR not ENUM, ULID, partitioned activities)
  ✓ All Enums defined
  ✓ All Model casts + fillable + scopes
  ✓ System tags seeder
  ✓ ClientPolicy with all gates
  ✓ All Form Requests validated
  ✓ ClientQueryBuilder
  ✓ DTOs: CreateClientDTO, UpdateClientDTO, ClientFiltersDTO
  Deliverable: Passing schema migrations + unit tests

Sprint 2 (Week 3-4): Service + Action Layer
  ✓ CreateClientAction, UpdateClientAction, DeleteClientAction, ArchiveClientAction
  ✓ AssignTagAction, RemoveTagAction
  ✓ LogClientActivityAction (with afterCommit = true)
  ✓ Observers: ClientObserver, InvoiceObserver, ProjectObserver
  ✓ Events + Listeners wired
  ✓ UpdateClientAggregates listener (atomic increments)
  Deliverable: Core CRUD + activity logging working

Sprint 3 (Week 5-6): API + Resources
  ✓ ClientListResource, ClientProfileResource
  ✓ All API endpoints (CRUD, tags, activities)
  ✓ Cursor pagination
  ✓ Rate limiting middleware
  ✓ Standardized error responses
  ✓ API tests (Feature tests for every endpoint)
  Deliverable: Postman collection + API tests all green

Sprint 4 (Week 7-8): Import/Export
  ✓ ImportClientsRequest (with virus scan on prod)
  ✓ Import preview (returns sample rows, field mapping UI)
  ✓ ImportClientsJob (chunked, idempotency key)
  ✓ Export: CSV sync (<500), async (>500)
  ✓ ClientImportLog + import result notification
  Deliverable: Import/Export E2E working

Sprint 5 (Week 9-10): Health Score + Segmentation
  ✓ ClientHealthScoreService (V2 with recency bias)
  ✓ RecalculateHealthScoreCommand + nightly schedule
  ✓ ClientSegmentService (prebuilt + custom)
  ✓ ClientQueryBuilder: all filter types
  ✓ SavedSegment save/restore
  Deliverable: Health score visible; segments working

Sprint 6 (Week 11-12): Follow-ups + Automation
  ✓ FollowUp CRUD + ClientFollowUpService
  ✓ DetectInactiveClientsCommand
  ✓ AutomationRuleEngine (3 core rules)
  ✓ Daily follow-up reminder notification
  Deliverable: Follow-ups + auto-reminders working

Sprint 7 (Week 13-14): Frontend
  ✓ Client list with filters, search, sorting
  ✓ Profile 360° page (all tabs)
  ✓ Tag picker (optimistic)
  ✓ Activity timeline with infinite scroll
  ✓ Follow-up drawer
  ✓ Import wizard (3 steps)
  Deliverable: Full UI working E2E
```

### 10.2 Testing Strategy

```
Unit Tests (run on every commit):
  ✓ ClientHealthScoreService::compute()    — all boundary cases
  ✓ SmartTagSuggestionService::analyze()   — all tag rules
  ✓ ClientQueryBuilder                     — all filter combinations
  ✓ AutomationRuleEngine::evaluate()       — each rule
  ✓ PortalTokenService::generate()         — token format + expiry
  ✓ ImportClientsJob — duplicate detection, row validation

Feature Tests (run on every PR):
  ✓ POST /api/v1/clients — all validation rules
  ✓ PATCH /api/v1/clients/{id}/tags/sync — tag assignment
  ✓ GET  /api/v1/clients?filters[...] — all filter types
  ✓ POST /api/v1/clients/import/confirm — idempotency
  ✓ GET  /portal/{token} — valid/invalid/expired token
  ✓ Policy: other user's client returns 403
  ✓ Plan limit: 11th client on free plan returns 422

Browser Tests (Dusk — run before release):
  ✓ Create client + assign tag (optimistic UI)
  ✓ Import flow: upload → preview → confirm → result
  ✓ Timeline: log note → appears instantly
  ✓ Follow-up: create → complete → badge updates
  ✓ Segment: build filter → save → reload persists

Performance Tests (run weekly):
  ✓ Client list for tenant with 5,000 clients < 200ms
  ✓ Timeline for client with 1,000 activities < 100ms
  ✓ Import of 500 rows < 30 seconds (queue job)
  ✓ Health score recalculation for 5,000 clients < 5 min
```

### 10.3 Observability & Metrics

```php
// Key metrics to track (send to Datadog / Prometheus / Laravel Pulse)

// Business Metrics
Metric::gauge('crm.clients.total', Client::count());
Metric::gauge('crm.clients.active', Client::where('status','active')->count());
Metric::gauge('crm.follow_ups.overdue', FollowUp::overdue()->count());

// Performance Metrics
Metric::histogram('crm.client_list.query_time', $queryMs);
Metric::histogram('crm.health_score.compute_time', $computeMs);
Metric::histogram('crm.import.job_duration', $jobMs);

// Error Metrics
Metric::increment('crm.import.errors', $errorCount);
Metric::increment('crm.portal.invalid_token_attempts');
Metric::increment('crm.export.failed_jobs');

// Feature Usage (for product analytics)
Metric::increment('crm.features.tag_assigned');
Metric::increment('crm.features.health_score_viewed');
Metric::increment('crm.features.segment_created');
Metric::increment('crm.features.portal_generated');
```

### 10.4 Technical Debt Warnings

| Debt | When It Becomes Critical | Mitigation |
|------|--------------------------|------------|
| FULLTEXT search without Scout | > 5,000 clients per tenant | Plan Scout migration in roadmap |
| Activities table without archiving | Year 1: ~90M rows | Partition NOW; archiving strategy by Month 6 |
| Denormalized aggregates drift | High concurrent payment volume | Nightly reconciliation job is the safety net |
| Single-DB for all tenants | > 10,000 tenants | Consider read replicas when query p95 > 500ms |
| Job retries without dead-letter | Long-running imports fail silently | Implement dead-letter queue from Day 1 |

### 10.5 Engineering Checklists

#### Pre-Implementation Checklist
```
□ All migrations reviewed and tested on staging with production data size
□ All ENUM columns replaced with VARCHAR + CHECK constraints
□ All API endpoints have rate limiting defined
□ client_activities partitioning confirmed with DBA
□ Redis confirmed available (required for cache tags)
□ Queue worker configured (crm-critical, crm-default, crm-batch, crm-automation)
□ Storage driver configured for attachments (S3 in production)
□ File size limits configured in PHP and Nginx
□ ClientPolicy unit tests cover all cases (own, other, plan-gated)
```

#### Pre-Launch Checklist (Phase 1)
```
□ Import/export tested with 100, 500, 1000 row files
□ Portal token: rate limiting tested (brute force scenario)
□ All plan limits enforced + upgrade prompts functional
□ Audit log entries created for: create, update, delete, export
□ Error responses follow standard contract (no stack traces in production)
□ All new routes protected with auth middleware
□ Soft delete: deleted clients not visible in any list/search
□ ULID used consistently in all API URLs (no numeric IDs exposed)
□ Import files cleaned up after 24h (verify scheduled command)
□ Health score seeded as 50 for new clients (not null)
```

#### QA Checklist
```
□ Create client with same email as another client (same user) — allowed
□ Create client with same email as another user's client — allowed
□ View another user's client URL → 403
□ Assign system tag (not owned by user) → allowed
□ Assign custom tag owned by another user → 403/404
□ Export client list on free plan → blocked with upgrade prompt
□ Import on free plan → blocked
□ 11th client on free plan → 422 with limit message
□ Portal token after expiry → 404
□ Portal token after revoke → 404
□ Portal: try accessing /invoices without invoices.view permission → 403
□ Deleted client still visible in associated invoice → name preserved
□ Archive client: disappears from active list, visible in archived tab
□ Restore archived client: reappears in active list
□ Health score: new client with no invoices → 50 (neutral, not 0)
□ Segment with 0 results → empty state shown correctly
□ Import with 100% errors → log shows failed, no clients created
□ Import duplicate detection: same email = duplicate, update or skip
```

---

## 11. Architectural Decision Records (ADRs)

### ADR-001: ULID as Public Client Identifier

**Decision:** Use ULID (not UUID, not auto-increment) as the public identifier for clients.

**Rationale:**
- Auto-increment IDs in URLs are enumerable (`/clients/1`, `/clients/2`) → data harvesting risk
- UUID is random → no sortability → index fragmentation
- ULID is sortable (time-prefixed) → B-tree friendly + URL-safe + not guessable

**Consequence:** All API endpoints use ULID. Internal joins use integer `id`. ULID is indexed uniquely.

---

### ADR-002: Separate Health Score Table

**Decision:** Store health score in a separate `client_health_scores` table, not as a column on `clients`.

**Rationale:**
- Score breakdown (5 sub-scores) would bloat the clients table
- Score has its own version tracking, trend, and computation timestamp
- Allows the score to be nullable (not yet computed) without ambiguity
- Enables lazy loading: list view loads only `clients.health_score` (denormalized); profile loads the full breakdown

**Consequence:** `clients.health_score` is a denormalized copy of `client_health_scores.score`, updated via Event.

---

### ADR-003: Listeners with $afterCommit = true for Activity Logging

**Decision:** All activity-logging listeners MUST use `public $afterCommit = true`.

**Rationale:** Activity logging inside a transaction risks either rolling back the parent operation (if logger fails) or creating orphaned activity records (if parent fails after logging). Post-commit queued listeners solve both problems.

**Consequence:** Activities may appear 1-3 seconds after the triggering event in the UI. This is acceptable. Timeline is not real-time.

---

### ADR-004: Cursor Pagination for All List Endpoints

**Decision:** All client list endpoints use cursor pagination, never offset pagination.

**Rationale:** With 10,000+ clients per tenant, `OFFSET 9000 LIMIT 25` scans 9,025 rows to return 25. Cursor pagination jumps directly to the next page via indexed column comparison.

**Consequence:** "Jump to page 47" is not supported. Accepted — users use search/filter instead of manual pagination.

---

### ADR-005: Denormalized Aggregates with Nightly Reconciliation

**Decision:** Store computed aggregates (total_revenue, invoice_count, etc.) directly on the clients table, updated atomically on events, with a nightly full reconciliation.

**Rationale:**
- Computing `SUM(invoices.total_paid) WHERE client_id = X` on every list render = N+1 query disaster
- Pure event-driven aggregates can drift under concurrent writes
- Nightly full recalculation is the safety net

**Consequence:** Aggregates may be off by small amounts for seconds/minutes during high concurrent activity. The nightly job corrects any drift. For financial reporting purposes, always use source tables (invoices), not denormalized aggregates.

---

*📁 Document: `docs/CLIENTS-CRM-SPEC-V2.md`*  
*🏢 دراهم — Financial & Business SaaS Platform*  
*🔒 Internal Architecture Review — Senior Engineer / CTO Audience Only*  
*⚠️ Supersedes V1 for all engineering implementation decisions*
