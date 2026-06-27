<?php

namespace App\Console\Commands;

use App\Modules\Referral\Models\Affiliate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ReconcileReferralAggregatesCommand — مطابقة وتصحيح إجماليات المسوّقين
 *
 * يُعيد حساب الحقول المُجمَّعة في جدول affiliates من المصادر الحقيقية:
 *
 *   total_referrals → COUNT(users.referred_by_affiliate_id = affiliate.id)
 *   total_converted → COUNT(referral_commissions) بحالات pending/approved/paid
 *   total_earned    → SUM(referral_commissions.amount) بحالات approved/paid
 *   total_paid      → SUM(referral_payouts.amount) بحالة paid
 *
 * هذا الأمر يُصحّح أي تباين ناتج عن:
 *   - فشل Queue أو Retry
 *   - إلغاء اشتراك يدوياً من Filament
 *   - Bug في increment/decrement
 *
 * الاستخدام:
 *   php artisan referral:reconcile
 *   php artisan referral:reconcile --dry-run
 *   php artisan referral:reconcile --dry-run -v   (تفاصيل التباينات)
 *   php artisan referral:reconcile --chunk=200
 *
 * مُجدوَل يومياً في routes/console.php الساعة 03:30
 */
class ReconcileReferralAggregatesCommand extends Command
{
    protected $signature = 'referral:reconcile
                            {--dry-run  : عرض التباينات فقط بدون تصحيح}
                            {--chunk=500 : حجم الدفعة لكل استعلام}';

    protected $description = 'مطابقة وتصحيح إجماليات المسوّقين (total_referrals, total_converted, total_earned, total_paid)';

    private int   $checked    = 0;
    private int   $corrected  = 0;
    private array $corrections = [];

    public function handle(): int
    {
        $startTime = microtime(true);
        $dryRun    = (bool) $this->option('dry-run');
        $chunkSize = (int) ($this->option('chunk') ?? 500);

        $mode = $dryRun ? '[DRY RUN]' : '';
        $this->info("🔍 مطابقة إجماليات المسوّقين {$mode}...");

        Affiliate::query()
            ->chunkById($chunkSize, function ($affiliates) use ($dryRun): void {
                $this->processChunk($affiliates, $dryRun);
            });

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->table(
            ['المقياس', 'القيمة'],
            [
                ['المسوّقون المفحوصون', number_format($this->checked)],
                ['المسوّقون المُصحَّحون', number_format($this->corrected)],
                ['الوضع', $dryRun ? 'معاينة فقط' : 'تم التصحيح'],
                ['المدة', "{$duration} ثانية"],
            ]
        );

        if (! empty($this->corrections) && $this->output->isVerbose()) {
            $this->warn('التباينات المكتشفة:');
            $this->table(
                ['المسوّق', 'الحقل', 'القيمة المخزنة', 'القيمة الحقيقية'],
                $this->corrections
            );
        }

        Log::info("referral:reconcile {$mode} — checked={$this->checked} corrected={$this->corrected} | {$duration}s");

        $this->info("✅ اكتمل — {$this->corrected} تصحيح من أصل {$this->checked} مسوّق.");

        return self::SUCCESS;
    }

    // ── Core Logic ────────────────────────────────────────────────────────

    private function processChunk(\Illuminate\Support\Collection $affiliates, bool $dryRun): void
    {
        $affiliateIds = $affiliates->pluck('id')->all();

        $computed = $this->computeAggregates($affiliateIds);

        foreach ($affiliates as $affiliate) {
            $this->checked++;

            $c = $computed[$affiliate->id] ?? [
                'total_referrals' => 0,
                'total_converted' => 0,
                'total_earned'    => 0.0,
                'total_paid'      => 0.0,
            ];

            $diffs = $this->findDifferences($affiliate, $c);

            if (! empty($diffs)) {
                $this->corrected++;

                if (! $dryRun) {
                    $affiliate->update(array_combine(
                        array_column($diffs, 'field'),
                        array_column($diffs, 'actual')
                    ));
                }

                foreach ($diffs as $diff) {
                    $this->corrections[] = [
                        $affiliate->id,
                        $diff['field'],
                        $diff['stored'],
                        $diff['actual'],
                    ];
                }

                if (! $dryRun) {
                    Log::info('referral:reconcile — affiliate corrected', [
                        'affiliate_id' => $affiliate->id,
                        'diffs'        => collect($diffs)
                            ->map(fn ($d) => "{$d['field']}: {$d['stored']}→{$d['actual']}")
                            ->join(', '),
                    ]);
                }
            }
        }
    }

