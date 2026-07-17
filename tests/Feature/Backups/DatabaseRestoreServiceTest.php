<?php

use App\Exceptions\Backup\BackupRestoreException;
use App\Models\Backup;
use App\Services\Backup\DatabaseRestoreProcessRunner;
use App\Services\Backup\DatabaseRestoreService;
use App\Services\Backup\SystemBackupService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

// makeValidSqlFileForRestoreTests() وwithFakeMysqlRestoreConnection() موحّدتان
// في tests/Helpers.php. الثانية تشير backups.restore.connection مؤقتاً إلى
// اتصال mysql وهمي (restore_mysql_test) منفصل تماماً عن database.default
// الحقيقي (sqlite :memory: — اتصال اختبارات Laravel الآمن، لا يتغيّر أبداً)،
// وتُعيد كل شيء لأصله داخل finally مهما كانت النتيجة — بلا أي تسرّب إعداد بين
// الاختبارات، وبلا أي محاولة فتح اتصال PDO فعلي بهذا الاسم (DatabaseRestoreService
// تقرأ فقط مصفوفة الإعدادات لبناء أمر Process).
//
// ⚠️ DatabaseRestoreProcessRunner وSystemBackupService مموَّهتان (Mockery mock)
// في كل اختبار يصل لمرحلة CLI/الاستيراد/النسخة الطارئة — لا يُشغَّل mysql CLI
// حقيقي إطلاقاً، ولا تُنشَأ نسخة احتياطية حقيقية عبر mysqldump. Maintenance
// Mode يُختبَر عبر تمويه واجهة Artisan نفسها (facade قابلة للمحاكاة أصلاً في
// Laravel) بدل تركه يُنفَّذ فعلياً على نظام الملفات.

test('restore rejects sqlite as the restore connection driver', function () {
    $sqlPath = makeValidSqlFileForRestoreTests();
    $original = config('backups.restore.connection');

    Process::fake();
    config(['backups.restore.connection' => 'sqlite']);

    try {
        expect(fn () => app(DatabaseRestoreService::class)->restore($sqlPath))
            ->toThrow(BackupRestoreException::class, 'mysql فقط');
    } finally {
        config(['backups.restore.connection' => $original]);
        @unlink($sqlPath);
    }

    Process::assertNothingRan();
});

test('restore rejects pgsql as the restore connection driver', function () {
    $sqlPath = makeValidSqlFileForRestoreTests();
    $originalConnection = config('backups.restore.connection');
    $originalPgsqlConfig = config('database.connections.fake_pgsql_for_test');

    Process::fake();
    config(['database.connections.fake_pgsql_for_test' => ['driver' => 'pgsql']]);
    config(['backups.restore.connection' => 'fake_pgsql_for_test']);

    try {
        expect(fn () => app(DatabaseRestoreService::class)->restore($sqlPath))
            ->toThrow(BackupRestoreException::class, 'mysql فقط');
    } finally {
        config(['backups.restore.connection' => $originalConnection]);
        config(['database.connections.fake_pgsql_for_test' => $originalPgsqlConfig]);
        @unlink($sqlPath);
    }

    Process::assertNothingRan();
});

test('restore throws and never touches the emergency backup or import when the mysql CLI is unavailable', function () {
    withFakeMysqlRestoreConnection(function () {
        $sqlPath = makeValidSqlFileForRestoreTests();

        $this->mock(DatabaseRestoreProcessRunner::class, function ($mock) {
            $mock->shouldReceive('checkMysqlCliAvailable')
                ->once()
                ->andReturn(Process::result(exitCode: 127, errorOutput: 'mysql: command not found'));
            $mock->shouldNotReceive('runImport');
        });

        $this->mock(SystemBackupService::class, function ($mock) {
            $mock->shouldNotReceive('run');
        });

        try {
            expect(fn () => app(DatabaseRestoreService::class)->restore($sqlPath))
                ->toThrow(BackupRestoreException::class, 'mysql CLI');
        } finally {
            @unlink($sqlPath);
        }
    });
});

test('restore stops before maintenance mode and never imports when the emergency backup fails', function () {
    withFakeMysqlRestoreConnection(function () {
        $sqlPath = makeValidSqlFileForRestoreTests();

        $this->mock(DatabaseRestoreProcessRunner::class, function ($mock) {
            $mock->shouldReceive('checkMysqlCliAvailable')
                ->once()
                ->andReturn(Process::result(exitCode: 0, output: 'mysql  Ver 8.0.0'));
            $mock->shouldNotReceive('runImport');
        });

        $this->mock(SystemBackupService::class, function ($mock) {
            $mock->shouldReceive('run')->once()->andThrow(new RuntimeException('emergency backup boom'));
        });

        // لا يجوز دخول أو خروج Maintenance Mode إطلاقاً — الفشل يحدث قبل ذلك
        Artisan::shouldReceive('call')->with('down')->never();
        Artisan::shouldReceive('call')->with('up')->never();

        try {
            expect(fn () => app(DatabaseRestoreService::class)->restore($sqlPath))
                ->toThrow(BackupRestoreException::class, 'النسخة الاحتياطية الطارئة');
        } finally {
            @unlink($sqlPath);
        }
    });
});

