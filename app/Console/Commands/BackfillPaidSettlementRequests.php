<?php

namespace App\Console\Commands;

use App\Models\SettlementRequest;
use App\Support\Enums\PaymentCollectionStatus;
use App\Support\Enums\SettlementRequestStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * أمر إصلاح مؤقّت — يُعالج طلبات تسوية (SettlementRequest) عالقة على حالة
 * غير paid رغم أن كل PaymentCollection المرتبطة بها أصبحت settled فعلياً.
 *
 * السبب الجذري الذي أدّى لهذا العطب: الزر المستقل "تسوية مع المشترك" في
 * PaymentCollectionResource كان يُحوِّل PaymentCollection.status→settled
 * مباشرة دون المرور بـ "تعليم كمدفوع" في SettlementRequestResource، فيبقى
 * SettlementRequest عالقاً على pending/approved للأبد. تمّ إغلاق هذا المسار
 * (راجع PaymentCollection::hasOpenSettlementRequest())، لكن أي طلبات عالقة
 * من قبل هذا الإصلاح تحتاج تصحيحاً يدوياً لمرة واحدة عبر هذا الأمر.
 *
 * ⚠️ حدود هذا الأمر (لا يتجاوزها):
 * - لا يُنشئ أي سجل Transaction.
 * - لا يُعدِّل أي Invoice.
 * - لا يُغيِّر أي PaymentCollection — هي بالفعل settled بشكل صحيح، يُقرَأ
 *   منها فقط للتحقق وحساب paid_at. التحديث يقتصر على عمود SettlementRequest
 *   نفسه (status + paid_at).
 *
 * الاستخدام:
 *   php artisan settlement-requests:backfill-paid --dry-run   (عرض فقط دون تنفيذ)
 *   php artisan settlement-requests:backfill-paid              (تنفيذ فعلي)
 */
class BackfillPaidSettlementRequests extends Command
{
    protected $signature   = 'settlement-requests:backfill-paid {--dry-run : عرض ما سيتغيّر دون حفظ أي تعديل}';
    protected $description = 'يُصلح طلبات تسوية عالقة على حالة غير paid رغم أن كل تحصيلاتها المرتبطة settled بالفعل';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $candidates = SettlementRequest::with('paymentCollections')
            ->where('status', '!=', SettlementRequestStatus::Paid)
            ->get()
            ->filter(function (SettlementRequest $sr) {
                $collections = $sr->paymentCollections;

                return $collections->isNotEmpty()
                    && $collections->every(fn ($pc) => $pc->status === PaymentCollectionStatus::Settled);
            });

        if ($candidates->isEmpty()) {
            $this->info('لا توجد طلبات تسوية عالقة تحتاج إصلاحاً.');

            return self::SUCCESS;
        }

        $this->warn("تم العثور على {$candidates->count()} طلب(طلبات) تسوية عالقة:");

        foreach ($candidates as $sr) {
            $latestSettledAt = $sr->paymentCollections->max('settled_at');

            $this->line(
                " - SettlementRequest #{$sr->id} (user_id={$sr->user_id}, الحالة الحالية={$sr->status->value}) "
                . "→ سيصبح status=paid, paid_at={$latestSettledAt}"
            );

            if (! $dryRun) {
                DB::transaction(function () use ($sr, $latestSettledAt) {
                    $sr->update([
                        'status'  => SettlementRequestStatus::Paid,
                        'paid_at' => $latestSettledAt,
                    ]);
                });

                Log::info('Backfilled stuck SettlementRequest to paid', [
                    'settlement_request_id' => $sr->id,
                    'user_id'               => $sr->user_id,
                    'previous_status'        => $sr->getOriginal('status'),
                    'paid_at'                => $latestSettledAt?->toIso8601String(),
                ]);
            }
        }

        if ($dryRun) {
            $this->comment('تشغيل تجريبي (--dry-run) — لم يُحفظ أي تغيير. أعد التشغيل بدون الخيار للتطبيق الفعلي.');
        } else {
            $this->info('تم إصلاح ' . $candidates->count() . ' طلب(طلبات) بنجاح.');
        }

        return self::SUCCESS;
    }
}
