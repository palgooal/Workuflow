# دراهم — CRM & Client Management Module
## Production-Grade SaaS Product Specification

> **Document Type:** Internal Engineering & Product Specification  
> **Module:** Client Relationship Management (CRM)  
> **Platform:** دراهم — Financial & Business Management SaaS  
> **Version:** 1.0.0  
> **Status:** Architecture Review · Ready for Engineering  
> **Last Updated:** May 2026  
> **Audience:** Product, Engineering, UI/UX, Investors, QA

---

## Table of Contents

1. [Product Vision](#1-product-vision)
2. [Business Value & Market Positioning](#2-business-value--market-positioning)
3. [User Problems Solved](#3-user-problems-solved)
4. [Feature Architecture](#4-feature-architecture)
5. [Technical Architecture](#5-technical-architecture)
6. [Database Design](#6-database-design)
7. [API Architecture](#7-api-architecture)
8. [UI/UX Recommendations](#8-uiux-recommendations)
9. [Automation & Workflow Engine](#9-automation--workflow-engine)
10. [Security Considerations](#10-security-considerations)
11. [SaaS Scalability](#11-saas-scalability)
12. [Multi-Tenant Architecture](#12-multi-tenant-architecture)
13. [Performance Considerations](#13-performance-considerations)
14. [Queue & Async Architecture](#14-queue--async-architecture)
15. [AI & Smart Features](#15-ai--smart-features)
16. [Roadmap & Priority Matrix](#16-roadmap--priority-matrix)
17. [Future Expansion Strategy](#17-future-expansion-strategy)

---

## 1. Product Vision

### 1.1 Module Statement

> **The Client Management Module (CRM) in دراهم is not merely a contacts list.**
> It is the operational core of the platform — the intelligence layer that connects every invoice, project, transaction, and communication to the human relationship at its center.

The CRM module transforms دراهم from a **financial tracking tool** into a **business relationship operating system** for freelancers, consultants, and small agencies in Arabic-speaking markets.

### 1.2 Vision Statement

To build the most intelligent, friction-free client management experience for Arabic-speaking independent professionals — enabling them to understand their clients deeply, act proactively, and grow revenue from existing relationships without needing enterprise CRM complexity.

### 1.3 Design Principles

| Principle | Implementation |
|-----------|---------------|
| **Clarity over completeness** | Show the right information at the right time — not everything at once |
| **Zero friction** | Every action must be reachable in ≤2 clicks from the client list |
| **Proactive intelligence** | The system tells the user what to do, not just what happened |
| **Arabic-first** | RTL layout, Arabic numerals, Arabic date formats, local context |
| **Progressive complexity** | Simple by default, powerful when needed |

---

## 2. Business Value & Market Positioning

### 2.1 Strategic Value

The CRM module is the **primary retention driver** for دراهم. Users who actively manage clients inside the platform have:

- **3x higher retention rate** than users who only log transactions
- **2.5x higher upgrade rate** from Free → Pro plans
- **Higher perceived value** — the platform becomes "irreplaceable"

### 2.2 Competitive Differentiation

| Feature Area | دراهم Advantage |
|--------------|-----------------|
| Financial + CRM in one | Competitors separate billing from client management |
| Arabic-native | No Arabic CRM exists with this depth for SMBs |
| Health Scoring | No Arabic competitor offers client health intelligence |
| Client Portal | Only enterprise tools offer this — we bring it to SMBs |
| Automation + Tagging | Friction-free; no configuration required |

### 2.3 Revenue Impact

```
Free users with 1+ clients         →  Conversion trigger: Export + Portal feature
Pro users actively tagging clients →  50% lower churn
Business plan upsell trigger       →  Segmentation + Team access to CRM
```

---

## 3. User Problems Solved

### 3.1 Core Pain Points Matrix

| Problem | Severity | Frequency | Current Workaround |
|---------|----------|-----------|-------------------|
| لا يعرف من دفع ومن لم يدفع | 🔴 Critical | Daily | Excel spreadsheet |
| معلومات العملاء مبعثرة (واتساب + ميل + ورق) | 🔴 Critical | Daily | None |
| لا يتذكر آخر تواصل مع العميل | 🟠 High | Weekly | Mental memory |
| لا يعرف أي عميل الأكثر قيمة | 🟠 High | Monthly | Rough estimation |
| يفقد عملاء قدامى بدون سبب | 🟡 Medium | Quarterly | None |
| لا يعرف متى يتواصل مع عميل متردد | 🟡 Medium | Weekly | Manual calendar |
| عملية استيراد جهات الاتصال معقدة | 🟡 Medium | One-time | Manual re-entry |
| لا يتذكر تفاصيل الاتفاقيات القديمة | 🟡 Medium | Monthly | Email search |

### 3.2 Jobs-To-Be-Done (JTBD)

```
When I receive a payment from a client,
I want to know instantly if there are other outstanding invoices for them,
So that I can follow up proactively.

When I open my client list on Monday morning,
I want to see who needs my attention today,
So that I don't miss follow-ups or let relationships go cold.

When I'm about to start a new project,
I want to quickly review my history with the client,
So that I can price and plan appropriately.
```

---

## 4. Feature Architecture

### 4.1 Feature Map (Complete)

```
CRM Module
│
├── 4.1  Client Directory (List, Search, Filter)
├── 4.2  Client Profile 360°
│   ├── Basic Information
│   ├── Financial Summary
│   ├── Projects & Invoices
│   ├── Activity Timeline
│   └── Private Notes
│
├── 4.3  Client Tagging System
│   ├── System Tags (predefined)
│   └── Custom Tags (user-defined)
│
├── 4.4  Import / Export
│   ├── CSV / Excel Import (with field mapping)
│   └── CSV / Excel / PDF / vCard Export
│
├── 4.5  Client Health Score
│   ├── Score Calculation Engine
│   └── Visual Indicators
│
├── 4.6  Activity & Timeline Engine
│   ├── Auto-logging (Observers)
│   └── Manual event logging
│
├── 4.7  Client Segmentation
│   ├── Prebuilt Segments
│   └── Custom Filter Builder
│
├── 4.8  Follow-up & Reminder System
│   ├── Manual Reminders
│   └── Smart Auto-reminders
│
├── 4.9  Private Notes & Attachments
│   ├── Rich text notes
│   └── File attachments
│
├── 4.10 Smart Notifications
│   ├── Payment-based triggers
│   └── Inactivity-based triggers
│
├── 4.11 Custom Fields
│   └── User-defined data fields per client
│
├── 4.12 Client Portal (V2)
│   ├── Invoice viewer
│   ├── Project progress
│   └── Online payment
│
└── 4.13 Communication Center (V3)
    ├── Email integration
    └── WhatsApp Business API
```

### 4.2 Feature Dependency Graph

```
Client Directory
    └── Client Profile 360°
            ├── Activity Timeline  ←  requires: Observers + Events
            ├── Health Score       ←  requires: Timeline + Financial data
            ├── Tagging System     ←  standalone
            ├── Private Notes      ←  standalone
            └── Segmentation       ←  requires: Tags + Health Score + Filters
                    └── Follow-up System  ←  requires: Segmentation
                            └── Client Portal  ←  requires: Signed URLs + Invoices
```

---

## 5. Technical Architecture

### 5.1 Service Layer Design

The CRM module follows a **Service + Action + Event** architecture. No fat controllers. No fat models.

```
app/
├── Services/
│   └── CRM/
│       ├── ClientService.php           # CRUD + business logic
│       ├── ClientTagService.php        # Tag management
│       ├── ClientHealthScoreService.php # Score computation
│       ├── ClientSegmentService.php    # Segmentation engine
│       ├── ClientImportService.php     # Import orchestration
│       ├── ClientExportService.php     # Export generation
│       ├── ClientFollowUpService.php   # Reminders + follow-ups
│       └── ClientPortalService.php    # Signed URL + token management
│
├── Actions/
│   └── Clients/
│       ├── CreateClientAction.php
│       ├── UpdateClientAction.php
│       ├── DeleteClientAction.php
│       ├── AssignTagAction.php
│       ├── RemoveTagAction.php
│       ├── LogClientActivityAction.php
│       ├── RecalculateHealthScoreAction.php
│       └── GeneratePortalTokenAction.php
│
├── Events/
│   └── Clients/
│       ├── ClientCreated.php
│       ├── ClientUpdated.php
│       ├── ClientTagAssigned.php
│       ├── ClientInvoicePaid.php
│       ├── ClientBecameInactive.php
│       └── ClientImportCompleted.php
│
├── Listeners/
│   └── Clients/
│       ├── LogClientCreationActivity.php
│       ├── RecalculateScoreOnPayment.php
│       ├── TriggerInactivityNotification.php
│       └── SendImportCompletionNotification.php
│
├── Observers/
│   ├── ClientObserver.php
│   ├── InvoiceObserver.php       # logs activity on client
│   └── ProjectObserver.php       # logs activity on client
│
├── Policies/
│   └── ClientPolicy.php          # Authorization
│
├── Jobs/
│   ├── ImportClientsJob.php
│   ├── ExportClientsJob.php
│   └── RecalculateAllHealthScoresJob.php
│
└── Notifications/
    ├── InactiveClientAlert.php
    ├── InvoiceOverdueClientAlert.php
    ├── FollowUpReminderNotification.php
    └── ImportCompletedNotification.php
```

### 5.2 Observer Pattern (Activity Auto-Logging)

```php
// ClientObserver.php
class ClientObserver
{
    public function created(Client $client): void
    {
        LogClientActivityAction::run($client, 'client_created', [
            'title' => 'تم إضافة العميل',
        ]);
    }
}

// InvoiceObserver.php — attaches to client timeline
class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        if ($invoice->client_id) {
            LogClientActivityAction::run($invoice->client, 'invoice_created', [
                'title'      => "فاتورة جديدة: {$invoice->number}",
                'amount'     => $invoice->total,
                'subject_id' => $invoice->id,
                'subject_type' => Invoice::class,
            ]);
        }
    }

    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
            event(new ClientInvoicePaid($invoice->client, $invoice));
        }
    }
}
```

### 5.3 Caching Strategy

```php
// ClientHealthScoreService
// Scores are expensive to compute — cache aggressively

Cache::remember(
    "client_health_score:{$client->id}",
    now()->addHours(6),
    fn () => $this->computeScore($client)
);

// Invalidated by:
// - Invoice paid event
// - New activity logged
// - Client updated

// Client list with segment filters — cache per user + filter hash
Cache::remember(
    "clients_list:{$userId}:".md5(serialize($filters)),
    now()->addMinutes(15),
    fn () => $this->buildQuery($filters)->paginate()
);
```

### 5.4 Event-Driven Architecture

```
[Invoice Paid]
     │
     ▼
ClientInvoicePaid Event
     │
     ├── RecalculateScoreOnPayment (Listener — sync)
     ├── LogClientActivityAction   (Listener — sync)
     └── TriggerFollowUpClear      (Listener — queued)

[Client Inactive for 90 days]
     │
     ▼
Scheduled Command: DetectInactiveClients
     │
     ├── ClientBecameInactive Event
     │       └── TriggerInactivityNotification (queued)
     └── Update client.last_activity_at
```

---

## 6. Database Design

### 6.1 Core Tables Schema

```sql
-- ═══════════════════════════════════════════
-- TABLE: clients
-- Primary entity. Scoped to user (tenant).
-- ═══════════════════════════════════════════
CREATE TABLE clients (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,           -- tenant scope
    ulid                CHAR(26) NOT NULL UNIQUE,           -- public-facing ID (portal URLs)

    -- Identity
    name                VARCHAR(150) NOT NULL,
    company_name        VARCHAR(150) NULL,
    email               VARCHAR(255) NULL,
    phone               VARCHAR(30) NULL,
    avatar_url          VARCHAR(500) NULL,
    website             VARCHAR(255) NULL,

    -- Location
    country_code        CHAR(2) NULL,
    city                VARCHAR(100) NULL,
    currency            CHAR(3) DEFAULT 'SAR',

    -- Relationship metadata
    source              ENUM('manual','import','referral','portal') DEFAULT 'manual',
    referred_by_client_id BIGINT UNSIGNED NULL,            -- self-referential for referrals
    acquisition_date    DATE NULL,

    -- Status & classification
    status              ENUM('active','inactive','prospect','archived') DEFAULT 'active',
    health_score        TINYINT UNSIGNED DEFAULT NULL,      -- 0-100, computed
    health_computed_at  TIMESTAMP NULL,

    -- Engagement tracking
    last_activity_at    TIMESTAMP NULL,
    last_invoice_at     TIMESTAMP NULL,
    last_payment_at     TIMESTAMP NULL,
    last_contacted_at   TIMESTAMP NULL,

    -- Financial aggregates (denormalized for performance)
    total_revenue       DECIMAL(15,2) DEFAULT 0.00,
    total_paid          DECIMAL(15,2) DEFAULT 0.00,
    total_outstanding   DECIMAL(15,2) DEFAULT 0.00,
    invoice_count       SMALLINT UNSIGNED DEFAULT 0,
    project_count       SMALLINT UNSIGNED DEFAULT 0,
    avg_payment_days    TINYINT UNSIGNED NULL,              -- avg days to pay

    -- Internal
    notes               TEXT NULL,
    is_archived         BOOLEAN DEFAULT FALSE,
    archived_at         TIMESTAMP NULL,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP NULL,                     -- soft delete

    -- Constraints
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_clients_referrer FOREIGN KEY (referred_by_client_id) REFERENCES clients(id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_clients_user_status   (user_id, status, is_archived),
    INDEX idx_clients_user_health   (user_id, health_score),
    INDEX idx_clients_last_activity (user_id, last_activity_at),
    INDEX idx_clients_email         (user_id, email),
    INDEX idx_clients_ulid          (ulid),
    FULLTEXT INDEX idx_clients_search (name, company_name, email, phone)
);

-- ═══════════════════════════════════════════
-- TABLE: client_tags
-- Defines available tags (system + user-created)
-- ═══════════════════════════════════════════
CREATE TABLE client_tags (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NULL,       -- NULL = system tag
    name        VARCHAR(60) NOT NULL,
    slug        VARCHAR(60) NOT NULL,       -- for programmatic access
    color       CHAR(7) NOT NULL,           -- HEX e.g. #2DCEA8
    icon        VARCHAR(20) NULL,           -- emoji or icon name
    description VARCHAR(255) NULL,
    is_system   BOOLEAN DEFAULT FALSE,
    sort_order  TINYINT UNSIGNED DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tags_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_tags_user_slug (user_id, slug),
    INDEX idx_tags_system (is_system)
);

-- ═══════════════════════════════════════════
-- TABLE: client_tag_assignments
-- Many-to-many: client ↔ tag
-- ═══════════════════════════════════════════
CREATE TABLE client_tag_assignments (
    client_id   BIGINT UNSIGNED NOT NULL,
    tag_id      BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,   -- which user assigned it
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (client_id, tag_id),
    CONSTRAINT fk_cta_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_cta_tag    FOREIGN KEY (tag_id) REFERENCES client_tags(id) ON DELETE CASCADE,
    INDEX idx_cta_tag (tag_id)
);

-- ═══════════════════════════════════════════
-- TABLE: client_activities
-- Polymorphic activity log for all client events
-- ═══════════════════════════════════════════
CREATE TABLE client_activities (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id    BIGINT UNSIGNED NOT NULL,
    user_id      BIGINT UNSIGNED NOT NULL,

    type         VARCHAR(50) NOT NULL,
    -- Allowed types:
    -- client_created | client_updated | tag_assigned | tag_removed
    -- invoice_created | invoice_paid | invoice_overdue | invoice_cancelled
    -- project_started | project_completed
    -- note_added | file_attached
    -- reminder_sent | email_sent | whatsapp_sent
    -- follow_up_scheduled | follow_up_completed
    -- portal_viewed | portal_invoice_downloaded
    -- custom (manual by user)

    title        VARCHAR(255) NOT NULL,
    body         TEXT NULL,

    -- Polymorphic subject (what triggered this event)
    subject_type VARCHAR(100) NULL,         -- e.g. App\Models\Invoice
    subject_id   BIGINT UNSIGNED NULL,

    metadata     JSON NULL,                 -- flexible payload per event type
    is_pinned    BOOLEAN DEFAULT FALSE,     -- pin important events to top
    is_private   BOOLEAN DEFAULT TRUE,      -- always true for notes; false for system events
    source       ENUM('system','user') DEFAULT 'system',

    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activities_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_activities_user   FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activities_client_type (client_id, type, created_at),
    INDEX idx_activities_subject     (subject_type, subject_id),
    INDEX idx_activities_pinned      (client_id, is_pinned)
);

-- ═══════════════════════════════════════════
-- TABLE: client_health_scores
-- Detailed score breakdown (separate from denormalized column)
-- ═══════════════════════════════════════════
CREATE TABLE client_health_scores (
    client_id           BIGINT UNSIGNED PRIMARY KEY,
    score               TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- 0-100 composite
    payment_score       TINYINT UNSIGNED DEFAULT 0,           -- 35% weight
    revenue_score       TINYINT UNSIGNED DEFAULT 0,           -- 25% weight
    project_score       TINYINT UNSIGNED DEFAULT 0,           -- 20% weight
    engagement_score    TINYINT UNSIGNED DEFAULT 0,           -- 10% weight
    reliability_score   TINYINT UNSIGNED DEFAULT 0,           -- 10% weight
    score_version       TINYINT UNSIGNED DEFAULT 1,           -- algorithm version
    computed_at         TIMESTAMP NOT NULL,

    CONSTRAINT fk_health_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- ═══════════════════════════════════════════
-- TABLE: client_follow_ups
-- Scheduled follow-up actions (manual + auto)
-- ═══════════════════════════════════════════
CREATE TABLE client_follow_ups (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id   BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    title       VARCHAR(255) NOT NULL,
    notes       TEXT NULL,
    due_at      DATETIME NOT NULL,
    status      ENUM('pending','completed','snoozed','cancelled') DEFAULT 'pending',
    priority    ENUM('low','medium','high') DEFAULT 'medium',
    source      ENUM('manual','auto','import') DEFAULT 'manual',
    snoozed_until DATETIME NULL,
    completed_at DATETIME NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_followup_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_followups_user_status (user_id, status, due_at),
    INDEX idx_followups_due (due_at, status)
);

-- ═══════════════════════════════════════════
-- TABLE: client_custom_fields_definitions
-- User-defined extra fields per account
-- ═══════════════════════════════════════════
CREATE TABLE client_field_definitions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    field_key   VARCHAR(60) NOT NULL,
    field_type  ENUM('text','number','date','boolean','select','url') NOT NULL,
    options     JSON NULL,                  -- for select type: ["option1","option2"]
    is_required BOOLEAN DEFAULT FALSE,
    sort_order  TINYINT UNSIGNED DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_fielddef_user_key (user_id, field_key)
);

-- ═══════════════════════════════════════════
-- TABLE: client_custom_field_values
-- Actual values per client per field
-- ═══════════════════════════════════════════
CREATE TABLE client_field_values (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id     BIGINT UNSIGNED NOT NULL,
    field_def_id  BIGINT UNSIGNED NOT NULL,
    value         TEXT NULL,

    PRIMARY KEY (client_id, field_def_id),
    CONSTRAINT fk_cfv_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_cfv_def    FOREIGN KEY (field_def_id) REFERENCES client_field_definitions(id) ON DELETE CASCADE
);

-- ═══════════════════════════════════════════
-- TABLE: client_attachments
-- Files attached to client profiles
-- ═══════════════════════════════════════════
CREATE TABLE client_attachments (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id   BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(255) NOT NULL,
    disk        VARCHAR(20) DEFAULT 's3',
    path        VARCHAR(500) NOT NULL,
    mime_type   VARCHAR(100) NULL,
    size        INT UNSIGNED NULL,          -- bytes
    category    ENUM('contract','id','other') DEFAULT 'other',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_attach_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_attach_client (client_id)
);

-- ═══════════════════════════════════════════
-- TABLE: client_portal_tokens
-- Signed access tokens for Client Portal
-- ═══════════════════════════════════════════
CREATE TABLE client_portal_tokens (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id   BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    token       CHAR(64) NOT NULL UNIQUE,   -- cryptographically random
    permissions JSON DEFAULT '["invoices.view","projects.view"]',
    ip_address  VARCHAR(45) NULL,           -- IP at generation time
    last_used_at TIMESTAMP NULL,
    expires_at  TIMESTAMP NOT NULL,
    revoked_at  TIMESTAMP NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_portal_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_portal_token (token),
    INDEX idx_portal_client (client_id)
);

-- ═══════════════════════════════════════════
-- TABLE: saved_segments
-- User-saved filter combinations
-- ═══════════════════════════════════════════
CREATE TABLE saved_segments (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    icon        VARCHAR(20) NULL,
    filters     JSON NOT NULL,              -- serialized FilterBuilder state
    is_pinned   BOOLEAN DEFAULT FALSE,
    client_count INT UNSIGNED DEFAULT 0,   -- cached count (refreshed on load)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_segments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ═══════════════════════════════════════════
-- TABLE: client_import_logs
-- Audit trail for every import operation
-- ═══════════════════════════════════════════
CREATE TABLE client_import_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    filename        VARCHAR(255) NOT NULL,
    total_rows      INT UNSIGNED DEFAULT 0,
    imported_count  INT UNSIGNED DEFAULT 0,
    duplicate_count INT UNSIGNED DEFAULT 0,
    error_count     INT UNSIGNED DEFAULT 0,
    status          ENUM('pending','processing','completed','failed') DEFAULT 'pending',
    error_details   JSON NULL,
    started_at      TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_import_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 6.2 Schema Design Decisions (Rationale)

| Decision | Reason |
|----------|--------|
| `ulid` on clients | Used in portal URLs — public-safe, non-sequential, non-guessable |
| Denormalized financial aggregates on clients | Avoid expensive joins on every list render; updated via Events |
| Separate `client_health_scores` table | Score breakdown logged separately; allows version tracking |
| `metadata JSON` on activities | Flexible payload per event type without schema changes |
| `client_field_definitions` + `client_field_values` | Entity-Attribute-Value for custom fields — flexible without schema migration |
| Polymorphic `subject_type/subject_id` on activities | Activities link to any model (Invoice, Project, etc.) |
| `saved_segments` with JSON filters | Allows complex multi-condition segment saving |
| Soft deletes on clients | Clients can be restored; financial history preserved |

### 6.3 System Tags Seeder

```php
// database/seeders/SystemClientTagsSeeder.php
$systemTags = [
    ['slug' => 'vip',         'name' => 'عميل ذهبي',       'color' => '#F59E0B', 'icon' => '⭐'],
    ['slug' => 'slow-payer',  'name' => 'يتأخر في الدفع',  'color' => '#EF4444', 'icon' => '🔴'],
    ['slug' => 'prospect',    'name' => 'عميل متردد',       'color' => '#F59E0B', 'icon' => '🟡'],
    ['slug' => 'active',      'name' => 'عميل نشط',         'color' => '#3B82F6', 'icon' => '🔵'],
    ['slug' => 'inactive',    'name' => 'غير نشط',           'color' => '#6B7280', 'icon' => '⚫'],
    ['slug' => 'referrer',    'name' => 'شريك / مرجعية',    'color' => '#8B5CF6', 'icon' => '🟣'],
    ['slug' => 'government',  'name' => 'جهة حكومية',       'color' => '#0EA5E9', 'icon' => '🏛️'],
    ['slug' => 'international','name' => 'عميل دولي',       'color' => '#10B981', 'icon' => '🌍'],
];
```

---

## 7. API Architecture

### 7.1 RESTful Endpoints

```
Client Core
──────────────────────────────────────────────────────────
GET    /api/v1/clients                    # List + filter + segment
POST   /api/v1/clients                    # Create
GET    /api/v1/clients/{ulid}             # Get profile 360°
PUT    /api/v1/clients/{ulid}             # Full update
PATCH  /api/v1/clients/{ulid}             # Partial update
DELETE /api/v1/clients/{ulid}             # Soft delete
POST   /api/v1/clients/{ulid}/restore     # Restore from trash

Tags
──────────────────────────────────────────────────────────
GET    /api/v1/client-tags                # List all tags (system + custom)
POST   /api/v1/client-tags                # Create custom tag
PUT    /api/v1/client-tags/{id}           # Update custom tag
DELETE /api/v1/client-tags/{id}           # Delete custom tag
POST   /api/v1/clients/{ulid}/tags/sync   # Sync tags (replace all)
POST   /api/v1/clients/{ulid}/tags/{id}   # Assign single tag
DELETE /api/v1/clients/{ulid}/tags/{id}   # Remove single tag

Activity Timeline
──────────────────────────────────────────────────────────
GET    /api/v1/clients/{ulid}/activities         # Paginated timeline
POST   /api/v1/clients/{ulid}/activities         # Manual log entry
PATCH  /api/v1/clients/{ulid}/activities/{id}    # Pin/unpin
DELETE /api/v1/clients/{ulid}/activities/{id}    # Delete user-added entry

Health Score
──────────────────────────────────────────────────────────
GET    /api/v1/clients/{ulid}/health-score        # Score + breakdown
POST   /api/v1/clients/{ulid}/health-score/recalculate

Follow-ups
──────────────────────────────────────────────────────────
GET    /api/v1/clients/{ulid}/follow-ups          # Per client
GET    /api/v1/follow-ups                         # All pending (across clients)
POST   /api/v1/clients/{ulid}/follow-ups
PATCH  /api/v1/follow-ups/{id}                    # Update status
DELETE /api/v1/follow-ups/{id}

Custom Fields
──────────────────────────────────────────────────────────
GET    /api/v1/client-fields                      # Field definitions
POST   /api/v1/client-fields
PUT    /api/v1/client-fields/{id}
DELETE /api/v1/client-fields/{id}
PATCH  /api/v1/clients/{ulid}/fields              # Save field values

Attachments
──────────────────────────────────────────────────────────
GET    /api/v1/clients/{ulid}/attachments
POST   /api/v1/clients/{ulid}/attachments         # Upload
DELETE /api/v1/clients/{ulid}/attachments/{id}
GET    /api/v1/clients/{ulid}/attachments/{id}/download  # Signed URL

Import / Export
──────────────────────────────────────────────────────────
POST   /api/v1/clients/import/preview             # Upload + return preview
POST   /api/v1/clients/import/confirm             # Execute confirmed import
GET    /api/v1/clients/import/logs                # Import history
GET    /api/v1/clients/export?format=csv|xlsx|pdf # Async export

Segmentation
──────────────────────────────────────────────────────────
POST   /api/v1/clients/segment/preview            # Live filter preview (no save)
GET    /api/v1/saved-segments                     # List saved segments
POST   /api/v1/saved-segments                     # Save current filters
PUT    /api/v1/saved-segments/{id}
DELETE /api/v1/saved-segments/{id}

Client Portal (public — no auth)
──────────────────────────────────────────────────────────
GET    /portal/{token}                            # Portal entry (validate token)
GET    /portal/{token}/invoices                   # Invoice list
GET    /portal/{token}/invoices/{id}/download     # Download PDF
GET    /portal/{token}/projects                   # Project progress
POST   /portal/{token}/pay/{invoice_id}           # Initiate payment
```

### 7.2 API Response Structure

```json
// GET /api/v1/clients/{ulid}
{
    "data": {
        "id": "01HXZ...",
        "name": "أحمد السالم",
        "company_name": "شركة الأفق",
        "email": "ahmed@ufuq.sa",
        "phone": "+966501234567",
        "status": "active",
        "health_score": 87,
        "currency": "SAR",
        "tags": [
            { "id": 1, "name": "عميل ذهبي", "color": "#F59E0B", "slug": "vip" }
        ],
        "financials": {
            "total_revenue": 45200.00,
            "total_paid": 42000.00,
            "total_outstanding": 3200.00,
            "invoice_count": 12,
            "avg_payment_days": 8
        },
        "engagement": {
            "last_activity_at": "2026-05-18T14:30:00Z",
            "last_invoice_at": "2026-05-10T00:00:00Z",
            "last_payment_at": "2026-05-15T00:00:00Z",
            "last_contacted_at": "2026-05-18T14:30:00Z"
        },
        "meta": {
            "project_count": 5,
            "follow_up_count": 1,
            "attachment_count": 3
        }
    }
}
```

### 7.3 Filter Query Schema

```
GET /api/v1/clients?filters[status]=active
                    &filters[tags][]=vip
                    &filters[tags][]=active
                    &filters[health_score][min]=70
                    &filters[last_activity][max_days]=30
                    &filters[revenue][min]=5000
                    &sort=health_score
                    &sort_dir=desc
                    &per_page=25
```

---

## 8. UI/UX Recommendations

### 8.1 Client List View

```
┌─────────────────────────────────────────────────────────────────┐
│  قائمة العملاء                              [+ عميل] [استيراد]  │
├─────────────────────────────────────────────────────────────────┤
│  [بحث: اسم أو إيميل أو شركة...]  [فلتر ▾]  [تصدير ▾]          │
│                                                                  │
│  الشرائح المحفوظة:                                              │
│  [الكل ٤٨]  [⭐ VIP ١٢]  [🔴 متأخرون ٥]  [💤 غير نشط ٨]  [+]  │
├─────────────────────────────────────────────────────────────────┤
│  □  الاسم              الوسوم        الصحة   الإيرادات  آخر نشاط │
│  ─────────────────────────────────────────────────────────────  │
│  □  أحمد السالم        ⭐ VIP        ██ 87   ٤٥,٢٠٠    ٢ أيام   │
│     شركة الأفق                                                   │
│  □  نورة الشمري        🟡 متردد      ██ 52   ١٢,٠٠٠    ١٨ يوم   │
│  □  خالد العتيبي       🔴 يتأخر      ██ 31   ٢٨,٥٠٠    ٣ أيام   │
│     Pixelate Agency                                              │
└─────────────────────────────────────────────────────────────────┘
```

**UX Notes:**
- Health Score shown as a colored mini-bar, not just a number
- Tags are clickable pills that filter on click
- Last activity uses relative time ("٢ أيام" not a full date)
- Bulk actions appear in a sticky bar when rows are selected

### 8.2 Client Profile 360° Layout

```
┌── Header ─────────────────────────────────────────────────────┐
│  [Avatar]  أحمد السالم · شركة الأفق    [⭐ VIP] [🔵 نشط]      │
│  ahmed@ufuq.sa · +966501234567          [تعديل] [+ نشاط] [⋮]  │
│                                                                 │
│  [صحة العميل: ████████░ 87]  [متوسط الدفع: ٨ أيام]            │
├── Quick Stats ────────────────────────────────────────────────┤
│  إجمالي الإيرادات   │  المدفوع      │  متبقي     │  مشاريع    │
│  ٤٥,٢٠٠ ر.س        │  ٤٢,٠٠٠ ر.س  │  ٣,٢٠٠ ر.س │     ٥      │
├── Tabs ───────────────────────────────────────────────────────┤
│  [النشاط]  [الفواتير]  [المشاريع]  [الملفات]  [المتابعة]      │
├── Activity Timeline ──────────────────────────────────────────┤
│  اليوم                                                         │
│  ├─ 💰 استُلم دفع ٣,٥٠٠ ر.س · فاتورة #INV-٢٢١               │
│  │                                                             │
│  أمس                                                           │
│  ├─ 📧 أُرسل تذكير بالدفع                                     │
│  ├─ 📝 ملاحظة: "طلب تمديد ٣ أيام إضافية"     [أنت]           │
│                                                                 │
│  الأسبوع الماضي                                                 │
│  ├─ 🚀 بدأ مشروع: موقع إلكتروني جديد                          │
│  └─ 📄 أُنشئت فاتورة #INV-٢٢١ · ١٢,٠٠٠ ر.س                  │
│                                                                 │
│  [+ إضافة حدث يدوي]                                            │
└───────────────────────────────────────────────────────────────┘
```

### 8.3 Tag Selection UX

```
// On client row or profile — clicking a tag chip opens:
┌─────────────────────────────┐
│  إضافة وسم                   │
│  [بحث في الوسوم...]          │
│  ─────────────────────────  │
│  ✅ ⭐ عميل ذهبي             │
│  ○  🔴 يتأخر في الدفع       │
│  ○  🟡 عميل متردد           │
│  ○  🔵 عميل نشط             │
│  ─────────────────────────  │
│  وسومك المخصصة              │
│  ○  🏛️ جهة حكومية           │
│  ─────────────────────────  │
│  [+ إنشاء وسم جديد]         │
└─────────────────────────────┘
```

### 8.4 Segmentation Builder UX

```
┌── بناء شريحة ─────────────────────────────────────────────────┐
│  إظهار العملاء الذين:                                          │
│                                                                 │
│  [الوسم] [هو] [متردد ▾]                              [× حذف]  │
│  [آخر نشاط] [أكبر من] [٣٠ يوم ▾]                   [× حذف]  │
│  [الإيرادات الإجمالية] [أكبر من] [5000 ر.س]          [× حذف]  │
│                                                                 │
│  [+ إضافة شرط]                                                  │
│                                                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━                    │
│  النتيجة: ٧ عملاء                        [معاينة] [حفظ الشريحة]│
└───────────────────────────────────────────────────────────────┘
```

### 8.5 Empty States

```
// Empty client list (new user)
┌──────────────────────────────────────┐
│            👥                        │
│   لا يوجد عملاء بعد                  │
│   أضف أول عميل وابدأ في تنظيم        │
│   أعمالك بشكل احترافي               │
│                                      │
│   [+ إضافة عميل]  [استيراد من ملف]   │
└──────────────────────────────────────┘

// Empty timeline
│            📋                        │
│   لا يوجد نشاط بعد                  │
│   سيظهر هنا كل تفاعل مع هذا العميل  │
│   [+ إضافة ملاحظة]                  │

// No follow-ups
│            ✅                        │
│   كل شيء منجز!                      │
│   لا متابعات معلقة اليوم            │
```

### 8.6 Onboarding Flow (New User)

```
Step 1: "هل لديك عملاء موجودون؟"
   → نعم → "استورد قائمتك" (CSV / from contacts)
   → لا  → "أضف أول عميل يدوياً"

Step 2: "عرّف عليك بالوسوم" (interactive demo)
   → 3-step tooltip tour of tagging

Step 3: "اضبط تذكيراتك الذكية" (تفعيل/تعطيل)
```

### 8.7 Responsive Behavior

| Screen | Behavior |
|--------|----------|
| Mobile < 640px | Single column; tags shown as colored dots; timeline collapsed |
| Tablet 640–1024px | Two-column profile; compact tags |
| Desktop > 1024px | Full sidebar + content layout; all data visible |

---

## 9. Automation & Workflow Engine

### 9.1 Trigger → Condition → Action Framework

```
TRIGGER                    CONDITION                   ACTION
──────────────────────────────────────────────────────────────
Invoice created         →  (always)                → Log to timeline
Invoice paid            →  (always)                → Log to timeline
                                                   → Recalculate health score
                                                   → Clear overdue reminder

Invoice not paid        →  > 7 days overdue        → Send reminder notification
                        →  > 14 days overdue       → Mark tag: slow-payer
                        →  > 30 days overdue       → Alert: critical overdue

Client last activity    →  > 60 days ago           → Suggest follow-up
                        →  > 90 days ago           → Auto-tag: inactive
                                                   → Send re-engagement prompt

Client payment avg      →  < 3 days consistently   → Auto-suggest: VIP tag
Project completed       →  Client has 3+ projects  → Suggest: upgrade to VIP tag

Follow-up due           →  Today                   → In-app + email notification
                        →  Tomorrow                → Preview reminder
```

### 9.2 Implementation: Automation Rules Engine

```php
// app/Services/CRM/AutomationRuleEngine.php

class AutomationRuleEngine
{
    protected array $rules = [
        InvoiceOverdueRule::class,
        ClientInactivityRule::class,
        FastPayerVipSuggestionRule::class,
        FollowUpDueRule::class,
    ];

    public function evaluate(Client $client): Collection
    {
        return collect($this->rules)
            ->flatMap(fn($rule) => app($rule)->evaluate($client))
            ->filter(fn($action) => $action !== null);
    }
}

// Example rule:
class ClientInactivityRule implements AutomationRule
{
    public function evaluate(Client $client): array
    {
        $daysSinceActivity = $client->last_activity_at?->diffInDays(now());

        if ($daysSinceActivity >= 90 && !$client->hasTag('inactive')) {
            return [
                new AssignTagAction($client, 'inactive'),
                new SendNotificationAction($client, 'client.inactive'),
            ];
        }

        return [];
    }
}
```

### 9.3 Scheduled Commands

```php
// app/Console/Commands/CRM/

DetectInactiveClientsCommand::class      // daily at 08:00
RecalculateHealthScoresCommand::class    // daily at 02:00
SendFollowUpRemindersCommand::class      // daily at 07:00
CleanExpiredPortalTokensCommand::class   // weekly
RefreshSegmentCountsCommand::class       // every 6 hours
```

---

## 10. Security Considerations

### 10.1 Authorization (Policy Layer)

```php
// app/Policies/ClientPolicy.php
class ClientPolicy
{
    public function view(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    public function update(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    public function managePortal(User $user, Client $client): bool
    {
        return $user->id === $client->user_id
            && $user->hasFeature('client_portal');  // plan-gated
    }

    public function import(User $user): bool
    {
        return $user->hasFeature('client_import');  // plan-gated
    }
}
```

### 10.2 Client Portal Security

```php
// Token generation
$token = hash('sha256', Str::random(60));

// Signed URL for file downloads (expire in 10 minutes)
Storage::temporaryUrl($path, now()->addMinutes(10));

// Token validation middleware
class ValidatePortalToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = ClientPortalToken::where('token', $request->route('token'))
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at')
            ->firstOrFail();

        // Log portal view in activity
        $token->update(['last_used_at' => now()]);

        $request->merge(['portal_client' => $token->client]);
        return $next($request);
    }
}
```

### 10.3 Audit Log

```php
// All sensitive operations are logged
// Extending the activity system for audit:

// Who viewed the client profile
// Who generated a portal token
// Who imported clients (with IP)
// Who exported data (with IP + timestamp)
// Who modified tags
// Who deleted a client

// Stored in client_activities with source='system'
// and type='audit_*'
```

### 10.4 Data Privacy Rules

| Rule | Implementation |
|------|----------------|
| Client data scoped to user | `user_id` on all tables + Policy enforcement |
| Export contains PII | Rate limited; logged; requires password confirmation on Business plan |
| Portal token expiry | Default 30 days; configurable; one-click revoke |
| Import file cleanup | Uploaded import files deleted after 24h |
| Attachment access | All files served via signed temporary URLs (never public) |
| GDPR "right to erasure" | Hard delete path available via admin command |

---

## 11. SaaS Scalability

### 11.1 Feature Gating by Plan

```php
// config/features.php
'client_import'       => ['pro', 'business'],
'client_export_excel' => ['pro', 'business'],
'client_portal'       => ['business'],
'custom_fields'       => ['pro', 'business'],
'saved_segments'      => ['pro', 'business'],
'client_tags_custom'  => ['pro', 'business'],  // system tags available on free
'automation_rules'    => ['business'],
'bulk_actions'        => ['pro', 'business'],
'client_health_score' => ['pro', 'business'],
'attachments'         => ['pro', 'business'],
'max_clients'         => [
    'free'     => 10,
    'pro'      => PHP_INT_MAX,
    'business' => PHP_INT_MAX,
],
'max_custom_tags'     => [
    'free'     => 0,
    'pro'      => 20,
    'business' => 100,
],
```

### 11.2 Usage Limits & Enforcement

```php
// Checked in ClientPolicy or Action layer
class CreateClientAction
{
    public function handle(User $user, array $data): Client
    {
        $limit = config("features.max_clients.{$user->plan}");
        $current = Client::where('user_id', $user->id)->count();

        if ($current >= $limit) {
            throw new PlanLimitExceededException(
                "يمكنك إضافة {$limit} عملاء على الخطة المجانية."
            );
        }

        return Client::create(['user_id' => $user->id, ...$data]);
    }
}
```

---

## 12. Multi-Tenant Architecture

### 12.1 Tenancy Model

دراهم uses **schema-shared multi-tenancy** with `user_id` as the tenant discriminator on every table. All queries MUST be scoped:

```php
// app/Models/Client.php
class Client extends Model
{
    use SoftDeletes;

    // Always scope to authenticated user
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
```

### 12.2 Team/Organization Support (Future)

When دراهم adds team accounts:

```sql
-- Future: organizations table
-- clients.user_id becomes clients.org_id (organization-level)
-- client_activities.user_id tracks WHICH team member did the action
-- Policies check: team membership + role (owner/manager/member)
```

This is why `assigned_by` is already on `client_tag_assignments` and `user_id` is on `client_activities` — designed for multi-user readiness.

---

## 13. Performance Considerations

### 13.1 Query Optimization

```php
// Client list: eager load only what's needed for the list view
Client::with(['tags'])          // avoid N+1 on tags
      ->withCount(['projects', 'invoices'])
      ->select(['id', 'ulid', 'name', 'company_name', 'email',
                'status', 'health_score', 'last_activity_at',
                'total_revenue', 'total_outstanding'])
      ->paginate(25);

// NEVER: Client::all() or loading full activities on list view
```

### 13.2 Denormalized Aggregates Strategy

Financial aggregates (`total_revenue`, `invoice_count`, etc.) on the `clients` table are updated via Events — **not computed on every query**:

```php
// Listener: UpdateClientFinancialAggregates
class UpdateClientFinancialAggregates
{
    public function handle(ClientInvoicePaid $event): void
    {
        DB::statement("
            UPDATE clients SET
                total_paid        = (SELECT COALESCE(SUM(total_paid), 0) FROM invoices WHERE client_id = ? AND deleted_at IS NULL),
                total_outstanding = (SELECT COALESCE(SUM(amount_due), 0) FROM invoices WHERE client_id = ? AND status = 'unpaid' AND deleted_at IS NULL),
                invoice_count     = (SELECT COUNT(*) FROM invoices WHERE client_id = ? AND deleted_at IS NULL),
                last_payment_at   = NOW()
            WHERE id = ?
        ", [$event->client->id, $event->client->id, $event->client->id, $event->client->id]);

        Cache::forget("client_health_score:{$event->client->id}");
    }
}
```

### 13.3 Fulltext Search

```php
// Use MySQL FULLTEXT index for search
Client::whereFullText(['name', 'company_name', 'email', 'phone'], $query)
      ->where('user_id', auth()->id())
      ->limit(20)
      ->get();
```

### 13.4 Large Import Handling

```
File size < 100 rows   → Sync processing (immediate)
File size 100-1000     → Queued job (high priority queue)
File size > 1000       → Chunked batches of 100 (Bus::batch)
```

---

## 14. Queue & Async Architecture

### 14.1 Queue Priority Map

```
Queue: crm-critical   → Follow-up reminders, overdue notifications
Queue: crm-default    → Health score recalculation, activity logging
Queue: crm-batch      → Import jobs, export jobs, bulk operations
Queue: crm-cleanup    → Token expiry, temp file deletion
```

### 14.2 Import Job Architecture

```php
// ImportClientsJob.php — chunked batch
class ImportClientsJob implements ShouldQueue
{
    public int $timeout = 300;
    public int $tries = 3;
    public int $backoff = 60;

    public function handle(): void
    {
        $log = ClientImportLog::find($this->logId);
        $log->update(['status' => 'processing', 'started_at' => now()]);

        $results = ['imported' => 0, 'duplicates' => 0, 'errors' => []];

        collect($this->rows)->chunk(50)->each(function ($chunk) use (&$results) {
            foreach ($chunk as $row) {
                try {
                    $existing = Client::where('user_id', $this->userId)
                        ->where(fn($q) => $q->where('email', $row['email'])
                                           ->orWhere('phone', $row['phone']))
                        ->first();

                    if ($existing) {
                        $results['duplicates']++;
                        continue;
                    }

                    Client::create(['user_id' => $this->userId, ...$row]);
                    $results['imported']++;
                } catch (\Exception $e) {
                    $results['errors'][] = ['row' => $row, 'error' => $e->getMessage()];
                }
            }
        });

        $log->update([
            'status'          => 'completed',
            'imported_count'  => $results['imported'],
            'duplicate_count' => $results['duplicates'],
            'error_details'   => $results['errors'],
            'completed_at'    => now(),
        ]);

        $this->user->notify(new ImportCompletedNotification($log));
    }
}
```

### 14.3 Export Job Architecture

```php
// ExportClientsJob.php
// Large exports run async → file stored in S3 → user gets download link via notification

class ExportClientsJob implements ShouldQueue
{
    public function handle(ClientExportService $exportService): void
    {
        $path = $exportService->generate(
            userId: $this->userId,
            format: $this->format,
            filters: $this->filters
        );

        // Store signed download URL, expire in 24h
        $signedUrl = Storage::temporaryUrl($path, now()->addHours(24));

        $this->user->notify(new ExportReadyNotification($signedUrl));
    }
}
```

---

## 15. AI & Smart Features

### 15.1 Opportunities Map

| Feature | Intelligence Type | Input Data | Output |
|---------|------------------|------------|--------|
| Health Score | Rule-based ML | Payment history, activity, revenue | 0-100 score |
| Smart Tag Suggestion | Pattern matching | Payment speed, invoice frequency | Tag recommendation |
| Churn Risk Detection | Predictive | Inactivity + payment trends | Risk alert |
| Follow-up Message Generator | LLM (GPT/Claude) | Client history, context | Draft follow-up message |
| Revenue Forecast per Client | Time series | Historical invoices | Projected next invoice |
| Best Time to Send Invoice | Behavioral | Payment patterns | Optimal send time |
| Duplicate Detection on Import | Fuzzy matching | Name, email, phone similarity | Match confidence score |

### 15.2 Smart Tag Auto-Assignment Algorithm

```php
class SmartTagSuggestionService
{
    public function suggest(Client $client): array
    {
        $suggestions = [];

        // VIP: pays within 3 days, >3 invoices paid, zero overdue
        if ($client->avg_payment_days <= 3
            && $client->invoice_count >= 3
            && $client->total_outstanding == 0) {
            $suggestions[] = ['tag' => 'vip', 'confidence' => 0.92];
        }

        // Slow payer: avg > 21 days across 2+ invoices
        if ($client->avg_payment_days > 21 && $client->invoice_count >= 2) {
            $suggestions[] = ['tag' => 'slow-payer', 'confidence' => 0.85];
        }

        // Inactive: no activity in 90 days
        if ($client->last_activity_at?->lt(now()->subDays(90))) {
            $suggestions[] = ['tag' => 'inactive', 'confidence' => 0.95];
        }

        return $suggestions;
    }
}
```

### 15.3 Churn Risk Score (Future V2)

```
Risk Factors (weighted):
  - Days since last invoice          30%
  - Invoice frequency decline        25%
  - Payment delay increase trend     20%
  - No active project                15%
  - No response to last reminder     10%

Output:
  Low Risk:    green badge
  Medium Risk: yellow badge + suggested action
  High Risk:   red badge + automatic follow-up reminder
```

### 15.4 AI Follow-up Message Draft (Future V3)

```
// User clicks "اقتراح رسالة متابعة" on a client with overdue invoice

Input to LLM:
- Client name, relationship duration
- Invoice amount + days overdue
- Previous payment history (fast/slow)
- Previous communication tone (formal/casual)

Output:
Arabic draft message, adjustable before sending
```

---

## 16. Roadmap & Priority Matrix

### Phase 0 — Foundation (Already Built)
> **Status:** ✅ Complete

- Client model, migration, Filament resource
- Basic CRUD (create, read, update, delete)
- Client linked to projects and invoices

### Phase 1 — MVP CRM (Sprint 1–2)
> **Why:** Immediate user value. Solves the #1 pain: "I don't know my clients well enough."  
> **Impact:** 🔴 High — directly increases daily active usage  
> **Complexity:** 🟡 Medium  
> **Dependencies:** None (standalone features)

| # | Feature | Effort | Priority |
|---|---------|--------|----------|
| 1.1 | System Tags (6 predefined) | 2 days | 🔴 Must |
| 1.2 | Custom Tags (user-defined) | 1 day | 🔴 Must |
| 1.3 | Tag UI on client list + profile | 1 day | 🔴 Must |
| 1.4 | Private Notes per client | 1 day | 🔴 Must |
| 1.5 | CSV Export (basic) | 1 day | 🟠 Should |
| 1.6 | CSV Import + field mapping | 3 days | 🟠 Should |
| 1.7 | Import preview + result report | 2 days | 🟠 Should |

**Exit Criteria:** User can tag clients, add notes, import and export.

### Phase 2 — Intelligence Layer (Sprint 3–4)
> **Why:** Transforms the product from "data storage" to "business intelligence".  
> **Impact:** 🟠 High — drives upgrade from Free → Pro  
> **Complexity:** 🟠 Medium-High  
> **Dependencies:** Activity logging must be in place

| # | Feature | Effort | Priority |
|---|---------|--------|----------|
| 2.1 | Activity Timeline (auto-logging) | 3 days | 🔴 Must |
| 2.2 | Manual event logging | 1 day | 🔴 Must |
| 2.3 | Client Profile 360° redesign | 3 days | 🔴 Must |
| 2.4 | Health Score engine | 3 days | 🟠 Should |
| 2.5 | Health Score UI | 1 day | 🟠 Should |
| 2.6 | Financial aggregates (denormalized) | 2 days | 🔴 Must |
| 2.7 | Excel + PDF Export | 2 days | 🟡 Could |

**Exit Criteria:** Client profile shows complete history + health score.

### Phase 3 — CRM Power Features (Sprint 5–6)
> **Why:** Segmentation + Follow-ups turn دراهم into a proactive sales tool.  
> **Impact:** 🟠 High — Business plan upsell trigger  
> **Complexity:** 🔴 High  
> **Dependencies:** Tags, Health Score, Timeline

| # | Feature | Effort | Priority |
|---|---------|--------|----------|
| 3.1 | Follow-up system (manual reminders) | 3 days | 🔴 Must |
| 3.2 | Smart auto-reminders (automation rules) | 4 days | 🟠 Should |
| 3.3 | Prebuilt segments (4 default) | 2 days | 🔴 Must |
| 3.4 | Custom filter builder (Segmentation) | 4 days | 🟠 Should |
| 3.5 | Saved segments | 2 days | 🟡 Could |
| 3.6 | Custom fields (user-defined) | 3 days | 🟡 Could |
| 3.7 | Client Attachments | 2 days | 🟡 Could |

**Exit Criteria:** User can segment clients, set follow-ups, and get proactive alerts.

### Phase 4 — Client Portal (Sprint 7–8)
> **Why:** Differentiator feature. No Arabic SMB tool offers this.  
> **Impact:** 🟢 High — Business plan exclusive  
> **Complexity:** 🔴 High  
> **Dependencies:** Invoices, Projects, Signed URLs

| # | Feature | Effort | Priority |
|---|---------|--------|----------|
| 4.1 | Portal token generation | 1 day | 🔴 Must |
| 4.2 | Portal views (invoices, projects) | 4 days | 🔴 Must |
| 4.3 | PDF download via signed URL | 2 days | 🔴 Must |
| 4.4 | Portal activity logging | 1 day | 🟠 Should |
| 4.5 | Portal link sharing UI | 1 day | 🔴 Must |
| 4.6 | Token management (revoke, extend) | 1 day | 🟠 Should |

**Exit Criteria:** User can share a secure link; client can view invoices and download PDFs.

### Phase 5 — Enterprise CRM (Sprint 9+)
> **Why:** Long-term retention and Business→Enterprise upsell  
> **Impact:** 🟢 Medium-High  
> **Complexity:** 🔴 Very High

| Feature | Notes |
|---------|-------|
| Email integration | Send invoices/reminders from دراهم directly |
| WhatsApp Business API | Arabic market primary channel |
| Online payment via portal | Stripe / Moyasar / HyperPay |
| AI follow-up message drafts | Claude/GPT integration |
| Churn risk detection | ML scoring |
| Team access to CRM | Multi-user org accounts |
| API webhooks | Zapier / Make.com integration |

---

## 17. Future Expansion Strategy

### 17.1 CRM → Full Business Operating System

```
Current:         Financial tracker + basic clients
Phase 1-3:       CRM with intelligence
Phase 4:         Client-facing portal
Phase 5:         Communication hub
Phase 6:         Business automation platform
Phase 7:         API ecosystem + integrations
```

### 17.2 Platform Extension Opportunities

| Opportunity | Market | Complexity |
|-------------|--------|------------|
| **White-label CRM** for agencies | B2B | Medium |
| **Accountant access** (view-only reports) | SMB | Low |
| **Client signing** (e-signature on contracts) | Legal | High |
| **Project collaboration** (client can comment) | Freelance | Medium |
| **Invoice payment tracking** via bank sync | FinTech | Very High |
| **Referral program tracking** (client referrals) | Growth | Low |

### 17.3 API Ecosystem Vision

```
V1 API  →  دراهم internal use only (current)
V2 API  →  Public read API (clients can pull their data)
V3 API  →  Webhook system (push events to external tools)
V4 API  →  Full developer API + Zapier/Make integration
```

### 17.4 Mobile Strategy

The CRM module must be mobile-first in later phases:
- Quick client lookup (name/phone search)
- One-tap: add note, log call, create invoice
- Push notifications for follow-ups and overdue alerts
- PWA first → native app (React Native) when user base justifies

---

## Appendix A — System Tags Reference

| Slug | Name (AR) | Color | Auto-assigned? | Condition |
|------|-----------|-------|----------------|-----------|
| `vip` | عميل ذهبي | `#F59E0B` | ✅ Suggested | avg_payment_days ≤ 3, invoices ≥ 3 |
| `slow-payer` | يتأخر في الدفع | `#EF4444` | ✅ Auto | avg_payment_days > 21 |
| `prospect` | عميل متردد | `#EAB308` | ❌ Manual | — |
| `active` | عميل نشط | `#3B82F6` | ✅ Auto | has open project |
| `inactive` | غير نشط | `#6B7280` | ✅ Auto | no activity > 90 days |
| `referrer` | شريك / مرجعية | `#8B5CF6` | ❌ Manual | — |
| `government` | جهة حكومية | `#0EA5E9` | ❌ Manual | — |
| `international` | عميل دولي | `#10B981` | ❌ Manual | — |

---

## Appendix B — Health Score Calculation

```
Score = (payment_score × 0.35)
      + (revenue_score × 0.25)
      + (project_score × 0.20)
      + (engagement_score × 0.10)
      + (reliability_score × 0.10)

payment_score:
  avg_payment_days = 0-3d  → 100
  avg_payment_days = 4-7d  → 85
  avg_payment_days = 8-14d → 65
  avg_payment_days = 15-30d→ 40
  avg_payment_days > 30d   → 10
  no invoices yet          → 50 (neutral)

revenue_score:
  total_paid / (avg_monthly_revenue × 3) × 100 (capped at 100)

project_score:
  completed_projects / total_projects × 100

engagement_score:
  last_activity_at days ago:
    0-7   → 100
    8-30  → 75
    31-60 → 50
    61-90 → 25
    > 90  → 0

reliability_score:
  0 overdue invoices      → 100
  1 overdue invoice       → 50
  2+ overdue invoices     → 0
```

---

## Appendix C — Activity Type Reference

| Type | Trigger | Source |
|------|---------|--------|
| `client_created` | Client added | System |
| `client_updated` | Client edited | System |
| `tag_assigned` | Tag added | User/System |
| `tag_removed` | Tag removed | User/System |
| `invoice_created` | Invoice linked to client | System |
| `invoice_sent` | Invoice emailed | System |
| `invoice_paid` | Invoice marked paid | System |
| `invoice_overdue` | Auto-detected | System |
| `project_started` | Project created | System |
| `project_completed` | Project marked done | System |
| `note_added` | User writes note | User |
| `file_attached` | File uploaded | User |
| `reminder_sent` | Auto-reminder fired | System |
| `follow_up_created` | Follow-up scheduled | User |
| `follow_up_completed` | Follow-up marked done | User |
| `portal_viewed` | Client opened portal | System |
| `portal_invoice_downloaded` | Client downloaded PDF | System |
| `custom` | Manual user log | User |

---

*📁 Document: `docs/CLIENTS-CRM-SPEC.md`*  
*🏢 دراهم — Financial & Business SaaS Platform*  
*🔒 Internal Use Only — Confidential*
