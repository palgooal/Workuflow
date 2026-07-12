<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\DataRetentionLedgerEntry;
use Illuminate\Console\Command;

/**
 * ReportDueDataRetentionPurges — تقرير Dry-Run فقط
 *
 * يبحث عن سجلات في data_retention_ledger تجاوزت تاريخ استحقاق التطهير
 * (purge_due_at) ولا تخضع لتعليق قانوني (legal_hold)، ويعرضها كتقرير.
 *
 * ⚠ لا يحذف أي بيانات إطلاقاً. هذا الأمر للتدقيق والمتابعة فقط، إلى حين
 * تصميم واعتماد آلية تطهير نهائية آمنة (تراعي العلاقات، الاستثناءات
 * القانونية، النسخ الاحتياطية، وDry Run) — راجع
 * docs/legal/LEGAL-IMPLEMENTATION-AUDIT.md، الفجوة #1.
 *
 * يُسجَّل ملخص كل تشغيلة في activity_logs لضمان أثر تدقيقي دائم،
 * بحيث لا تعتمد المتابعة على قراءة مخرجات الطرفية فقط.
 */
class ReportDueDataRetentionPurges extends Command
{
    protected $signature = 'retention:report-due';

    protected $description = 'تقرير (بدون حذف) بالحسابات التي تجاوزت مدة الاحتفاظ المعتمَدة وتحتاج مراجعة يدوية للتطهير';

    public function handle(): int
    {
        $due = DataRetentionLedgerEntry::query()
            ->dueForPurge()
            ->orderBy('purge_due_at')
            ->get();

        if ($due->isEmpty()) {
            $this->info('✅ لا توجد حسابات تجاوزت مدة الاحتفاظ حالياً.');
        } else {
            $this->warn("⚠ يوجد {$due->count()} حساب(ات) تجاوزت مدة الاحتفاظ المعتمَدة وتحتاج مراجعة يدوية للتطهير:");

            $this->table(
                ['ID', 'user_id', 'البريد (لحظة الإغلاق)', 'أُغلق في', 'استُحق التطهير في', 'أيام التأخير'],
                $due->map(fn (DataRetentionLedgerEntry $entry) => [
                    $entry->id,
                    $entry->user_id ?? '—',
                    $entry->user_email_snapshot ?? '—',
                    $entry->closed_at->format('Y-m-d'),
                    $entry->purge_due_at->format('Y-m-d'),
                    (int) $entry->purge_due_at->diffInDays(now()),
                ])->toArray(),
            );

            $this->newLine();
            $this->line('راجع docs/operations/DATA-DELETION-SOP.md لإجراء التطهير يدوياً بعد المراجعة اللازمة (العلاقات، الاستثناءات القانونية، النسخ الاحتياطية).');
        }

        ActivityLog::record(
            eventType: 'data_retention.due_report_generated',
            metadata: [
                'due_count'  => $due->count(),
                'entry_ids'  => $due->pluck('id')->all(),
            ],
        );

        return self::SUCCESS;
    }
}
