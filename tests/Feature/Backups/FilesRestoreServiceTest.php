<?php

use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Services\Backup\AtomicFilesystem;
use App\Services\Backup\FilesRestoreService;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\File;

// withFakeStorageAppDir() وmakeFakeExtractedStorageAppTree() موحّدتان في
// tests/Helpers.php. الأولى توجّه backups.restore.storage_app_path نحو مجلد
// مؤقت معزول تماماً عن storage/app الحقيقي للمشروع طوال مدة $callback، وتعيد
// كل شيء لأصله (بما في ذلك حذف أي .__old/.__restore متبقٍّ) في finally مهما
// كانت النتيجة — لا يوجد أي اختبار هنا يلمس storage/app الحقيقي إطلاقاً.
// الثانية تبني شجرة $extractDir/storage/app بمحتوى مُعطى، تماماً كما تُنتِجها
// RestoreService::extractSafely() فعلياً بعد فك ضغط أرشيف حقيقي.
//
// ⚠️ محاكاة فشل rename محدَّد (نسخة الطوارئ أو النقل النهائي) تتم عبر
// AtomicFilesystem::makePartial() (methods عامة) لا عبر Mock على methods
// محمية لـFilesRestoreService نفسها — يمنع الحاجة لـ
// shouldAllowMockingProtectedMethods() نهائياً، وباقي العمليات (copy/delete/
// move الأخرى) تمر فعلياً عبر التنفيذ الحقيقي تلقائياً (makePartial).

test('restore does nothing at all for a database-type backup', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        file_put_contents($fakeAppDir.'/must-remain.txt', 'untouched');

        // extractDir بدون storage/app إطلاقاً — لا يهم، لأن type=database تُرجع فوراً
        $extractDir = sys_get_temp_dir().'/files-restore-db-noop-'.uniqid();
        mkdir($extractDir, 0777, true);

        try {
            app(FilesRestoreService::class)->restore($extractDir, BackupType::Database);
        } finally {
            File::deleteDirectory($extractDir);
        }

        expect(file_get_contents($fakeAppDir.'/must-remain.txt'))->toBe('untouched');
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
        expect(is_dir($fakeAppDir.'.__restore'))->toBeFalse();
    });
});

test('restore replaces storage/app atomically so its content matches the backup exactly', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        // موجود حالياً، وموجود أيضاً في النسخة — يجب أن يُستبدَل بمحتوى النسخة
        file_put_contents($fakeAppDir.'/keep.txt', 'old content that must be overwritten by the backup version');

        // موجود حالياً، وغير موجود في النسخة — يجب أن يُحذَف بالكامل
        File::ensureDirectoryExists($fakeAppDir.'/to-be-deleted-dir');
        file_put_contents($fakeAppDir.'/to-be-deleted-dir/gone.txt', 'this whole file and directory must disappear');

        $extractDir = makeFakeExtractedStorageAppTree([
            'keep.txt'                => 'content from backup — must win',
            'public/new-file.txt'     => 'brand new file that did not exist before restore',
            'private/nested/deep.txt' => 'nested new file',
        ]);

        try {
            app(FilesRestoreService::class)->restore($extractDir, BackupType::Full);
        } finally {
            File::deleteDirectory($extractDir);
        }

        expect(file_get_contents($fakeAppDir.'/keep.txt'))->toBe('content from backup — must win');
        expect(file_get_contents($fakeAppDir.'/public/new-file.txt'))->toBe('brand new file that did not exist before restore');
        expect(file_get_contents($fakeAppDir.'/private/nested/deep.txt'))->toBe('nested new file');
        expect(is_dir($fakeAppDir.'/to-be-deleted-dir'))->toBeFalse();

        // لا بقايا مجلدات مؤقتة بعد النجاح
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
        expect(is_dir($fakeAppDir.'.__restore'))->toBeFalse();
    });
});

test('restore throws BackupManifestException when storage/app is missing from the extracted archive for a Full backup', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        file_put_contents($fakeAppDir.'/untouched.txt', 'must remain exactly as-is');

        $extractDir = sys_get_temp_dir().'/files-restore-empty-source-'.uniqid();
        mkdir($extractDir, 0777, true); // بدون storage/app إطلاقاً

        try {
            expect(fn () => app(FilesRestoreService::class)->restore($extractDir, BackupType::Full))
                ->toThrow(BackupManifestException::class);
        } finally {
            File::deleteDirectory($extractDir);
        }

        expect(file_get_contents($fakeAppDir.'/untouched.txt'))->toBe('must remain exactly as-is');
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
    });
});