test('restore creates the emergency backup, toggles maintenance mode in order, and imports successfully', function () {
    withFakeMysqlRestoreConnection(function () {
        $sqlPath = makeValidSqlFileForRestoreTests();

        $this->mock(DatabaseRestoreProcessRunner::class, function ($mock) use ($sqlPath) {
            $mock->shouldReceive('checkMysqlCliAvailable')
                ->once()->ordered()
                ->andReturn(Process::result(exitCode: 0, output: 'mysql  Ver 8.0.0'));

            $mock->shouldReceive('runImport')
                ->once()->ordered()
                ->withArgs(fn (array $connection, string $path) => $path === $sqlPath)
                ->andReturn(Process::result(exitCode: 0, output: '', errorOutput: ''));
        });

        $emergencyBackupCreated = false;
        $this->mock(SystemBackupService::class, function ($mock) use (&$emergencyBackupCreated) {
            $mock->shouldReceive('run')->once()->ordered()
                ->andReturnUsing(function (Backup $backup) use (&$emergencyBackupCreated) {
                    $backup->markCompleted(disk: 'local', path: 'fake-emergency.zip.enc', sizeBytes: 10, checksum: 'fake-checksum', encrypted: true);
                    $emergencyBackupCreated = true;
                });
        });

        Artisan::shouldReceive('call')->once()->ordered()->with('down')->andReturn(0);
        Artisan::shouldReceive('call')->once()->ordered()->with('config:clear')->andReturn(0);
        Artisan::shouldReceive('call')->once()->ordered()->with('cache:clear')->andReturn(0);
        Artisan::shouldReceive('call')->once()->ordered()->with('route:clear')->andReturn(0);
        Artisan::shouldReceive('call')->once()->ordered()->with('up')->andReturn(0);

        try {
            app(DatabaseRestoreService::class)->restore($sqlPath);
        } finally {
            @unlink($sqlPath);
        }

        expect($emergencyBackupCreated)->toBeTrue();
    });
});

test('restore exits maintenance mode and surfaces the original STDERR when the mysql import fails', function () {
    withFakeMysqlRestoreConnection(function () {
        $sqlPath = makeValidSqlFileForRestoreTests();

        $this->mock(DatabaseRestoreProcessRunner::class, function ($mock) {
            $mock->shouldReceive('checkMysqlCliAvailable')
                ->once()
                ->andReturn(Process::result(exitCode: 0, output: 'mysql  Ver 8.0.0'));

            $mock->shouldReceive('runImport')
                ->once()
                ->andReturn(Process::result(exitCode: 1, output: '', errorOutput: 'ERROR 1064 (42000): syntax error near SELECT'));
        });

        $this->mock(SystemBackupService::class, function ($mock) {
            $mock->shouldReceive('run')->once()->andReturnUsing(function (Backup $backup) {
                $backup->markCompleted(disk: 'local', path: 'fake-emergency.zip.enc', sizeBytes: 10, checksum: 'fake-checksum', encrypted: true);
            });
        });

        // Maintenance Mode يجب أن يُلغى رغم فشل الاستيراد (finally) — لكن
        // config:clear/cache:clear/route:clear لا يجب أن تُستدعى إطلاقاً لأن
        // refreshApplicationState() لا تُنفَّذ إلا بعد نجاح الاستيراد.
        Artisan::shouldReceive('call')->once()->with('down')->andReturn(0);
        Artisan::shouldReceive('call')->once()->with('up')->andReturn(0);

        try {
            expect(fn () => app(DatabaseRestoreService::class)->restore($sqlPath))
                ->toThrow(BackupRestoreException::class, 'ERROR 1064 (42000): syntax error near SELECT');
        } finally {
            @unlink($sqlPath);
        }
    });
});

test('DatabaseRestoreProcessRunner never places the password in command arguments, only in the process environment', function () {
    $sqlPath = makeValidSqlFileForRestoreTests();

    Process::fake();

    $connection = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'username' => 'restore_user',
        'password' => 'super-secret-password-for-test',
        'database' => 'restore_testing',
    ];

    try {
        app(DatabaseRestoreProcessRunner::class)->runImport($connection, $sqlPath);
    } finally {
        @unlink($sqlPath);
    }

    Process::assertRan(function ($process) use ($connection) {
        $commandString = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;

        // السلاح الأمني الأساسي: كلمة المرور لا تظهر إطلاقاً داخل سطر الأوامر
        // نفسه (وبالتالي لن تظهر أبداً في `ps aux`).
        return ! str_contains($commandString, $connection['password']);
    });
});
