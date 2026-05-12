<?php

namespace App\Modules\Categories\Actions;

use App\Models\Category;
use App\Modules\Categories\DTOs\CategoryData;

class UpdateCategoryAction
{
    public function execute(Category $category, CategoryData $data): Category
    {
        $category->update([
            'name'  => $data->name,
            'type'  => $data->type,
            'color' => $data->color,
            'icon'  => $data->icon,
        ]);

        return $category->fresh();
    }
}
