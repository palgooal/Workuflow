<?php

namespace App\Modules\CRM\Actions\Tag;

use App\Models\Client;
use App\Modules\CRM\Events\ClientTagRemoved;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Facades\DB;

/**
 * RemoveTagAction — إزالة وسم من عميل
 */
class RemoveTagAction
{
    public function execute(Client $client, ClientTag $tag, int $actorId): void
    {
        if (! $client->tags()->where('client_tags.id', $tag->id)->exists()) {
            return; // غير مرتبط — لا شيء
        }

        DB::transaction(function () use ($client, $tag, $actorId): void {
            $client->tags()->detach($tag->id);

            event(new ClientTagRemoved($client, $tag, $actorId));
        });
    }
}
