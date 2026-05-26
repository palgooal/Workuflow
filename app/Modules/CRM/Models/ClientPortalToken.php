<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Enums\PortalPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClientPortalToken — رمز بوابة العميل
 *
 * ⚠️ أمان حرج (C-04 Fix):
 * - عمود token = hash('sha256', $plaintext) — لا تُخزَّن القيمة الأصلية
 * - القيمة الأصلية تُعرض مرة واحدة عند الإنشاء فقط
 */
class ClientPortalToken extends Model
{
    protected $table = 'client_portal_tokens';

    protected $fillable = [
        'client_id',
        'token',            // SHA-256 hash فقط
        'permissions',
        'expires_at',
        'last_used_at',
        'last_used_ip',
        'created_by',
    ];

    // لا تُعيد الـ token في JSON responses أبداً
    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'permissions'  => 'array',
            'expires_at'   => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // ==================== Helpers ====================

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasPermission(PortalPermission $permission): bool
    {
        return in_array($permission->value, $this->permissions ?? []);
    }

    public function daysUntilExpiry(): int
    {
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    /**
     * تحقق من رمز نصي عبر مقارنة الـ hash.
     * يُستخدم في ClientPortalController::authenticate()
     */
    public static function findByPlaintext(string $plaintext): ?self
    {
        $hash = hash('sha256', $plaintext);

        return static::where('token', $hash)
                     ->where('expires_at', '>', now())
                     ->first();
    }
}
