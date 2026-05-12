<?php

namespace App\Modules\Transactions\Actions;

use App\Models\Transaction;

class DeleteTransactionAction
{
    public function execute(Transaction $transaction): void
    {
        $transaction->delete();
    }
}
