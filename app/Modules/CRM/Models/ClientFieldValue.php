<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFieldValue extends Model
{
    protected $table = 'client_field_values';

    protected $fillable = [
        'client_id',
        'field_definition_id',
        'value',
    ];

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ClientFieldDefinition::class, 'field_definition_id');
    }

    // ==================== Helpers ====================

    /** القيمة المُحوَّلة حسب نوع الحقل */
    public function castValue(): mixed
    {
        $type = $this->definition?->type ?? 'text';

        return match($type) {
            'number'  => is_numeric($this->value) ? (float) $this->value : null,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'date'    => $this->value ? \Carbon\Carbon::parse($this->value) : null,
            default   => $this->value,
        };
    }
}
