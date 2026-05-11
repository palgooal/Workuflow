<?php

namespace App\Support\Enums;

enum TransactionType: string
{
    case Income   = 'income';
    case Expense  = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::Income   => 'دخل',
            self::Expense  => 'مصروف',
            self::Transfer => 'تحويل',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Income   => 'green',
            self::Expense  => 'red',
            self::Transfer => 'blue',
        };
    }

    public function isIncome(): bool
    {
        return $this === self::Income;
    }

    public function isExpense(): bool
    {
        return $this === self::Expense;
    }
}
