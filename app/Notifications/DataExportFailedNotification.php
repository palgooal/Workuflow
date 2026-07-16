<?php

namespace App\Notifications;

use App\Models\DataExportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DataExportFailedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly DataExportRequest $exportRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'data_export_failed',
            'title'   => 'تعذّر إنشاء نسخة بياناتك',
            'message' => 'حدث خطأ أثناء تجهيز نسخة بياناتك. يمكنك المحاولة مرة أخرى.',
            'link'    => route('settings.index').'#data',
            'icon'    => '⚠️',
        ];
    }
}
