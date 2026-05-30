<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['key', 'name', 'subject', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * جلب قالب وتطبيق المتغيرات عليه
     * يُرجع ['subject' => '...', 'body' => '...'] أو null
     */
    public static function render(string $key, array $vars = []): ?array
    {
        $template = static::where('key', $key)->where('is_active', true)->first();
        if (! $template) return null;

        $subject = $template->subject;
        $body    = $template->body;

        foreach ($vars as $placeholder => $value) {
            $subject = str_replace($placeholder, $value, $subject);
            $body    = str_replace($placeholder, $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }
}
