<?php

namespace App\Filament\Widgets;

use App\Services\Backup\BackupMonitoringService;
use Filament\Widgets\Widget;

/**
 * BackupMonitoringWidget — "Backup Monitoring" (المرحلة السادسة، الجزء
 * الأول). عرض فقط — لا Polling ولا Live Refresh ولا JavaScript، ولا أي منطق
 * نسخ/جدولة جديد. كل الحساب الفعلي في BackupMonitoringService (استعلام
 * واحد فقط). راجع docs/BACKUP-SYSTEM.md.
 */
class BackupMonitoringWidget extends Widget
{
    protected static string $view = 'filament.widgets.backup-monitoring-widget';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    // super_admin فقط — نفس القيد المطبَّق على كل واجهات النسخ الاحتياطي
    // الأخرى في BackupResource/BackupScheduleSettings.
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function getSnapshot(): array
    {
        return app(BackupMonitoringService::class)->snapshot();
    }
}
