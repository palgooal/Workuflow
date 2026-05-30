<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}

    public function envelope(): Envelope
    {
        $tpl = EmailTemplate::render('welcome', []);
        $subject = $tpl['subject'] ?? 'مرحباً بك في دراهم 🎉';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $tpl = EmailTemplate::render('welcome', [
            '{{name}}'      => $this->user->name,
            '{{login_url}}' => config('app.url') . '/dashboard',
        ]);

        // إذا وُجد قالب في DB استخدمه، وإلا استخدم الـ view القديم
        if ($tpl) {
            return new Content(
                view: 'emails.template',
                with: ['body' => $tpl['body']],
            );
        }

        return new Content(
            view: 'emails.welcome',
            with: [
                'userName'     => $this->user->name,
                'dashboardUrl' => config('app.url') . '/dashboard',
            ],
        );
    }
}
