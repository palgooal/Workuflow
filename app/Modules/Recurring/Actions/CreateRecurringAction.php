<?php

namespace App\Modules\Recurring\Actions;

use App\Models\RecurringTransaction;
use App\Modules\Recurring\DTOs\RecurringData;

class CreateRecurringAction
{
    public function execute(RecurringData $data): RecurringTransaction
    {
        return RecurringTransaction::create([
            'type'          => $data->type,
            'amount'        => $data->amount,
            'description'   => $data->description,
            'frequency'     => $data->frequency,
            'currency'      => $data->currency,
            'category_id'   => $data->category_id,
            'project_id'    => $data->project_id,
            'start_date'    => $data->start_date,
            'next_due_date' => $data->start_date,   // أول استحقاق = تاريخ البداية
            'end_date'      => $data->end_date,
            'is_active'     => true,
        ]);
    }
}
