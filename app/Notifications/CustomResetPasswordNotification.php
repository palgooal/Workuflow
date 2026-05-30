<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        $tpl = EmailTemplate::render('password_reset', [
            '{{name}}'      => $notifiable->name,
            '{{reset_url}}' => $resetUrl,
        ]);

        // إذا لم يوجد قالب في DB، استخدم الافتراضي
        if (! $tpl) {
            return parent::toMail($notifiable);
        }

        return (new MailMessage)
            ->subject($tpl['subject'])
            ->view('emails.template', ['body' => $tpl['body']]);
    }
}
