<?php

namespace App\Modules\Budgets\Actions;

use App\Models\Budget;
use App\Modules\Budgets\DTOs\BudgetData;

class UpdateBudgetAction
{
    public function execute(Budget $budget, BudgetData $data): Budget
    {
        $budget->update([
            'amount'      => $data->amount,
            'period'      => $data->period,
            'year'        => $data->year,
            'month'       => $data->month,
            'category_id' => $data->category_id,
            'project_id'  => $data->project_id,
        ]);

        return $budget->fresh();
    }
}
