<?php

namespace App\Modules\Recurring\Actions;

use App\Models\RecurringTransaction;

class DeleteRecurringAction
{
    public function execute(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }
}
