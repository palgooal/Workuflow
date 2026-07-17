<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;

/**
 * AtomicFilesystem — غلاف صغير حول عمليات الملفات الخام (rename/copy/delete)
 * التي تعتمد عليها FilesRestoreService لاستعادة storage/app.
 *
 * ⚠️ سبب وجود هذه الفئة تحديداً: عزل هذه العمليات عبر Dependency Injection
 * يسمح للاختبارات بعمل Mock/Partial-Mock حقيقي لها مباشرة (methods عامة —
 * لا حاجة لـ shouldAllowMockingProtectedMethods() إطلاقاً) لمحاكاة فشل نقل
 * محدَّد (نسخة الطوارئ أو النقل النهائي) بدقة وبشكل محمول بين الأنظمة، دون أي
 * حاجة للتلاعب بصلاحيات ملفات النظام الحقيقي أو الاعتماد على سلوك خاص بنظام
 * تشغيل معيّن. لا منطق فيها إطلاقاً غير هذا التفويض المباشر — لا تُستخدَم في
 * أي مكان آخر خارج FilesRestoreService.
 */
class AtomicFilesystem
{
    public function move(string $from, string $to): bool
    {
        return @rename($from, $to);
    }

    public function copy(string $from, string $to): bool
    {
        return copy($from, $to);
    }

    public function deleteDirectory(string $directory): bool
    {
        return File::deleteDirectory($directory);
    }
}
