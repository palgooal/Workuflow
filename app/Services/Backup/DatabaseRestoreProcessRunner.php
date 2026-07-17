<?php

namespace App\Services\Backup;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * DatabaseRestoreProcessRunner — الغلاف الوحيد حول Illuminate\Support\Facades\Process
 * لخطوتَي "فحص mysql CLI" و"تنفيذ الاستيراد الفعلي" داخل DatabaseRestoreService.
 *
 * ⚠️ سبب وجود هذه الفئة الصغيرة تحديداً: DatabaseRestoreService تعتمد عليها عبر
 * Dependency Injection (بدل استدعاء Process مباشرة داخل كل خطوة) حتى يمكن
 * استبدالها بالكامل في الاختبارات (Mockery mock) لمحاكاة كل سيناريو (نجاح/فشل
 * CLI، نجاح/فشل الاستيراد مع STDERR محدَّد) دون أي اعتماد على وجود mysql CLI
 * حقيقي على الجهاز الذي تُشغَّل عليه الاختبارات، ودون تشغيل أي عملية فرعية
 * فعلية أثناء Feature Tests.
 *
 * ⚠️ أمان: لا تُمرَّر كلمة المرور أبداً كجزء من مصفوفة الأوامر (فلن تظهر أبداً
 * في `ps aux`) — تُمرَّر فقط عبر متغيّر بيئة MYSQL_PWD خاص بهذه العملية
 * الفرعية، بنفس الأسلوب المستخدَم فعلياً وبنجاح في
 * SystemBackupService::dumpDatabase() لعملية mysqldump.
 */
class DatabaseRestoreProcessRunner
{
    public function checkMysqlCliAvailable(): ProcessResult
    {
        return Process::run(['mysql', '--version']);
    }

    /** @param array<string,mixed> $connection */
    public function runImport(array $connection, string $sqlPath): ProcessResult
    {
        $command = [
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ];

        return Process::timeout(1800)
            ->env(['MYSQL_PWD' => $connection['password'] ?? ''])
            ->input(File::get($sqlPath))
            ->run($command);
    }
}
