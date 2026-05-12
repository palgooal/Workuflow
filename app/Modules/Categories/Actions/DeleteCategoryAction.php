<?php

namespace App\Modules\Categories\Actions;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        // لا يمكن حذف فئة لها معاملات مرتبطة
        if ($category->transactions()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'لا يمكن حذف هذه الفئة لأنها مرتبطة بمعاملات موجودة.',
            ]);
        }

        $category->delete();
    }
}
