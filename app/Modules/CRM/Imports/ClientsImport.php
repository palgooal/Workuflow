<?php

namespace App\Modules\CRM\Imports;

use App\Models\Client;
use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Models\ClientImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * ClientsImport — استيراد العملاء من Excel/CSV
 *
 * Sprint 4 — S4.1
 * - WithHeadingRow: يقرأ السطر الأول كـ headers (عربي + إنجليزي)
 * - WithChunkReading: معالجة على دفعات لتجنب memory overflow
 * - WithBatchInserts: إدخال دفعي لتحسين الأداء
 *
 * خريطة الأعمدة المقبولة (عربي أو إنجليزي):
 *   الاسم / name → name
 *   البريد الالكتروني / email → email
 *   الهاتف / phone → phone
 *   الشركة / company → company
 *   المسمى الوظيفي / position → position
 *   المصدر / source → source
 *   الحالة / status → status
 *   العنوان / address → address
 *   المدينة / city → city
 *   الدولة / country → country
 *   الموقع الالكتروني / website → website
 *   ملاحظات / notes → notes
 */
class ClientsImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts
{
    // ==================== Config ====================

    public const BATCH_SIZE  = 500;
    public const CHUNK_SIZE  = 1000;
    public const MAX_PER_ROW = 50; // max characters for short string fields

    // خريطة: اسم العمود في الملف → اسم الحقل في قاعدة البيانات
    private const HEADING_MAP = [
        // عربي
        'الاسم'                  => 'name',
        'البريد الالكتروني'     => 'email',
        'البريد الإلكتروني'     => 'email',
        'الهاتف'                 => 'phone',
        'الشركة'                 => 'company',
        'المسمى الوظيفي'         => 'position',
        'المصدر'                 => 'source',
        'الحالة'                 => 'status',
        'العنوان'                => 'address',
        'المدينة'                => 'city',
        'الدولة'                 => 'country',
        'الموقع الالكتروني'     => 'website',
        'الموقع الإلكتروني'     => 'website',
        'ملاحظات'                => 'notes',
        // إنجليزي (WithHeadingRow يحوّل إلى lowercase تلقائياً)
        'name'     => 'name',
        'email'    => 'email',
        'phone'    => 'phone',
        'company'  => 'company',
        'position' => 'position',
        'source'   => 'source',
        'status'   => 'status',
        'address'  => 'address',
        'city'     => 'city',
        'country'  => 'country',
        'website'  => 'website',
        'notes'    => 'notes',
    ];

    // ==================== State ====================

    private int $successCount = 0;
    private int $errorCount   = 0;
    private int $skippedCount = 0;
    private array $errors     = [];
    private int $rowOffset    = 2; // يبدأ من السطر 2 (بعد headers)

    public function __construct(
        private readonly int            $userId,
        private readonly ClientImportLog $log,
        private readonly bool           $skipDuplicates  = true,
        private readonly bool           $updateExisting  = false,
        private readonly array          $columnMap       = [],
    ) {}

    // ==================== Main ====================

