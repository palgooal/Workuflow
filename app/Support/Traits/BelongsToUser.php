<?php

namespace App\Support\Traits;

use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToUser Trait
 *
 * يُضاف لكل Model يملكه المستخدم لضمان العزل التلقائي للبيانات.
 * لا حاجة لكتابة where('user_id', auth()->id()) في كل استعلام.
 */
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        // عزل تلقائي: كل استعلام يُقيَّد ببيانات المستخدم الحالي
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(
                    (new static)->getTable() . '.user_id',
                    auth()->id()
                );
            }
        });

        // ربط user_id تلقائياً عند الإنشاء
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });

        // إبطال Cache لوحة التحكم (dashboard_v2) عند أي تغيير على بيانات مملوكة للمستخدم —
        // كان لوحة التحكم تبقى بأرقام قديمة حتى 30 دقيقة (مدة الـ Cache) بعد أي تعديل، لأن
        // لا شيء كان يستدعي DashboardService::clearCache(). راجع docs/KNOWN-BUGS-AND-GAPS.md.
        static::saved(function ($model) {
            if (! empty($model->user_id)) {
                app(DashboardService::class)->clearCache($model->user_id);
            }
        });

        static::deleted(function ($model) {
            if (! empty($model->user_id)) {
                app(DashboardService::class)->clearCache($model->user_id);
            }
        });
    }

    /**
     * تجاهل الـ Global Scope عند الحاجة (للـ Admin أو Commands)
     */
    public function scopeForAllUsers(Builder $query): Builder
    {
        return $query->withoutGlobalScope('user');
    }
}
