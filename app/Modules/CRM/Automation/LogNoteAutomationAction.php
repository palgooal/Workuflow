<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;
use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Enums\ActivityType;
use Illuminate\Support\Facades\Log;

/**
 * LogNoteAutomationAction — تسجيل ملاحظة في سجل نشاط العميل
 *
 * params: { "note": "تم تطبيق قاعدة الأتمتة تلقائياً" }
 */
class LogNoteAutomationAction extends BaseAutomationAction
{
    public function __construct(
        private readonly LogClientActivityAction $logAction,
    ) {}

    public static function type(): string  { return 'log_note'; }
    public static function label(): string { return 'تسجيل ملاحظة'; }

    public function execute(Client $client, int $userId, array $params = []): void
    {
        $note = $params['note'] ?? 'تم تطبيق قاعدة أتمتة تلقائياً';

        $this->logAction->execute(
            clientId:    $client->id,
            userId:      $userId,
            type:        ActivityType::NoteAdded,
            description: $note,
            metadata:    ['source' => 'automation'],
        );

        Log::info("LogNoteAutomationAction: logged note for client {$client->id}");
    }
}
