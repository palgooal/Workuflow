<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Project;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Policies\BudgetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DebtPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RecurringPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\WalletPolicy;
use App\Models\Setting;
use App\Modules\Billing\Contracts\PaymentProviderInterface;
use App\Modules\Billing\Contracts\RenewalServiceInterface;
use App\Modules\Billing\Services\ManualRenewalService;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Billing\Services\TogoPaymentService;
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
        // ── ربط خدمة التجديد ───────────────────────────────────────────
        $this->app->bind(RenewalServiceInterface::class, function ($app) {
            return new ManualRenewalService(
                $app->make(SubscriptionService::class)
            );
        });

        // ── ربط مزود الدفع النشط ───────────────────────────────────────
        $this->app->bind(PaymentProviderInterface::class, function () {
            return match (config('billing.provider')) {
                'togo'  => new TogoPaymentService(),
                default => throw new \RuntimeException(
                    'لا يوجد مزود دفع مفعّل. اضبط BILLING_PROVIDER في .env'
                ),
            };
        });
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
        Gate::policy(Wallet::class, WalletPolicy::class);

        // ── تطبيق إعدادات البريد من قاعدة البيانات ──────────────────────
        $this->applyMailSettings();

        // ── تطبيق إعدادات بوابة الدفع من قاعدة البيانات ─────────────────
        $this->applyPaymentSettings();
    }

    private function applyPaymentSettings(): void
    {
        try {
            $p = Setting::group('payment');
            if (empty($p)) return;

            // مزود الدفع — نطبّق دائماً حتى لو كانت فارغة (لتعطيل البوابة)
            if (array_key_exists('billing_provider', $p)) {
                Config::set('billing.provider', $p['billing_provider'] ?: null);
            }

            // Togo credentials
            if (! empty($p['togo_api_key'])) {
                Config::set('billing.togo.api_key', $p['togo_api_key']);
            }
            if (! empty($p['togo_receiver_address_id'])) {
                Config::set('billing.togo.receiver_address_id', $p['togo_receiver_address_id']);
            }
            if (! empty($p['togo_currency'])) {
                Config::set('billing.togo.currency', $p['togo_currency']);
            }

            // ملاحظة: أسعار الخطط لا تُضبط من هنا.
            // مصدر الحقيقة: config/billing.php (billing.plans.{plan}.{cycle}.price)
        } catch (\Throwable) {
            // تجاهل إذا كان الجدول غير موجود بعد (أول migrate)
        }
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
