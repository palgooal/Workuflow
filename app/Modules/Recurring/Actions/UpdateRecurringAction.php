<?php

namespace App\Modules\Recurring\Actions;

use App\Models\RecurringTransaction;
use App\Modules\Recurring\DTOs\RecurringData;

class UpdateRecurringAction
{
    public function execute(RecurringTransaction $recurring, RecurringData $data): RecurringTransaction
    {
        $recurring->update([
            'type'        => $data->type,
            'amount'      => $data->amount,
            'description' => $data->description,
            'frequency'   => $data->frequency,
            'currency'    => $data->currency,
            'category_id' => $data->category_id,
            'project_id'  => $data->project_id,
            'start_date'  => $data->start_date,
            'end_date'    => $data->end_date,
        ]);

        return $recurring->refresh();
    }
}
