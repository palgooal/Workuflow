<?php

namespace App\Modules\Debts\Actions;

use App\Models\Debt;
use App\Support\Enums\DebtStatus;
use Illuminate\Validation\ValidationException;

class RecordPartialPaymentAction
{
    public function execute(Debt $debt, float $amount): Debt
    {
        if ($debt->isPaid()) {
            throw ValidationException::withMessages([
                'amount' => 'هذا الدين مسدد بالكامل.',
            ]);
        }

        if ($amount > $debt->remaining_amount) {
            throw ValidationException::withMessages([
                'amount' => "المبلغ المدخل ({$amount}) أكبر من المتبقي ({$debt->remaining_amount}).",
            ]);
        }

        $newRemaining = round($debt->remaining_amount - $amount, 2);

        $debt->update([
            'remaining_amount' => $newRemaining,
            'status' => $newRemaining <= 0
                ? DebtStatus::Paid
                : DebtStatus::PartiallyPaid,
        ]);

        return $debt->fresh();
    }
}
