<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;

/**
 * BaseAutomationAction — الـ Base Class لكل Actions الأتمتة
 *
 * Sprint 6 — S6.2
 *
 * كل Action تمتد هذا الـ class وتنفّذ:
 *   - execute(Client, int $userId, array $params): void  → تنفيذ الإجراء
 *   - canExecute(Client, array $params): bool             → guard قبل التنفيذ
 */
abstract class BaseAutomationAction
{
    /**
     * نوع الـ Action (يُستخدم في automation_rules.actions JSON)
     */
    abstract public static function type(): string;

    /**
     * وصف مختصر للعرض في الـ UI
     */
    abstract public static function label(): string;

    /**
     * تنفيذ الإجراء
     */
    abstract public function execute(Client $client, int $userId, array $params = []): void;

    /**
     * Guard: هل يمكن تنفيذ الإجراء على هذا العميل؟
     * يُمنع التنفيذ إذا رجع false (مثلاً: العميل مؤرشف)
     */
    public function canExecute(Client $client, array $params = []): bool
    {
        return !$client->is_archived;
    }

    /**
     * جميع الـ Actions المتاحة
     * @return array<string, class-string<BaseAutomationAction>>
     */
    public static function all(): array
    {
        return [
            AssignTagAutomationAction::type()        => AssignTagAutomationAction::class,
            CreateFollowUpAutomationAction::type()   => CreateFollowUpAutomationAction::class,
            SendNotificationAutomationAction::type() => SendNotificationAutomationAction::class,
            UpdateStatusAutomationAction::type()     => UpdateStatusAutomationAction::class,
            LogNoteAutomationAction::type()          => LogNoteAutomationAction::class,
        ];
    }

    /**
     * إنشاء instance من النوع
     */
    public static function make(string $type): ?self
    {
        $class = static::all()[$type] ?? null;
        return $class ? app($class) : null;
    }
}