test('restore rejects a symlink found inside the extracted storage/app tree before touching the real directory', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        file_put_contents($fakeAppDir.'/untouched.txt', 'must remain exactly as-is');

        $extractDir = sys_get_temp_dir().'/files-restore-symlink-source-'.uniqid();
        $appDir = $extractDir.'/storage/app';
        mkdir($appDir, 0777, true);
        file_put_contents($appDir.'/real-file.txt', 'ok');

        $linkTarget = sys_get_temp_dir().'/files-restore-symlink-target-'.uniqid().'.txt';
        file_put_contents($linkTarget, 'content outside the backup archive tree');
        $linkCreated = @symlink($linkTarget, $appDir.'/malicious-link.txt');

        // بعض الأنظمة (خصوصاً Windows بدون صلاحيات مرتفعة) ترفض إنشاء symlink
        // إطلاقاً — هذا قيد بيئي وليس فشلاً في FilesRestoreService، فنتخطى
        // الاختبار بوضوح بدل اعتباره فاشلاً.
        if (! $linkCreated) {
            @unlink($linkTarget);
            File::deleteDirectory($extractDir);

            $this->markTestSkipped('تعذّر إنشاء symlink في بيئة الاختبار الحالية (قيود نظام التشغيل أو الصلاحيات) — هذا الاختبار يتطلب دعم symlink فعلياً.');
        }

        try {
            expect(fn () => app(FilesRestoreService::class)->restore($extractDir, BackupType::Full))
                ->toThrow(BackupRestoreException::class);
        } finally {
            @unlink($appDir.'/malicious-link.txt');
            @unlink($linkTarget);
            File::deleteDirectory($extractDir);
        }

        // الرفض حدث قبل لمس storage/app الحقيقي إطلاقاً
        expect(file_get_contents($fakeAppDir.'/untouched.txt'))->toBe('must remain exactly as-is');
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
        expect(is_dir($fakeAppDir.'.__restore'))->toBeFalse();
    });
});

test('restore stops entirely and never begins staging when the emergency backup rename fails', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        file_put_contents($fakeAppDir.'/untouched.txt', 'must remain exactly as-is');

        $extractDir = makeFakeExtractedStorageAppTree(['some-file.txt' => 'content']);

        // AtomicFilesystem::move() هي method عامة — Mock/Partial-Mock مباشر
        // عليها لا يحتاج shouldAllowMockingProtectedMethods() إطلاقاً.
        $atomicFs = Mockery::mock(AtomicFilesystem::class)->makePartial();
        $atomicFs->shouldReceive('move')
            ->once()
            ->with($fakeAppDir, $fakeAppDir.'.__old')
            ->andReturn(false);

        $this->app->instance(AtomicFilesystem::class, $atomicFs);

        try {
            expect(fn () => app(FilesRestoreService::class)->restore($extractDir, BackupType::Full))
                ->toThrow(BackupRestoreException::class, 'نسخة الطوارئ');
        } finally {
            File::deleteDirectory($extractDir);
        }

        expect(file_get_contents($fakeAppDir.'/untouched.txt'))->toBe('must remain exactly as-is');
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
        expect(is_dir($fakeAppDir.'.__restore'))->toBeFalse();
    });
});

test('restore performs a full rollback when the final rename fails', function () {
    withFakeStorageAppDir(function (string $fakeAppDir) {
        file_put_contents($fakeAppDir.'/original.txt', 'this must be back exactly as it was after rollback');

        $extractDir = makeFakeExtractedStorageAppTree(['from-backup.txt' => 'should never end up in storage/app']);

        // فقط النقل النهائي (.__restore → app) مموَّه ليفشل — باقي عمليات
        // move/copy/deleteDirectory (نسخة الطوارئ، بناء .__restore، الـRollback
        // نفسه) تمر فعلياً عبر التنفيذ الحقيقي تلقائياً بفضل makePartial().
        $atomicFs = Mockery::mock(AtomicFilesystem::class)->makePartial();
        $atomicFs->shouldReceive('move')
            ->once()
            ->with($fakeAppDir.'.__restore', $fakeAppDir)
            ->andReturn(false);

        $this->app->instance(AtomicFilesystem::class, $atomicFs);

        try {
            expect(fn () => app(FilesRestoreService::class)->restore($extractDir, BackupType::Full))
                ->toThrow(BackupRestoreException::class, 'النقل النهائي');
        } finally {
            File::deleteDirectory($extractDir);
        }

        // storage/app عاد بالضبط لمحتواه الأصلي قبل المحاولة — Rollback كامل
        expect(file_get_contents($fakeAppDir.'/original.txt'))->toBe('this must be back exactly as it was after rollback');
        expect(file_exists($fakeAppDir.'/from-backup.txt'))->toBeFalse();

        // تنظيف كامل لكل المجلدات المؤقتة بعد الـRollback
        expect(is_dir($fakeAppDir.'.__old'))->toBeFalse();
        expect(is_dir($fakeAppDir.'.__restore'))->toBeFalse();
    });
});
