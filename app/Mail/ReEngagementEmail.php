<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReEngagementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}

    public function envelope(): Envelope
    {
        $tpl = EmailTemplate::render('re_engagement', []);
        $subject = $tpl['subject'] ?? 'مشروعك الأول ينتظرك في دراهم 🚀';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $tpl = EmailTemplate::render('re_engagement', [
            '{{name}}'           => $this->user->name,
            '{{dashboard_url}}'  => config('app.url') . '/dashboard',
            '{{owner_whatsapp}}' => config('billing.owner_whatsapp', ''),
        ]);

        return new Content(
            view: 'emails.template',
            with: ['body' => $tpl['body'] ?? ''],
        );
    }
}
