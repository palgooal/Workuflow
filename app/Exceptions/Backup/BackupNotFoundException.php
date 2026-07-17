<?php

namespace App\Exceptions\Backup;

use RuntimeException;

/**
 * يُرمى عندما لا يوجد سجل Backup بالمعرّف المطلوب، أو كان موجوداً لكن في
 * حالة لا يمكن استعادته منها (مثل status != completed).
 */
class BackupNotFoundException extends RuntimeException
{
}
