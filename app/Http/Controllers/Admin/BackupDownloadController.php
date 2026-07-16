<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * BackupDownloadController — تنزيل أرشيف نسخة احتياطية تشغيلية (super_admin فقط).
 *
 * ⚠️ الملف يُنزَّل كما هو **مشفَّراً** (بامتداد .enc) — لا يُفكّ تشفيره هنا إطلاقاً.
 * فك التشفير يتم فقط محلياً بواسطة الأدمن عبر `php artisan backup:restore`
 * (أو BackupEncryptor يدوياً) بعد التنزيل، باستخدام BACKUP_ENCRYPTION_KEY من
 * بيئة الخادم المصرَّح لها فقط. راجع docs/BACKUP-SYSTEM.md.
 */
class BackupDownloadController extends Controller
{
    public function __invoke(Request $request, string $backup): StreamedResponse
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        /** @var Backup|null $record */
        $record = Backup::query()->find($backup);

        abort_unless($record && $record->disk && $record->path, 404);
        abort_unless(Storage::disk($record->disk)->exists($record->path), 404, 'ملف النسخة غير موجود على القرص.');

        ActivityLog::recordFor(
            eventType: 'backup.downloaded',
            entityType: Backup::class,
            entityId: $record->id,
            metadata: ['name' => $record->name],
        );

        $disk = $record->disk;
        $path = $record->path;

        // ⚠️ لا نستخدم Storage::download() هنا: Flysystem يحاول اكتشاف
        // mime_type تلقائياً لملفات .enc (لا يعرفها) ويرمي
        // UnableToRetrieveMetadata. نستخدم streamDownload() + readStream()
        // بدلاً من ذلك: يتجاوز اكتشاف الـ MIME كلياً، ولا يحمّل الملف كاملاً
        // في الذاكرة (Storage::get()) — مهم لأن نسخ full قد تكون كبيرة جداً.
        return response()->streamDownload(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);

            if (! is_resource($stream)) {
                return;
            }

            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $record->name.'.enc', [
            // نوع عام غير قابل للتنفيذ/العرض — لا يبدو كملف ZIP عادي، ويمنع
            // المتصفح من محاولة "تخمين" النوع الحقيقي (nosniff).
            'Content-Type'           => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store, no-cache, must-revalidate',
            'Pragma'                 => 'no-cache',
            'Expires'                => '0',
        ]);
    }
}