    /**
     * معالجة كل chunk من الصفوف
     */
    public function collection(Collection $rows): void
    {
        // تحديث إجمالي الصفوف في أول chunk
        if ($this->rowOffset === 2) {
            // نُقدِّر العدد الإجمالي (يُحدَّث لاحقاً بالقيمة الحقيقية)
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $this->rowOffset + $index;
            $data      = $this->mapRow($row->toArray());

            // تخطي الصفوف الفارغة
            if (empty($data['name'])) {
                $this->skippedCount++;
                continue;
            }

            // التحقق من الصحة
            $validator = $this->makeValidator($data);
            if ($validator->fails()) {
                $this->errorCount++;
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'data'   => ['name' => $data['name'] ?? '', 'email' => $data['email'] ?? ''],
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $validated = $validator->validated();

            try {
                $this->upsertClient($validated);
                $this->successCount++;
            } catch (\Throwable $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'data'   => ['name' => $validated['name'] ?? '', 'email' => $validated['email'] ?? ''],
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        $this->rowOffset += $rows->count();

        // تحديث الـ log بعد كل chunk
        $this->syncLog();
    }

    // ==================== Maatwebsite Contracts ====================

    public function batchSize(): int
    {
        return self::BATCH_SIZE;
    }

    public function chunkSize(): int
    {
        return self::CHUNK_SIZE;
    }

    // ==================== Helpers ====================

    /**
     * تعيين أعمدة الملف إلى حقول قاعدة البيانات
     * يقبل الأعمدة العربية والإنجليزية، ويتجاهل الأعمدة غير المعروفة
     */
    private function mapRow(array $rawRow): array
    {
        $mapped = [];

        foreach ($rawRow as $heading => $value) {
            // WithHeadingRow يحوّل الـ headings إلى slug (spaces → underscores, lowercase)
            // نُعيد الحرف الأصلي باستخدام HEADING_MAP
            $normalizedHeading = mb_strtolower(trim((string) $heading));
            $field = self::HEADING_MAP[$normalizedHeading]
                  ?? self::HEADING_MAP[str_replace('_', ' ', $normalizedHeading)]
                  ?? null;

            // تحقق من columnMap المخصص (field mapping من الـ wizard)
            if (!$field && isset($this->columnMap[$heading])) {
                $field = $this->columnMap[$heading];
            }

            if ($field) {
                $mapped[$field] = $value !== null ? trim((string) $value) : null;
            }
        }

        return $mapped;
    }

    /**
     * قواعد التحقق لكل صف
     */
    private function makeValidator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $statusValues = ClientStatus::values();
        $sourceValues = ClientSource::values();

        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'company'  => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'source'   => ['nullable', 'string', 'in:' . implode(',', $sourceValues)],
            'status'   => ['nullable', 'string', 'in:' . implode(',', $statusValues)],
            'address'  => ['nullable', 'string', 'max:500'],
            'city'     => ['nullable', 'string', 'max:100'],
            'country'  => ['nullable', 'string', 'max:100'],
            'website'  => ['nullable', 'url', 'max:255'],
            'notes'    => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.email'   => 'البريد الإلكتروني غير صالح',
            'website.url'   => 'الموقع الإلكتروني غير صالح',
            'source.in'     => 'قيمة المصدر غير مقبولة',
            'status.in'     => 'قيمة الحالة غير مقبولة',
        ]);
    }

    /**
     * إدخال أو تحديث العميل
     */
    private function upsertClient(array $data): void
    {
        $payload = array_filter([
            'user_id'  => $this->userId,
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'company'  => $data['company'] ?? null,
            'position' => $data['position'] ?? null,
            'source'   => $data['source'] ?? ClientSource::Import->value,
            'status'   => $data['status'] ?? ClientStatus::Prospect->value,
            'address'  => $data['address'] ?? null,
            'city'     => $data['city'] ?? null,
            'country'  => $data['country'] ?? null,
            'website'  => $data['website'] ?? null,
            'notes'    => $data['notes'] ?? null,
        ], fn ($v) => $v !== null);

        $email = $data['email'] ?? null;

        if ($email && $this->updateExisting) {
            // updateOrCreate: تحديث إذا موجود، إنشاء إذا لا
            Client::updateOrCreate(
                ['user_id' => $this->userId, 'email' => $email],
                $payload
            );
        } elseif ($email && $this->skipDuplicates) {
            // تخطي إذا موجود مسبقاً
            $exists = Client::where('user_id', $this->userId)
                            ->where('email', $email)
                            ->exists();
            if ($exists) {
                $this->successCount--; // لن تُحسب كنجاح — تُعاد كـ skipped
                $this->skippedCount++;
                return;
            }
            Client::create($payload);
        } else {
            // إضافة بدون التحقق من التكرار (للعملاء بلا بريد)
            Client::create($payload);
        }
    }

    /**
     * مزامنة نتائج الـ chunk مع سجل الاستيراد
     */
    private function syncLog(): void
    {
        $this->log->update([
            'success_count' => $this->successCount,
            'error_count'   => $this->errorCount,
            'skipped_count' => $this->skippedCount,
            'errors'        => array_slice($this->errors, 0, 100), // أول 100 خطأ فقط
        ]);
    }

    // ==================== Accessors ====================

    public function getSuccessCount(): int { return $this->successCount; }
    public function getErrorCount(): int   { return $this->errorCount;   }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getErrors(): array     { return $this->errors;       }
}
