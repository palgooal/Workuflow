<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Enums\HealthScoreGrade;
use App\Modules\CRM\Models\ClientHealthScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ClientHealthScoreService — محرك حساب مؤشر صحة العميل
 *
 * Sprint 5 — S5.1 (V2 — Recency Bias)
 *
 * الخوارزمية:
 *   Score = Σ (factor_score × weight) × 100
 *
 * العوامل وأوزانها (config/crm.php → health_score.weights):
 *   payment_rate        0.35  — معدل الدفع (paid / invoiced)
 *   work_frequency      0.25  — تكرار التعاملات خلال الـ 12 شهراً
 *   revenue_value       0.20  — قيمة الإيراد النسبية
 *   contact_regularity  0.10  — انتظام التواصل
 *   response_rate       0.10  — حصة المتابعات المكتملة
 *
 * Recency Bias (V2):
 *   كل عامل = 70% الثلاثة أشهر الأخيرة + 30% الـ 12 شهراً كاملة
 */
class ClientHealthScoreService
{
    // ==================== Config ====================

    private array $weights;
    private int   $recentMonths;
    private float $recentWeight;
    private int   $historicMonths;
    private float $historicWeight;

    // المرجع للإيراد: ≥ 10,000 = 1.0 (أعلى درجة ممكنة)
    private const REVENUE_TOP = 10_000.0;

    // تكرار العمل: ≥ 12 معاملة في السنة = 1.0
    private const FREQ_TOP = 12;

    public function __construct()
    {
        $cfg                  = config('crm.health_score', []);
        $this->weights        = $cfg['weights']        ?? [];
        $this->recentMonths   = $cfg['recency_bias']['recent_months']   ?? 3;
        $this->recentWeight   = $cfg['recency_bias']['recent_weight']   ?? 0.70;
        $this->historicMonths = $cfg['recency_bias']['historic_months'] ?? 12;
        $this->historicWeight = $cfg['recency_bias']['historic_weight'] ?? 0.30;
    }

    // ==================== Public API ====================

    /**
     * حساب وحفظ مؤشر صحة عميل واحد
     * يُحدِّث clients.health_score + يُنشئ سجلاً في client_health_scores
     */
    public function calculate(Client $client): int
    {
        $factors = $this->computeFactors($client);
        $score   = $this->aggregateScore($factors);

        DB::transaction(function () use ($client, $score, $factors) {
            // تحديث العمود الكاشف في جدول clients
            $client->update(['health_score' => $score]);

            // سجل تاريخي
            ClientHealthScore::create([
                'client_id' => $client->id,
                'score'     => $score,
                'factors'   => $factors,
                'scored_at' => now(),
            ]);
        });

        return $score;
    }

    /**
     * حساب دون حفظ (لعرض معاينة)
     * @return array{score: int, grade: string, factors: array}
     */
    public function preview(Client $client): array
    {
        $factors = $this->computeFactors($client);
        $score   = $this->aggregateScore($factors);

        return [
            'score'   => $score,
            'grade'   => HealthScoreGrade::fromScore($score)->value,
            'label'   => HealthScoreGrade::fromScore($score)->label(),
            'factors' => $factors,
        ];
    }

    /**
     * حساب دفعي لكل عملاء مستخدم (chunk)
     * @return array{processed: int, avg_score: float, duration_ms: int}
     */
    public function recalculateForUser(int $userId, int $chunkSize = 200): array
    {
        $start     = microtime(true);
        $processed = 0;
        $scoreSum  = 0;

        Client::where('user_id', $userId)
              ->where('is_archived', false)
              ->select(['id', 'total_revenue', 'total_paid', 'invoice_count',
                        'last_contact_at', 'last_payment_at', 'created_at'])
              ->chunkById($chunkSize, function ($clients) use (&$processed, &$scoreSum) {
                  foreach ($clients as $client) {
                      try {
                          $score     = $this->calculate($client);
                          $scoreSum += $score;
                          $processed++;
                      } catch (\Throwable $e) {
                          Log::warning("HealthScore: failed for client {$client->id}: {$e->getMessage()}");
                      }
                  }
              });

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $avgScore   = $processed > 0 ? round($scoreSum / $processed, 1) : 0;

        Log::info("HealthScore: recalculated {$processed} clients for user {$userId} | avg={$avgScore} | {$durationMs}ms");

        return [
            'processed'   => $processed,
            'avg_score'   => $avgScore,
            'duration_ms' => $durationMs,
        ];
    }

