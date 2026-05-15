<?php

namespace App\Jobs;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly User $user
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email, $this->user->name)
            ->send(new WelcomeEmail($this->user));
    }

    public function failed(\Throwable $exception): void
    {
        // يمكن إضافة logging هنا لاحقاً
        \Illuminate\Support\Facades\Log::error('WelcomeEmail failed for user: ' . $this->user->id, [
            'error' => $exception->getMessage(),
        ]);
    }
}
