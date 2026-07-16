<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\Export\ExportUserDataJob;
use App\Models\ActivityLog;
use App\Models\DataExportRequest;
use App\Support\Enums\DataExportStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * DataExportController — "تنزيل نسخة من بياناتي" (لوحة حساب المستخدم).
 *
 * ⚠️ كل استعلام هنا مقيَّد صراحةً بـ where('user_id', $request->user()->id) —
 * راجع docs/DATA-EXPORT.md قبل التعديل. هذا التحكم ليس أداة استعادة (Restore).
 */
class DataExportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // منع أكثر من طلب نشط واحد لنفس المستخدم
        $hasActive = DataExportRequest::query()
            ->where('user_id', $user->id)
            ->active()
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'لديك طلب تصدير قيد المعالجة بالفعل — انتظر اكتماله قبل طلب نسخة جديدة.')
                ->withFragment('data');
        }

        // Rate limit: طلب واحد كل N ساعة لكل مستخدم
        $rateLimitHours = (int) config('backups.user_export.rate_limit_hours', 24);
        $limiterKey     = "data-export:{$user->id}";

        if (RateLimiter::tooManyAttempts($limiterKey, 1)) {
            $availableInMinutes = (int) ceil(RateLimiter::availableIn($limiterKey) / 60);
            return back()->with('error', "يمكنك طلب نسخة جديدة بعد {$availableInMinutes} دقيقة تقريباً (حد أقصى: طلب واحد كل {$rateLimitHours} ساعة).")
                ->withFragment('data');
        }

        $exportRequest = DataExportRequest::create([
            'user_id'      => $user->id,
            'status'       => DataExportStatus::Pending,
            'requested_at' => now(),
        ]);

        RateLimiter::hit($limiterKey, $rateLimitHours * 3600);

        ActivityLog::recordFor(
            eventType: 'export.requested',
            entityType: DataExportRequest::class,
            entityId: $exportRequest->id,
        );

        ExportUserDataJob::dispatch($exportRequest->id);

        return back()->with('success', 'تم استلام طلبك — سنُشعرك عند جاهزية النسخة للتنزيل.')
            ->withFragment('data');
    }

    /**
     * إنشاء رابط تنزيل موقّع مؤقت (Signed URL) لطلب مكتمل يخص المستخدم الحالي فقط.
     */
    public function download(Request $request, string $dataExportRequest): RedirectResponse|StreamedResponse
    {
        $user = $request->user();

        $exportRequest = DataExportRequest::query()
            ->where('id', $dataExportRequest)
            ->where('user_id', $user->id) // ⚠️ عزل صريح — لا اعتماد على أي شيء آخر
            ->first();

        if (! $exportRequest) {
            abort(404);
        }

        if (! $exportRequest->isDownloadable()) {
            return back()->with('error', 'رابط التنزيل غير متاح حالياً (إما لم تكتمل النسخة أو انتهت صلاحيتها).')
                ->withFragment('data');
        }

        $disk = config('backups.user_export.disk');

        if (! Storage::disk($disk)->exists($exportRequest->file_path)) {
            return back()->with('error', 'تعذّر العثور على ملف النسخة — قد يكون قد حُذف بعد انتهاء الصلاحية.')
                ->withFragment('data');
        }

        ActivityLog::recordFor(
            eventType: 'export.downloaded',
            entityType: DataExportRequest::class,
            entityId: $exportRequest->id,
        );

        $filename = 'my-data-export.zip';

        return Storage::disk($disk)->download($exportRequest->file_path, $filename);
    }

    /**
     * يُستدعى داخلياً لبناء رابط Signed URL يُعرَض للمستخدم (وليس رابط التخزين المباشر).
     */
    public static function signedDownloadUrl(DataExportRequest $exportRequest): string
    {
        $ttl = (int) config('backups.user_export.signed_url_ttl_minutes', 60);

        return URL::temporarySignedRoute(
            'data-export.download',
            now()->addMinutes($ttl),
            ['dataExportRequest' => $exportRequest->id]
        );
    }
}