    // ==================== Factor Computation ====================

    /**
     * حساب كل عامل بـ Recency Bias
     * كل عامل يُرجع قيمة بين 0.0 و 1.0
     */
    private function computeFactors(Client $client): array
    {
        return [
            'payment_rate'       => $this->factorPaymentRate($client),
            'work_frequency'     => $this->factorWorkFrequency($client),
            'revenue_value'      => $this->factorRevenueValue($client),
            'contact_regularity' => $this->factorContactRegularity($client),
            'response_rate'      => $this->factorResponseRate($client),
        ];
    }

    /**
     * معدل الدفع: total_paid / total_revenue
     * Recency Bias: نعطي وزناً أكبر للدفعات الأخيرة
     */
    private function factorPaymentRate(Client $client): float
    {
        $totalRevenue = (float)($client->total_revenue ?? 0);

        if ($totalRevenue <= 0) {
            // لا فواتير = عميل محايد (0.5 بدلاً من 0.0)
            return 0.5;
        }

        $totalPaid = (float)($client->total_paid ?? 0);

        // الدفعات الأخيرة (3 أشهر)
        $recentPaid = $this->getRecentPaid($client->id, $this->recentMonths);
        $recentBilled = $this->getRecentBilled($client->id, $this->recentMonths);

        $recentRate  = $recentBilled > 0 ? min(1.0, $recentPaid / $recentBilled) : 0.5;
        $historicRate = min(1.0, $totalPaid / $totalRevenue);

        return ($recentRate * $this->recentWeight) + ($historicRate * $this->historicWeight);
    }

    /**
     * تكرار العمل: عدد المعاملات خلال الفترة
     * Recency Bias: تكرار الـ 3 أشهر × 4 مقارناً بالسنة كاملة
     */
    private function factorWorkFrequency(Client $client): float
    {
        $recentTx  = $this->countTransactions($client->id, $this->recentMonths);
        $historicTx = $this->countTransactions($client->id, $this->historicMonths);

        // تطبيع: annualize الـ recent
        $recentAnnualized = $recentTx * (12 / max($this->recentMonths, 1));

        $recentScore  = min(1.0, $recentAnnualized / self::FREQ_TOP);
        $historicScore = min(1.0, $historicTx / self::FREQ_TOP);

        return ($recentScore * $this->recentWeight) + ($historicScore * $this->historicWeight);
    }

    /**
     * قيمة الإيراد: نسبة إلى REVENUE_TOP
     * Recency Bias: إيراد الـ 3 أشهر × 4 مقارناً بالإجمالي
     */
    private function factorRevenueValue(Client $client): float
    {
        $totalRevenue = (float)($client->total_revenue ?? 0);

        if ($totalRevenue <= 0) return 0.0;

        $recentRevenue = $this->getRecentRevenue($client->id, $this->recentMonths);

        // annualize الـ recent
        $recentAnnualized = $recentRevenue * (12 / max($this->recentMonths, 1));

        $recentScore   = min(1.0, $recentAnnualized / self::REVENUE_TOP);
        $historicScore = min(1.0, $totalRevenue / self::REVENUE_TOP);

        return ($recentScore * $this->recentWeight) + ($historicScore * $this->historicWeight);
    }

    /**
     * انتظام التواصل: كم مرة تواصلنا خلال الـ 12 شهراً
     * Recency Bias: آخر تواصل قريب يرفع الدرجة
     */
    private function factorContactRegularity(Client $client): float
    {
        if (!$client->last_contact_at) return 0.0;

        $daysSinceLast = (int) now()->diffInDays($client->last_contact_at);

        // كلما قل الوقت، كلما ارتفعت الدرجة
        // 0 يوم = 1.0 | 30 يوم = 0.7 | 90 يوم = 0.3 | 180+ يوم = 0.0
        $recentScore = match(true) {
            $daysSinceLast <= 7   => 1.0,
            $daysSinceLast <= 30  => 0.8,
            $daysSinceLast <= 60  => 0.5,
            $daysSinceLast <= 90  => 0.3,
            $daysSinceLast <= 180 => 0.1,
            default               => 0.0,
        };

        // نقسم على المدة التاريخية (12 شهر)
        $contactCount  = $this->countContacts($client->id, $this->historicMonths);
        $historicScore = min(1.0, $contactCount / 12); // مرة/شهر = كامل

        return ($recentScore * $this->recentWeight) + ($historicScore * $this->historicWeight);
    }

