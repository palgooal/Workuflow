<?php

namespace App\Modules\Debts\Actions;

use App\Models\Debt;
use App\Support\Enums\DebtStatus;

class MarkDebtAsPaidAction
{
    public function execute(Debt $debt): Debt
    {
        $debt->update([
            'remaining_amount' => 0,
            'status'           => DebtStatus::Paid,
        ]);

        return $debt->fresh();
    }
}
