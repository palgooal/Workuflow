<?php

namespace App\Exceptions\Backup;

use RuntimeException;

/**
 * يُرمى عند أي مشكلة في manifest.json أو بنية الأرشيف بعد فك التشفير بنجاح:
 * manifest.json مفقود/غير صالح، نوع النسخة لا يطابق السجل، database.sql
 * مفقود، أو مجلد storage/ مفقود لنسخة من نوع "كاملة".
 */
class BackupManifestException extends RuntimeException
{
}
