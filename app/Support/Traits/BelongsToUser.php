<?php

namespace App\Support\Traits;

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
    }

    /**
     * تجاهل الـ Global Scope عند الحاجة (للـ Admin أو Commands)
     */
    public function scopeForAllUsers(Builder $query): Builder
    {
        return $query->withoutGlobalScope('user');
    }
}
