<?php

namespace App\Modules\Transactions\Actions;

use App\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionData;

class CreateTransactionAction
{
    public function execute(TransactionData $data): Transaction
    {
        return Transaction::create([
            'type'             => $data->type,
            'amount'           => $data->amount,
            'currency'         => $data->currency,
            'description'      => $data->description,
            'transaction_date' => $data->transaction_date,
            'project_id'       => $data->project_id,
            'category_id'      => $data->category_id,
            'notes'            => $data->notes,
            'reference'        => $data->reference,
        ]);
    }
}
