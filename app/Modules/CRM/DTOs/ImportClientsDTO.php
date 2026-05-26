<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Requests\ImportClientRequest;
use Illuminate\Support\Str;

final readonly class ImportClientsDTO
{
    public function __construct(
        public int     $userId,
        public string  $filePath,        // المسار المؤقت للملف بعد الرفع
        public string  $originalName,   // اسم الملف الأصلي
        public string  $idempotencyKey, // CHAR(64) — SHA-256 hash أو UUID
        public bool    $skipDuplicates  = true,
        public bool    $updateExisting  = false,
        public array   $columnMap       = [],  // ['name' => 'col_A', 'email' => 'col_B', ...]
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(ImportClientRequest $request): self
    {
        $file = $request->file('file');

        // استخدام idempotency_key المُرسَل أو إنشاء واحد من hash الملف
        $idempotencyKey = $request->filled('idempotency_key')
            ? $request->string('idempotency_key')->toString()
            : hash('sha256', $file->getClientOriginalName() . $file->getSize() . $request->user()->id);

        // حفظ الملف مؤقتاً في disk local
        $tempPath = $file->storeAs(
            'imports/tmp',
            Str::ulid() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        return new self(
            userId:          $request->user()->id,
            filePath:        $tempPath,
            originalName:    $file->getClientOriginalName(),
            idempotencyKey:  $idempotencyKey,
            skipDuplicates:  $request->boolean('skip_duplicates', true),
            updateExisting:  $request->boolean('update_existing', false),
            columnMap:       (array) $request->input('column_map', []),
        );
    }

    // ==================== Helpers ====================

    public function diskPath(): string
    {
        return $this->filePath;
    }

    public function resolveColumn(string $field, string $default): string
    {
        return $this->columnMap[$field] ?? $default;
    }
}
