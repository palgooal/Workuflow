<?php

namespace App\Modules\Categories\Actions;

use App\Models\Category;
use App\Modules\Categories\DTOs\CategoryData;

class CreateCategoryAction
{
    public function execute(CategoryData $data): Category
    {
        return Category::create([
            'name'       => $data->name,
            'type'       => $data->type,
            'color'      => $data->color,
            'icon'       => $data->icon,
            'is_default' => false,
        ]);
    }
}
