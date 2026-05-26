<?php

namespace App\Modules\CRM\Actions\Activity;

use App\Modules\CRM\Enums\ActivityType;
use App\Modules\CRM\Models\ClientActivity;

/**
 * LogClientActivityAction — تسجيل نشاط على العميل
 *
 * C-01: يُستدعى دائماً من داخل Listener بـ $afterCommit = true
 * لا تستدعِه مباشرةً من داخل Transaction.
 */
class LogClientActivityAction
{
    public function execute(
        int          $clientId,
        int          $userId,
        ActivityType $type,
        ?string      $description = null,
        array        $metadata    = [],
    ): ClientActivity {
        return ClientActivity::create([
            'client_id'   => $clientId,
            'user_id'     => $userId,
            'type'        => $type->value,
            'description' => $description ?? $type->label(),
            'metadata'    => ! empty($metadata) ? $metadata : null,
            'occurred_at' => now(),
        ]);
    }

    // ==================== Shorthand Helpers ====================

    public function clientCreated(int $clientId, int $userId, string $clientName): ClientActivity
    {
        return $this->execute(
            clientId:    $clientId,
            userId:      $userId,
            type:        ActivityType::ClientCreated,
            description: "تم إنشاء العميل: {$clientName}",
            metadata:    ['client_name' => $clientName],
        );
    }

    public function tagAssigned(int $clientId, int $userId, string $tagName): ClientActivity
    {
        return $this->execute(
            clientId:    $clientId,
            userId:      $userId,
            type:        ActivityType::TagAssigned,
            description: "تم تعيين الوسم: {$tagName}",
            metadata:    ['tag_name' => $tagName],
        );
    }

    public function tagRemoved(int $clientId, int $userId, string $tagName): ClientActivity
    {
        return $this->execute(
            clientId:    $clientId,
            userId:      $userId,
            type:        ActivityType::TagRemoved,
            description: "تمت إزالة الوسم: {$tagName}",
            metadata:    ['tag_name' => $tagName],
        );
    }

    public function statusChanged(int $clientId, int $userId, string $from, string $to): ClientActivity
    {
        return $this->execute(
            clientId:    $clientId,
            userId:      $userId,
            type:        ActivityType::StatusChanged,
            description: "تغيّرت الحالة من {$from} إلى {$to}",
            metadata:    ['from' => $from, 'to' => $to],
        );
    }

    public function noteAdded(int $clientId, int $userId, string $preview): ClientActivity
    {
        return $this->execute(
            clientId:    $clientId,
            userId:      $userId,
            type:        ActivityType::NoteAdded,
            description: "ملاحظة: " . \Illuminate\Support\Str::limit($preview, 80),
        );
    }
}
