<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        $tpl = EmailTemplate::render('email_verification', [
            '{{name}}'       => $notifiable->name,
            '{{verify_url}}' => $verifyUrl,
        ]);

        // إذا لم يوجد قالب نشط في DB، استخدم الافتراضي
        if (! $tpl) {
            return parent::toMail($notifiable);
        }

        return (new MailMessage)
            ->subject($tpl['subject'])
            ->view('emails.template', ['body' => $tpl['body']]);
    }
}
