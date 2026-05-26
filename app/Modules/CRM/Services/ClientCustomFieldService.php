<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Models\ClientFieldDefinition;
use App\Modules\CRM\Models\ClientFieldValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClientCustomFieldService
{
    // ==================== CRUD للحقول ====================

    public function create(int $userId, array $data): ClientFieldDefinition
    {
        return DB::transaction(function () use ($userId, $data) {
            $nextOrder = ClientFieldDefinition::where('user_id', $userId)->max('sort_order') ?? 0;

            return ClientFieldDefinition::create([
                'user_id'      => $userId,
                'label'        => $data['label'],
                'key'          => $data['key'],
                'type'         => $data['type'] ?? 'text',
                'options'      => $data['options'] ?? null,
                'is_required'  => $data['is_required'] ?? false,
                'is_visible'   => $data['is_visible'] ?? true,
                'sort_order'   => $nextOrder + 1,
            ]);
        });
    }

    public function update(ClientFieldDefinition $field, array $data): ClientFieldDefinition
    {
        $field->update(array_filter([
            'label'       => $data['label']       ?? null,
            'type'        => $data['type']         ?? null,
            'options'     => $data['options']      ?? null,
            'is_required' => isset($data['is_required']) ? (bool) $data['is_required'] : null,
            'is_visible'  => isset($data['is_visible'])  ? (bool) $data['is_visible']  : null,
        ], fn ($v) => $v !== null));

        return $field->refresh();
    }

    public function destroy(ClientFieldDefinition $field): void
    {
        DB::transaction(function () use ($field) {
            // حذف جميع القيم المرتبطة أولاً
            ClientFieldValue::where('field_definition_id', $field->id)->delete();
            $field->delete();
        });
    }

    /**
     * إعادة ترتيب الحقول
     */
    public function reorder(int $userId, array $orderedIds): void
    {
        DB::transaction(function () use ($userId, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                ClientFieldDefinition::where('id', $id)
                    ->where('user_id', $userId)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    // ==================== قيم الحقول ====================

    public function saveValue(Client $client, ClientFieldDefinition $field, mixed $value): ClientFieldValue
    {
        return ClientFieldValue::updateOrCreate(
            [
                'client_id'           => $client->id,
                'field_definition_id' => $field->id,
            ],
            ['value' => $value]
        );
    }

    // ==================== Queries ====================

    public function forUser(int $userId): Collection
    {
        return ClientFieldDefinition::where('user_id', $userId)
            ->orderBy('sort_order')
            ->get();
    }

    public function valuesForClient(Client $client): Collection
    {
        return ClientFieldValue::where('client_id', $client->id)
            ->with('fieldDefinition')
            ->get();
    }
}
