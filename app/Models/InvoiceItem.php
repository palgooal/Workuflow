<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            // ⚠️ decimal:3 لدعم عملات الفلس (JOD/KWD/BHD/OMR) — راجع Invoice::casts()
            'quantity'   => 'decimal:3',
            'unit_price' => 'decimal:3',
            'total'      => 'decimal:3',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
