<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

class SendDebtAlerts extends Command
{
    protected $signature   = 'debts:send-alerts {--user= : إرسال لمستخدم محدد بالـ ID}';
    protected $description = 'إرسال تنبيهات الديون المستحقة والمتأخرة لجميع المستخدمين';

    public function __construct(private readonly NotificationService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->option('user');

        $users = $userId
            ? User::where('id', $userId)->get()
            : User::all();

        if ($users->isEmpty()) {
            $this->warn('لا يوجد مستخدمون.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $total = 0;

        foreach ($users as $user) {
            try {
                $before = $user->unreadNotifications()->count();
                $this->service->generateDebtAlerts($user);
                $after  = $user->unreadNotifications()->count();
                $total += max(0, $after - $before);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("خطأ للمستخدم {$user->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ تم إرسال {$total} إشعار جديد لـ {$users->count()} مستخدم.");

        return self::SUCCESS;
    }
}
