<?php

namespace App\Modules\CRM\Actions\Tag;

use App\Models\Client;
use App\Modules\CRM\Events\ClientTagAssigned;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Facades\DB;

/**
 * AssignTagAction — تعيين وسم واحد لعميل
 *
 * - يتجاهل التعيين إذا كان الوسم مرتبطاً مسبقاً (idempotent)
 * - يُطلق ClientTagAssigned بعد Commit (C-01)
 */
class AssignTagAction
{
    public function execute(Client $client, ClientTag $tag, int $actorId): void
    {
        // فحص إذا الوسم مرتبط مسبقاً
        if ($client->tags()->where('client_tags.id', $tag->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($client, $tag, $actorId): void {
            $client->tags()->attach($tag->id, [
                'assigned_by' => $actorId,
                'assigned_at' => now(),
            ]);

            event(new ClientTagAssigned($client, $tag, $actorId));
        });
    }
}
