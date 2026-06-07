<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\Enums\WalletType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignDefaultWallet extends Command
{
    protected $signature = 'wallets:assign-default
                            {--dry-run : اعرض فقط بدون تعديل}
                            {--user= : معالجة مستخدم محدد (ID)}';

    protected $description = 'تعيين صندوق افتراضي لكل معاملة قديمة بدون wallet_id';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');

        if ($dryRun) {
            $this->warn('⚠️  وضع المعاينة — لن يتم حفظ أي تغيير');
        }

        // استعلام المستخدمين الذين لديهم معاملات بدون wallet_id
        $query = User::whereHas('transactions', fn ($q) => $q->whereNull('wallet_id'));

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('✅ لا توجد معاملات بدون صندوق — كل شيء على ما يرام!');
            return self::SUCCESS;
        }

        $this->info("وُجد {$users->count()} مستخدم/مستخدمين لديهم معاملات بدون صندوق.");
        $this->newLine();

        $totalFixed = 0;

        foreach ($users as $user) {
            $count = $user->transactions()->whereNull('wallet_id')->count();
            $this->line("👤 {$user->name} ({$user->email}) — {$count} معاملة");

            if ($dryRun) {
                $this->line("   ← سيتم تعيينها لصندوق افتراضي");
                $totalFixed += $count;
                continue;
            }

            DB::transaction(function () use ($user, $count, &$totalFixed) {
                // ابحث عن صندوق موجود أو أنشئ واحداً افتراضياً
                $wallet = Wallet::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->orderBy('created_at')
                    ->first();

                if (! $wallet) {
                    $wallet = Wallet::create([
                        'user_id'         => $user->id,
                        'name'            => 'الصندوق العام',
                        'type'            => WalletType::Cash,
                        'currency'        => $user->currency ?? 'SAR',
                        'initial_balance' => 0,
                        'color'           => '#6366f1',
                        'icon'            => '💼',
                        'description'     => 'صندوق افتراضي — أُنشئ تلقائياً لتنظيم المعاملات القديمة',
                        'is_active'       => true,
                    ]);

                    $this->line("   + تم إنشاء صندوق «{$wallet->name}»");
                } else {
                    $this->line("   → استخدام صندوق موجود: «{$wallet->name}»");
                }

                // تحديث المعاملات
                Transaction::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->whereNull('wallet_id')
                    ->update(['wallet_id' => $wallet->id]);

                $this->info("   ✅ تم تعيين {$count} معاملة للصندوق «{$wallet->name}»");
                $totalFixed += $count;
            });
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("⚠️  المعاينة: سيتم تحديث {$totalFixed} معاملة. شغّل بدون --dry-run للتطبيق.");
        } else {
            $this->info("✅ تم الانتهاء — عُولجت {$totalFixed} معاملة.");
        }

        return self::SUCCESS;
    }
}
