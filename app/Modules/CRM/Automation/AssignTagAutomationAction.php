<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;
use App\Modules\CRM\Actions\Tag\AssignTagAction;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Facades\Log;

/**
 * AssignTagAutomationAction — تعيين وسم على العميل
 *
 * params: { "tag_slug": "vip" }
 */
class AssignTagAutomationAction extends BaseAutomationAction
{
    public function __construct(
        private readonly AssignTagAction $assignTagAction,
    ) {}

    public static function type(): string  { return 'assign_tag'; }
    public static function label(): string { return 'تعيين وسم'; }

    public function execute(Client $client, int $userId, array $params = []): void
    {
        $tagSlug = $params['tag_slug'] ?? null;
        if (!$tagSlug) {
            Log::warning("AssignTagAutomationAction: missing tag_slug for client {$client->id}");
            return;
        }

        $tag = ClientTag::where('slug', $tagSlug)
                        ->where(fn ($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
                        ->first();

        if (!$tag) {
            Log::warning("AssignTagAutomationAction: tag '{$tagSlug}' not found for user {$userId}");
            return;
        }

        // لا تُعيد التعيين إذا كان العميل يحمل الوسم مسبقاً
        if ($client->tags()->where('client_tags.id', $tag->id)->exists()) {
            return;
        }

        $this->assignTagAction->run($client, $tag, $userId);
        Log::info("AssignTagAutomationAction: assigned '{$tagSlug}' to client {$client->id}");
    }
}
