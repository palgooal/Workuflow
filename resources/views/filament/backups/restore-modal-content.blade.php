{{--
    محتوى عرض فقط (بدون أي Filament Form Components) — يُمرَّر عبر
    Action::modalContent() في app/Filament/Resources/BackupResource.php.
    لا يحتوي أي حقل إدخال؛ حقل التأكيد الوحيد (confirmation) يبقى في form()
    الخاص بالـAction نفسها.
--}}
<div class="space-y-4">
    @if ($inspectionError)
        <div class="rounded-lg border border-danger-300 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
            تعذّرت قراءة تفاصيل الأرشيف: {{ $inspectionError }}
        </div>
    @else
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-700 sm:grid-cols-2">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">نوع النسخة</dt>
                <dd class="text-gray-950 dark:text-white">{{ $record->type === \App\Support\Enums\BackupType::Full ? 'Full Backup' : 'Database Backup' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">تاريخ الإنشاء</dt>
                <dd class="text-gray-950 dark:text-white">{{ optional($record->completed_at)->format('Y-m-d H:i:s') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">الحجم</dt>
                <dd class="text-gray-950 dark:text-white">{{ $record->humanSize() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">عدد الملفات</dt>
                <dd class="text-gray-950 dark:text-white">{{ $fileCount !== null ? $fileCount : '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Checksum</dt>
                <dd class="break-all font-mono text-xs text-gray-950 dark:text-white">{{ $record->checksum ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Laravel Version</dt>
                <dd class="text-gray-950 dark:text-white">{{ $manifest['laravel'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">PHP Version</dt>
                <dd class="text-gray-950 dark:text-white">غير مسجَّل في manifest هذه النسخة</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Database Driver</dt>
                <dd class="text-gray-950 dark:text-white">{{ $manifest['database']['driver'] ?? '—' }}</dd>
            </div>
        </dl>
    @endif

    <div class="rounded-lg border border-danger-600 bg-danger-50 p-4 font-bold text-danger-700 dark:bg-danger-950 dark:text-danger-300">
        <p>⚠️ سيتم استبدال قاعدة البيانات الحالية.</p>
        @if ($record->type === \App\Support\Enums\BackupType::Full)
            <p>وفي النسخة الكاملة سيتم أيضاً استبدال ملفات storage/app بالكامل.</p>
        @endif
        <p>لا يمكن التراجع عن هذه العملية.</p>
        <p>سيتم إنشاء نسخة طوارئ تلقائياً قبل البدء.</p>
        <p class="mt-2 font-normal">
            {{ $record->type === \App\Support\Enums\BackupType::Full ? 'سيتم استعادة قاعدة البيانات وملفات storage/app.' : 'سيتم استعادة قاعدة البيانات فقط.' }}
        </p>
    </div>
</div>
