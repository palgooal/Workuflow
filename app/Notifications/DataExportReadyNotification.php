<?php

namespace App\Notifications;

use App\Models\DataExportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DataExportReadyNotification extends Notification
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
            'type'       => 'data_export_ready',
            'title'      => 'نسخة بياناتك جاهزة',
            'message'    => 'نسخة بياناتك جاهزة للتنزيل. الرابط صالح حتى '
                .optional($this->exportRequest->expires_at)->translatedFormat('Y-m-d H:i'),
            'link'       => route('settings.index').'#data',
            'icon'       => '📦',
        ];
    }
}
