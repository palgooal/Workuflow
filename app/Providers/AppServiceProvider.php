<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Project;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Policies\BudgetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DebtPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RecurringPolicy;
use App\Policies\TransactionPolicy;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Debt::class, DebtPolicy::class);
        Gate::policy(RecurringTransaction::class, RecurringPolicy::class);

        // ── تطبيق إعدادات البريد من قاعدة البيانات ──────────────────────
        $this->applyMailSettings();
    }

    private function applyMailSettings(): void
    {
        try {
            $mail = Setting::group('mail');
            if (empty($mail)) return;

            // تحديث config البريد في الـ runtime
            if (! empty($mail['mail_host'])) {
                Config::set('mail.mailers.smtp.host',       $mail['mail_host']);
                Config::set('mail.mailers.smtp.port',       (int) ($mail['mail_port'] ?? 465));
                Config::set('mail.mailers.smtp.username',   $mail['mail_username'] ?? null);
                Config::set('mail.mailers.smtp.password',   $mail['mail_password'] ?? null);
                Config::set('mail.mailers.smtp.encryption', $mail['mail_encryption'] ?? 'ssl');
                Config::set('mail.mailers.smtp.scheme',     $mail['mail_scheme'] ?? 'smtps');
                Config::set('mail.from.address',            $mail['mail_from_address'] ?? config('mail.from.address'));
                Config::set('mail.from.name',               $mail['mail_from_name']    ?? config('mail.from.name'));
            }
        } catch (\Throwable) {
            // تجاهل إذا كان جدول settings غير موجود بعد (أول migrate)
        }
    }
}
