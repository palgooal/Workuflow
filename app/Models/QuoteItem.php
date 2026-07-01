<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id', 'description', 'quantity', 'unit_price', 'total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            // ⚠️ decimal:3 لدعم عملات الفلس (JOD/KWD/BHD/OMR) — راجع Quote::casts()
            'quantity'   => 'decimal:3',
            'unit_price' => 'decimal:3',
            'total'      => 'decimal:3',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
