<?php

namespace App\Modules\Recurring\Actions;

use App\Models\RecurringTransaction;
use App\Models\Transaction;

class ProcessRecurringAction
{
    /**
     * ينشئ معاملة فعلية من الالتزام المتكرر ثم يحرّك تاريخ الاستحقاق للأمام.
     */
    public function execute(RecurringTransaction $recurring): Transaction
    {
        // إنشاء المعاملة الفعلية
        $transaction = Transaction::create([
            'user_id'          => $recurring->user_id,
            'project_id'       => $recurring->project_id,
            'category_id'      => $recurring->category_id,
            'type'             => $recurring->type,
            'amount'           => $recurring->amount,
            'currency'         => $recurring->currency,
            'description'      => $recurring->description,
            'transaction_date' => today(),
        ]);

        // تحديث next_due_date
        $recurring->advanceToNextDue();

        // إيقاف التكرار إذا تجاوز end_date
        if ($recurring->end_date && $recurring->next_due_date->gt($recurring->end_date)) {
            $recurring->update(['is_active' => false]);
        }

        return $transaction;
    }
}
