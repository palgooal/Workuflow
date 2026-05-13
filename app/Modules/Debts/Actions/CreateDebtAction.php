<?php

namespace App\Modules\Debts\Actions;

use App\Models\Debt;
use App\Modules\Debts\DTOs\DebtData;

class CreateDebtAction
{
    public function execute(DebtData $data): Debt
    {
        return Debt::create([
            'type'             => $data->type,
            'party_name'       => $data->party_name,
            'amount'           => $data->amount,
            'remaining_amount' => $data->amount,
            'currency'         => $data->currency,
            'due_date'         => $data->due_date,
            'notes'            => $data->notes,
            'project_id'       => $data->project_id,
            'status'           => \App\Support\Enums\DebtStatus::Active,
        ]);
    }
}
