<?php

namespace App\Modules\Budgets\Actions;

use App\Models\Budget;

class DeleteBudgetAction
{
    public function execute(Budget $budget): void
    {
        $budget->delete();
    }
}
