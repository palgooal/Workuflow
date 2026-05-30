<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── زر تجربة الرسالة ─────────────────────────────────────────
            Actions\Action::make('sendTest')
                ->label('تجربة الرسالة')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('test_email')
                        ->label('البريد الإلكتروني للاختبار')
                        ->email()
                        ->required()
                        ->placeholder('your@email.com'),
                ])
                ->modalHeading('إرسال نسخة تجريبية')
                ->modalDescription('سيُرسَل البريد بالمتغيرات الافتراضية للمعاينة.')
                ->modalSubmitActionLabel('إرسال الآن')
                ->action(function (array $data): void {
                    $record   = $this->record;
                    $toEmail  = $data['test_email'];

                    // قيم افتراضية للمتغيرات — للمعاينة فقط
                    $demoVars = [
                        '{{name}}'      => 'محمد (مستخدم تجريبي)',
                        '{{reset_url}}' => config('app.url') . '/reset-password/DEMO_TOKEN',
                        '{{verify_url}}'=> config('app.url') . '/verify-email/DEMO',
                        '{{login_url}}' => config('app.url') . '/dashboard',
                    ];

                    $subject = $record->subject;
                    $body    = $record->body;

                    foreach ($demoVars as $placeholder => $value) {
                        $subject = str_replace($placeholder, $value, $subject);
                        $body    = str_replace($placeholder, $value, $body);
                    }

                    try {
                        Mail::html(
                            view('emails.template', ['body' => $body])->render(),
                            function ($msg) use ($toEmail, $subject) {
                                $msg->to($toEmail)->subject('[تجريبي] ' . $subject);
                            }
                        );

                        Notification::make()
                            ->title("✅ تم الإرسال إلى {$toEmail}")
                            ->body('تحقق من صندوق الوارد.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('❌ فشل الإرسال')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            // ── العودة للقائمة ───────────────────────────────────────────
            Actions\Action::make('back')
                ->label('العودة')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(EmailTemplateResource::getUrl()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return '✅ تم حفظ القالب بنجاح';
    }
}
