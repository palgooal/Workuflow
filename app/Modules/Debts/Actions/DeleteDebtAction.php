<?php

namespace App\Modules\Debts\Actions;

use App\Models\Debt;

class DeleteDebtAction
{
    public function execute(Debt $debt): void
    {
        $debt->delete();
    }
}