    /**
     * حساب الإجماليات الحقيقية لدفعة من المسوّقين
     * 4 استعلامات مُجمَّعة لكل الدفعة — لا N+1
     *
     * @param  string[]  $affiliateIds
     * @return array<string, array{total_referrals:int, total_converted:int, total_earned:float, total_paid:float}>
     */
    private function computeAggregates(array $affiliateIds): array
    {
        // ── 1. total_referrals ────────────────────────────────────────────
        // عدد المستخدمين المُحالين (المسجَّلين عبر الرابط)
        $referrals = DB::table('users')
            ->whereIn('referred_by_affiliate_id', $affiliateIds)
            ->whereNotNull('referred_by_affiliate_id')
            ->selectRaw('referred_by_affiliate_id as affiliate_id, COUNT(*) as cnt')
            ->groupBy('referred_by_affiliate_id')
            ->get()
            ->keyBy('affiliate_id');

        // ── 2. total_converted ────────────────────────────────────────────
        // عدد العمولات بحالات تُحتسب للـ Tier: pending, approved, paid
        // (CommissionStatus::countsForTier)
        $converted = DB::table('referral_commissions')
            ->whereIn('affiliate_id', $affiliateIds)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->selectRaw('affiliate_id, COUNT(*) as cnt')
            ->groupBy('affiliate_id')
            ->get()
            ->keyBy('affiliate_id');

        // ── 3. total_earned ───────────────────────────────────────────────
        // مجموع العمولات المعتمدة: approved + paid فقط
        // (CommissionStatus::countsForEarnings)
        $earned = DB::table('referral_commissions')
            ->whereIn('affiliate_id', $affiliateIds)
            ->whereIn('status', ['approved', 'paid'])
            ->selectRaw('affiliate_id, SUM(amount) as total')
            ->groupBy('affiliate_id')
            ->get()
            ->keyBy('affiliate_id');

        // ── 4. total_paid ─────────────────────────────────────────────────
        // مجموع المدفوعات الفعلية (Payouts) بحالة paid
        $paid = DB::table('referral_payouts')
            ->whereIn('affiliate_id', $affiliateIds)
            ->where('status', 'paid')
            ->selectRaw('affiliate_id, SUM(amount) as total')
            ->groupBy('affiliate_id')
            ->get()
            ->keyBy('affiliate_id');

        // ── تجميع النتائج ─────────────────────────────────────────────────
        $result = [];

        foreach ($affiliateIds as $id) {
            $result[$id] = [
                'total_referrals' => (int) ($referrals->get($id)?->cnt  ?? 0),
                'total_converted' => (int) ($converted->get($id)?->cnt  ?? 0),
                'total_earned'    => round((float) ($earned->get($id)?->total ?? 0), 2),
                'total_paid'      => round((float) ($paid->get($id)?->total   ?? 0), 2),
            ];
        }

        return $result;
    }

    /**
     * إيجاد التباينات بين القيم المخزنة والمحسوبة
     *
     * @return array<array{field:string, stored:int|float, actual:int|float}>
     */
    private function findDifferences(Affiliate $affiliate, array $computed): array
    {
        $diffs = [];

        $fields = [
            'total_referrals' => [
                'stored' => (int)   $affiliate->total_referrals,
                'actual' => (int)   $computed['total_referrals'],
                'type'   => 'int',
            ],
            'total_converted' => [
                'stored' => (int)   $affiliate->total_converted,
                'actual' => (int)   $computed['total_converted'],
                'type'   => 'int',
            ],
            'total_earned' => [
                'stored' => (float) $affiliate->total_earned,
                'actual' => (float) $computed['total_earned'],
                'type'   => 'decimal',
            ],
            'total_paid' => [
                'stored' => (float) $affiliate->total_paid,
                'actual' => (float) $computed['total_paid'],
                'type'   => 'decimal',
            ],
        ];

        foreach ($fields as $field => $values) {
            // تسامح 0.01 للحقول العشرية فقط
            $tolerance = $values['type'] === 'decimal' ? 0.01 : 0;

            if (abs($values['stored'] - $values['actual']) > $tolerance) {
                $diffs[] = [
                    'field'  => $field,
                    'stored' => $values['stored'],
                    'actual' => $values['actual'],
                ];
            }
        }

        return $diffs;
    }
}
