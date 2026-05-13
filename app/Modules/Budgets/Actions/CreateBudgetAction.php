<?php

namespace App\Modules\Budgets\Actions;

use App\Models\Budget;
use App\Modules\Budgets\DTOs\BudgetData;

class CreateBudgetAction
{
    public function execute(BudgetData $data): Budget
    {
        return Budget::create([
            'amount'      => $data->amount,
            'period'      => $data->period,
            'year'        => $data->year,
            'month'       => $data->month,
            'category_id' => $data->category_id,
            'project_id'  => $data->project_id,
        ]);
    }
}
