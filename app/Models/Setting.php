<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['key', 'value', 'group'];

    // ── API ──────────────────────────────────────────────────────────────

    /** جلب قيمة واحدة مع قيمة افتراضية */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /** حفظ قيمة واحدة */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        Cache::forget("setting:{$key}");
        Cache::forget("settings:group:{$group}");
    }

    /** جلب مجموعة كاملة كـ array ['key' => 'value'] */
    public static function group(string $group): array
    {
        return Cache::rememberForever("settings:group:{$group}", function () use ($group) {
            return static::where('group', $group)
                ->pluck('value', 'key')
                ->all();
        });
    }

    /** حفظ مجموعة كاملة دفعة واحدة */
    public static function setGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
            Cache::forget("setting:{$key}");
        }
        Cache::forget("settings:group:{$group}");
    }
}
