<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Modules\CRM\Enums\HealthScoreGrade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientHealthScore extends Model
{
    protected $table = 'client_health_scores';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'score',
        'factors',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'score'     => 'integer',
            'factors'   => 'array',
            'scored_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $score) {
            if (empty($score->scored_at)) {
                $score->scored_at = now();
            }
        });
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ==================== Helpers ====================

    public function grade(): HealthScoreGrade
    {
        return HealthScoreGrade::fromScore($this->score);
    }

    /** نسبة كل عامل من العوامل الخمسة */
    public function factorBreakdown(): array
    {
        return array_map(
            fn ($v) => round($v * 100),
            $this->factors ?? []
        );
    }
}
