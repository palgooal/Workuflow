<?php

namespace App\Modules\Transactions\Actions;

use App\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionData;

class UpdateTransactionAction
{
    public function execute(Transaction $transaction, TransactionData $data): Transaction
    {
        $transaction->update([
            'type'             => $data->type,
            'amount'           => $data->amount,
            'currency'         => $data->currency,
            'description'      => $data->description,
            'transaction_date' => $data->transaction_date,
            'project_id'       => $data->project_id,
            'wallet_id'        => $data->wallet_id,
            'category_id'      => $data->category_id,
            'notes'            => $data->notes,
            'reference'        => $data->reference,
            'payee'            => $data->payee,
        ]);

        return $transaction->fresh();
    }
}
