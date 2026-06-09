<?php

namespace App\Console\Commands;

use App\Mail\ReEngagementEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReEngagementEmails extends Command
{
    protected $signature = 'emails:re-engage
                            {--dry-run : اعرض القائمة فقط بدون إرسال}
                            {--user= : أرسل لمستخدم واحد فقط (email)}';

    protected $description = 'أرسل إيميل إعادة تفعيل للمستخدمين الذين سجّلوا ولم ينشئوا أي مشروع أو معاملة';

    public function handle(): int
    {
        // مستخدم واحد محدد
        if ($email = $this->option('user')) {
            $users = User::where('email', $email)->get();
        } else {
            // كل المستخدمين: لا مشاريع + لا معاملات + لم يُغلقوا الـ Onboarding
            $users = User::whereDoesntHave('projects')
                ->whereDoesntHave('transactions')
                ->whereNull('onboarding_dismissed_at')
                ->whereNull('email_verified_at', 'or', fn ($q) => $q->whereNotNull('email_verified_at'))
                ->get();
        }

        if ($users->isEmpty()) {
            $this->info('لا يوجد مستخدمون مؤهلون للإرسال.');
            return Command::SUCCESS;
        }

        $this->table(
            ['#', 'الاسم', 'البريد', 'تاريخ التسجيل'],
            $users->map(fn ($u, $i) => [
                $i + 1,
                $u->name,
                $u->email,
                $u->created_at->format('Y-m-d'),
            ])
        );

        $count = $users->count();

        if ($this->option('dry-run')) {
            $this->warn("Dry run — لن يُرسل شيء. ({$count} مستخدم)");
            return Command::SUCCESS;
        }

        if (! $this->confirm("إرسال الإيميل لـ {$count} مستخدم؟")) {
            $this->line('إلغاء.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new ReEngagementEmail($user));
                $this->line("  ✓ {$user->email}");
                $sent++;
                // تأخير بسيط لتجنب spam throttle
                usleep(300_000); // 0.3 ثانية
            } catch (\Throwable $e) {
                $this->error("  ✗ {$user->email} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("✅ أُرسل: {$sent} | ✗ فشل: {$failed}");

        return Command::SUCCESS;
    }
}
