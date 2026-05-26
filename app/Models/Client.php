<?php

namespace App\Models;

use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Enums\HealthScoreGrade;
use App\Modules\CRM\Models\ClientActivity;
use App\Modules\CRM\Models\ClientAttachment;
use App\Modules\CRM\Models\ClientFieldValue;
use App\Modules\CRM\Models\ClientFollowUp;
use App\Modules\CRM\Models\ClientHealthScore;
use App\Modules\CRM\Models\ClientPortalToken;
use App\Modules\CRM\Models\ClientTag;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Client Model — CRM V2
 * المرجع: docs/CLIENTS-CRM-SPEC-V2.md — Sprint 1, S1.3
 */
class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id', 'public_id',
        'name', 'phone', 'email', 'company',
        'position', 'website', 'address', 'city', 'country',
        'notes',
        'is_active',
        'status', 'source', 'is_archived',
        'total_revenue', 'total_paid', 'invoice_count',
        'health_score', 'last_payment_at', 'last_contact_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'is_archived'     => 'boolean',
            'status'          => ClientStatus::class,
            'source'          => ClientSource::class,
            'total_revenue'   => 'decimal:2',
            'total_paid'      => 'decimal:2',
            'invoice_count'   => 'integer',
            'health_score'    => 'integer',
            'last_payment_at' => 'datetime',
            'last_contact_at' => 'datetime',
        ];
    }

    // ==================== Boot ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $client) {
            if (empty($client->public_id)) {
                $client->public_id = Str::ulid()->toString();
            }
            if (empty($client->status)) {
                $client->status = $client->is_active
                    ? ClientStatus::Active
                    : ClientStatus::Inactive;
            }
        });
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientTag::class,
            'client_tag_assignments',
            'client_id',
            'tag_id'
        )->withPivot(['assigned_at', 'assigned_by'])
         ->orderBy('priority');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class)
                    ->orderByDesc('occurred_at');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(ClientFollowUp::class);
    }

    public function pendingFollowUps(): HasMany
    {
        return $this->hasMany(ClientFollowUp::class)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->orderBy('due_at');
    }

    public function healthScores(): HasMany
    {
        return $this->hasMany(ClientHealthScore::class)
                    ->orderByDesc('scored_at');
    }

    public function latestHealthScore(): HasOne
    {
        return $this->hasOne(ClientHealthScore::class)
                    ->latestOfMany('scored_at');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClientAttachment::class)
                    ->orderByDesc('created_at');
    }

    public function portalTokens(): HasMany
    {
        return $this->hasMany(ClientPortalToken::class);
    }

    public function activePortalTokens(): HasMany
    {
        return $this->hasMany(ClientPortalToken::class)
                    ->where('expires_at', '>', now());
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', ClientStatus::Active->value)
                     ->where('is_archived', false);
    }

    public function scopeProspect($query)
    {
        return $query->where('status', ClientStatus::Prospect->value)
                     ->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeWithPendingFollowUps($query)
    {
        return $query->whereHas('followUps', fn ($q) =>
            $q->whereIn('status', ['pending', 'overdue'])
        );
    }

    public function scopeWithHealthScore($query)
    {
        return $query->with('latestHealthScore');
    }

    public function scopeWithTags($query)
    {
        return $query->with('tags');
    }

    // ==================== Accessors ====================

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company
                ? "{$this->name} ({$this->company})"
                : $this->name
        );
    }

    protected function healthGrade(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->health_score !== null
                ? HealthScoreGrade::fromScore($this->health_score)
                : null
        );
    }

    protected function isVip(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->relationLoaded('tags')
                && $this->tags->contains(fn ($t) => $t->slug === 'vip')
        );
    }

    protected function outstandingAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, (float) $this->total_revenue - (float) $this->total_paid)
        );
    }

    // ==================== Helpers ====================

    /** للتوافق مع الكود القديم */
    public function getDisplayNameAttribute(): string
    {
        return $this->company
            ? "{$this->name} ({$this->company})"
            : $this->name;
    }

    public function touchLastContact(): void
    {
        $this->update(['last_contact_at' => now()]);
    }

    public function hasActivePortal(): bool
    {
        return $this->activePortalTokens()->exists();
    }
}
