<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AggregatesReconciliationCommand — مطابقة وتصحيح إجماليات العملاء
 *
 * Sprint 5 — S5.4
 *
 * يُعيد حساب total_revenue, total_paid, invoice_count من المعاملات الفعلية
 * ويُصحح أي تباين مع القيم المخزنة في جدول clients.
 *
 * الاستخدام:
 *   php artisan crm:reconcile-aggregates
 *   php artisan crm:reconcile-aggregates --user=5
 *   php artisan crm:reconcile-aggregates --dry-run   (معاينة بدون تغيير)
 *
 * مُجدوَل يومياً في routes/console.php الساعة 03:00
 */
class AggregatesReconciliationCommand extends Command
{
    protected $signature = 'crm:reconcile-aggregates
                            {--user=  : معرّف مستخدم محدد (اختياري)}
                            {--dry-run : عرض التباينات فقط بدون تصحيح}
                            {--chunk=500 : حجم الدفعة}';

    protected $description = 'مطابقة وتصحيح إجماليات العملاء (total_revenue, total_paid, invoice_count)';

    private int   $checked    = 0;
    private int   $corrected  = 0;
    private array $corrections = [];

    public function handle(): int
    {
        $startTime = microtime(true);
        $userId    = $this->option('user') ? (int)$this->option('user') : null;
        $dryRun    = (bool)$this->option('dry-run');
        $chunkSize = (int)($this->option('chunk') ?? 500);

        $mode = $dryRun ? '[DRY RUN]' : '';
        $this->info("🔍 مطابقة إجماليات العملاء {$mode}...");

        $usersQuery = User::query()->where('suspended', false);
        if ($userId) {
            $usersQuery->where('id', $userId);
        }

        $userIds = $usersQuery->pluck('id');

        Client::whereIn('user_id', $userIds)
              ->where('is_archived', false)
              ->chunkById($chunkSize, function ($clients) use ($dryRun) {
                  $this->processChunk($clients, $dryRun);
              });

        $duration = round(microtime(true) - $startTime, 2);

        // عرض النتائج
        $this->newLine();
        $this->table(
            ['المقياس', 'القيمة'],
            [
                ['العملاء المفحوصون', number_format($this->checked)],
                ['العملاء المُصحَّحون', number_format($this->corrected)],
                ['الوضع', $dryRun ? 'معاينة فقط' : 'تم التصحيح'],
                ['المدة', "{$duration} ثانية"],
            ]
        );

        if (!empty($this->corrections) && $this->output->isVerbose()) {
            $this->warn('التباينات المكتشفة:');
            $this->table(
                ['العميل', 'الحقل', 'القيمة المخزنة', 'القيمة الحقيقية'],
                $this->corrections
            );
        }

        Log::info("crm:reconcile-aggregates {$mode} — checked={$this->checked} corrected={$this->corrected} | {$duration}s");

        $this->info("✅ اكتمل — {$this->corrected} تصحيح من أصل {$this->checked} عميل.");

        return self::SUCCESS;
    }

    // ==================== Core Logic ====================

    private function processChunk(\Illuminate\Support\Collection $clients, bool $dryRun): void
    {
        $clientIds = $clients->pluck('id')->all();

        // استعلام واحد لكل إجماليات الـ chunk (أكثر كفاءة من N+1)
        $computed = $this->computeAggregates($clientIds);

        foreach ($clients as $client) {
            $this->checked++;

            $c = $computed[$client->id] ?? [
                'total_revenue' => 0,
                'total_paid'    => 0,
                'invoice_count' => 0,
            ];

            $diffs = $this->findDifferences($client, $c);

            if (!empty($diffs)) {
                $this->corrected++;

                if (!$dryRun) {
                    $client->update(array_combine(
                        array_column($diffs, 'field'),
                        array_column($diffs, 'actual')
                    ));
                }

                foreach ($diffs as $diff) {
                    $this->corrections[] = [
                        $client->id,
                        $diff['field'],
                        $diff['stored'],
                        $diff['actual'],
                    ];
                }

                if (!$dryRun) {
                    Log::info("crm:reconcile — client {$client->id} corrected: " .
                        collect($diffs)->map(fn ($d) => "{$d['field']}: {$d['stored']}→{$d['actual']}")->join(', ')
                    );
                }
            }
        }
    }

    /**
     * حساب الإجماليات الحقيقية من جدول transactions
     * باستعلام واحد لكل الـ clients في الـ chunk
     */
    private function computeAggregates(array $clientIds): array
    {
        $rows = DB::table('transactions')
            ->join('projects', 'transactions.project_id', '=', 'projects.id')
            ->whereIn('projects.client_id', $clientIds)
            ->whereNull('transactions.deleted_at')
            ->selectRaw("
                projects.client_id,
                SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END) as total_paid,
                COUNT(CASE WHEN transactions.type = 'income' THEN 1 END) as invoice_count
            ")
            ->groupBy('projects.client_id')
            ->get()
            ->keyBy('client_id');

        // تحويل إلى array indexed by client_id
        $result = [];
        foreach ($clientIds as $id) {
            $row = $rows->get($id);
            $result[$id] = [
                'total_revenue' => $row ? round((float)$row->total_revenue, 2) : 0.0,
                'total_paid'    => $row ? round((float)$row->total_paid, 2)    : 0.0,
                'invoice_count' => $row ? (int)$row->invoice_count              : 0,
            ];
        }

        return $result;
    }

    /**
     * إيجاد التباينات بين القيم المخزنة والمحسوبة
     */
    private function findDifferences(Client $client, array $computed): array
    {
        $diffs = [];

        $fields = [
            'total_revenue' => ['stored' => (float)$client->total_revenue, 'actual' => $computed['total_revenue']],
            'total_paid'    => ['stored' => (float)$client->total_paid,    'actual' => $computed['total_paid']],
            'invoice_count' => ['stored' => (int)$client->invoice_count,   'actual' => $computed['invoice_count']],
        ];

        foreach ($fields as $field => $values) {
            // تسامح 0.01 للفروق العشرية الطفيفة
            $tolerance = $field === 'invoice_count' ? 0 : 0.01;

            if (abs($values['stored'] - $values['actual']) > $tolerance) {
                $diffs[] = [
                    'field'   => $field,
                    'stored'  => $values['stored'],
                    'actual'  => $values['actual'],
                ];
            }
        }

        return $diffs;
    }
}
