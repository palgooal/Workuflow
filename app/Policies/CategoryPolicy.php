<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function update(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }

    public function delete(User $user, Category $category): bool
    {
        // الفئات الافتراضية لا يمكن حذفها
        if ($category->is_default) {
            return false;
        }

        return $user->id === $category->user_id;
    }
}
