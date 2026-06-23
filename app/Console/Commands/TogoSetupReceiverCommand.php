<?php

namespace App\Console\Commands;

use App\Modules\Billing\Services\TogoPaymentService;
use Illuminate\Console\Command;

/**
 * أمر لإنشاء Togo receiver address (يُنفَّذ مرة واحدة فقط عند الإعداد الأولي)
 *
 * الاستخدام:
 *   php artisan togo:setup-receiver
 *
 * بعد التشغيل: انسخ الـ ID الظاهر وأضفه لـ .env كـ TOGO_RECEIVER_ADDRESS_ID
 */
class TogoSetupReceiverCommand extends Command
{
    protected $signature   = 'togo:setup-receiver';
    protected $description = 'إنشاء Togo receiver address وطباعة الـ ID لإضافته لـ .env';

    public function handle(TogoPaymentService $togo): int
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  إعداد Togo Receiver Address');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // التحقق من وجود API key أولاً
        if (empty(config('billing.togo.api_key'))) {
            $this->error('TOGO_API_KEY غير موجود في .env — أضفه أولاً.');
            return Command::FAILURE;
        }

        // إذا كان الـ ID موجوداً بالفعل
        if ($existing = config('billing.togo.receiver_address_id')) {
            $this->warn("يوجد receiver address مضبوط بالفعل: {$existing}");
            if (! $this->confirm('هل تريد إنشاء عنوان جديد؟', false)) {
                return Command::SUCCESS;
            }
        }

        $this->line('أدخل بيانات صاحب الحساب (مستقبل المدفوعات):');
        $this->newLine();

        $name        = $this->ask('الاسم الكامل');
        $phone       = $this->ask('رقم الهاتف (مثال: +970591234567)');
        $countryCode = $this->ask('كود الدولة (مثال: PS أو JO)', 'PS');
        $countryName = $this->ask('اسم الدولة (مثال: Palestine)', 'Palestine');
        $city        = $this->ask('المدينة');
        $details     = $this->ask('تفاصيل إضافية (اختياري)', '');
        $whatsapp    = $this->confirm('هل الرقم متصل بواتساب؟', true);

        $this->newLine();
        $this->line('جاري الإنشاء...');

        try {
            $data = $togo->createReceiverAddress(
                name: $name,
                phone: $phone,
                countryCode: strtoupper($countryCode),
                countryName: $countryName,
                city: $city,
                details: $details,
                phoneConnectedToWhatsapp: $whatsapp,
            );
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $id = $data['id'];

        $this->newLine();
        $this->info('✓ تم إنشاء receiver address بنجاح!');
        $this->newLine();
        $this->line('أضف السطر التالي لملف .env:');
        $this->newLine();
        $this->line("  <fg=yellow>TOGO_RECEIVER_ADDRESS_ID={$id}</>");
        $this->newLine();
        $this->warn('⚠  لا تشاركه مع أحد. هذا الـ ID يرتبط بحسابك في Togo.');

        return Command::SUCCESS;
    }
}