    /**
     * معدل الاستجابة: المتابعات المكتملة / الإجمالي
     * Recency Bias: إكمال المتابعات الأخيرة أهم
     */
    private function factorResponseRate(Client $client): float
    {
        $stats = $this->getFollowUpStats($client->id);

        if ($stats['total'] === 0) return 0.6; // لا متابعات = محايد

        $recentStats   = $this->getFollowUpStats($client->id, $this->recentMonths);
        $historicStats = $stats;

        $recentRate  = $recentStats['total'] > 0
            ? min(1.0, $recentStats['completed'] / $recentStats['total'])
            : 0.6;

        $historicRate = min(1.0, $historicStats['completed'] / $historicStats['total']);

        return ($recentRate * $this->recentWeight) + ($historicRate * $this->historicWeight);
    }

    // ==================== Score Aggregation ====================

    private function aggregateScore(array $factors): int
    {
        $score = 0.0;

        foreach ($factors as $name => $value) {
            $weight = $this->weights[$name] ?? 0.0;
            $score += $value * $weight;
        }

        return (int) round(min(100, max(0, $score * 100)));
    }

    // ==================== DB Queries ====================

    private function getRecentPaid(int $clientId, int $months): float
    {
        return (float) DB::table('transactions')
            ->join('projects', 'transactions.project_id', '=', 'projects.id')
            ->where('projects.client_id', $clientId)
            ->where('transactions.type', 'income')
            ->where('transactions.transaction_date', '>=', now()->subMonths($months))
            ->whereNull('transactions.deleted_at')
            ->sum('transactions.amount');
    }

    private function getRecentBilled(int $clientId, int $months): float
    {
        return (float) DB::table('transactions')
            ->join('projects', 'transactions.project_id', '=', 'projects.id')
            ->where('projects.client_id', $clientId)
            ->whereIn('transactions.type', ['income', 'expense'])
            ->where('transactions.transaction_date', '>=', now()->subMonths($months))
            ->whereNull('transactions.deleted_at')
            ->sum(DB::raw('ABS(transactions.amount)'));
    }

    private function getRecentRevenue(int $clientId, int $months): float
    {
        return (float) DB::table('transactions')
            ->join('projects', 'transactions.project_id', '=', 'projects.id')
            ->where('projects.client_id', $clientId)
            ->where('transactions.type', 'income')
            ->where('transactions.transaction_date', '>=', now()->subMonths($months))
            ->whereNull('transactions.deleted_at')
            ->sum('transactions.amount');
    }

    private function countTransactions(int $clientId, int $months): int
    {
        return (int) DB::table('transactions')
            ->join('projects', 'transactions.project_id', '=', 'projects.id')
            ->where('projects.client_id', $clientId)
            ->where('transactions.transaction_date', '>=', now()->subMonths($months))
            ->whereNull('transactions.deleted_at')
            ->count();
    }

    private function countContacts(int $clientId, int $months): int
    {
        // client_activities يستخدم occurred_at وليس created_at (لا يوجد عمود created_at في الجدول)
        return (int) DB::table('client_activities')
            ->where('client_id', $clientId)
            ->where('occurred_at', '>=', now()->subMonths($months))
            ->count();
    }

    private function getFollowUpStats(int $clientId, ?int $months = null): array
    {
        // client_follow_ups لا يستخدم SoftDeletes — لا يوجد عمود deleted_at
        $query = DB::table('client_follow_ups')
            ->where('client_id', $clientId);

        if ($months) {
            // client_follow_ups يستخدم created_at (من timestamps()) — صحيح هنا
            $query->where('created_at', '>=', now()->subMonths($months));
        }

        $total     = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();

        return compact('total', 'completed');
    }
}
