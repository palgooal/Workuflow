<?php

namespace App\Modules\AiCopilot\Services;

use App\Modules\AiCopilot\DTOs\FinancialSnapshot;

final class FinancialRiskAnalyzer
{
    /** Expenses at 80% of income materially constrain the remaining margin. */
    public const HIGH_EXPENSE_RATIO_WARNING_THRESHOLD = 0.80;

    /** Expenses equal to or above income are a critical expense-ratio condition. */
    public const HIGH_EXPENSE_RATIO_CRITICAL_THRESHOLD = 1.00;

    /** Borrowed debt 25% above positive wallet liquidity indicates pressure. */
    public const DEBT_LIQUIDITY_PRESSURE_WARNING_THRESHOLD = 1.25;

    /** Borrowed debt at least twice positive wallet liquidity is critical pressure. */
    public const DEBT_LIQUIDITY_PRESSURE_CRITICAL_THRESHOLD = 2.00;

    public function analyze(FinancialSnapshot $snapshot): array
    {
        $currencies = $snapshot->currencies;
        ksort($currencies);

        $currencyAnalysis = [];

        foreach ($currencies as $currency => $aggregates) {
            $currencyAnalysis[(string) $currency] = [
                'signals' => $this->analyzeCurrency($aggregates),
            ];
        }

        return [
            'state' => $this->hasMeaningfulActivity($currencies) ? 'analyzed' : 'insufficient_data',
            'data_quality_warnings' => $this->dataQualityWarnings($snapshot->dataQuality),
            'currencies' => $currencyAnalysis,
        ];
    }

    private function analyzeCurrency(array $aggregates): array
    {
        $signals = [];

        $income = $this->amount($aggregates, 'transactions', 'income');
        $expenses = $this->amount($aggregates, 'transactions', 'expenses');
        $net = $this->amount($aggregates, 'transactions', 'net');
        $walletBalance = $this->amount($aggregates, 'wallets', 'balance');
        $overdueInvoiceCount = $this->count($aggregates, 'invoices', 'overdue_count');
        $overdueInvoiceAmount = $this->amount($aggregates, 'invoices', 'overdue_amount');
        $overdueDebtCount = $this->count($aggregates, 'debts', 'overdue_count');
        $overdueDebtRemaining = $this->amount($aggregates, 'debts', 'overdue_remaining');
        $borrowedRemaining = $this->amount($aggregates, 'debts', 'borrowed_remaining');

        // This order is the public deterministic signal order.
        if ($net < 0) {
            $signals[] = $this->signal('negative_cash_flow', 'critical', [
                'income' => $income,
                'expenses' => $expenses,
                'net' => $net,
            ]);
        }

        // Income must be positive to make the ratio defined. With zero income,
        // expenses are represented by negative_cash_flow instead.
        if ($income > 0) {
            $expenseRatio = $expenses / $income;

            if ($expenseRatio >= self::HIGH_EXPENSE_RATIO_WARNING_THRESHOLD) {
                $severity = $expenseRatio >= self::HIGH_EXPENSE_RATIO_CRITICAL_THRESHOLD
                    ? 'critical'
                    : 'warning';

                $signals[] = $this->signal('high_expense_ratio', $severity, [
                    'income' => $income,
                    'expenses' => $expenses,
                ]);
            }
        }

        if ($walletBalance < 0) {
            $signals[] = $this->signal('negative_wallet_balance', 'critical', [
                'wallet_balance' => $walletBalance,
            ]);
        }

        if ($overdueInvoiceCount > 0 || $overdueInvoiceAmount > 0) {
            $signals[] = $this->signal('overdue_invoices', 'warning', [
                'overdue_count' => $overdueInvoiceCount,
                'overdue_amount' => $overdueInvoiceAmount,
            ]);
        }

        if ($overdueDebtCount > 0 || $overdueDebtRemaining > 0) {
            $signals[] = $this->signal('overdue_debts', 'warning', [
                'overdue_count' => $overdueDebtCount,
                'overdue_remaining' => $overdueDebtRemaining,
            ]);
        }

        $positiveWalletBalance = max(0.0, $walletBalance);

        if ($borrowedRemaining > 0 && $positiveWalletBalance === 0.0) {
            $signals[] = $this->signal('debt_liquidity_pressure', 'critical', [
                'borrowed_remaining' => $borrowedRemaining,
                'positive_wallet_balance' => $positiveWalletBalance,
            ]);
        } elseif ($positiveWalletBalance > 0) {
            $debtLiquidityRatio = $borrowedRemaining / $positiveWalletBalance;

            if ($debtLiquidityRatio >= self::DEBT_LIQUIDITY_PRESSURE_WARNING_THRESHOLD) {
                $severity = $debtLiquidityRatio >= self::DEBT_LIQUIDITY_PRESSURE_CRITICAL_THRESHOLD
                    ? 'critical'
                    : 'warning';

                $signals[] = $this->signal('debt_liquidity_pressure', $severity, [
                    'borrowed_remaining' => $borrowedRemaining,
                    'positive_wallet_balance' => $positiveWalletBalance,
                ]);
            }
        }

        return $signals;
    }

    private function dataQualityWarnings(array $dataQuality): array
    {
        $excludedTransfers = max(0, (int) ($dataQuality['excluded_cross_currency_transfers'] ?? 0));

        if ($excludedTransfers === 0) {
            return [];
        }

        return [
            $this->signal('excluded_cross_currency_transfers', 'warning', [
                'count' => $excludedTransfers,
            ]),
        ];
    }

    private function hasMeaningfulActivity(array $currencies): bool
    {
        foreach ($currencies as $aggregates) {
            if (
                $this->count($aggregates, 'transactions', 'count') > 0
                || $this->amount($aggregates, 'transactions', 'income') != 0.0
                || $this->amount($aggregates, 'transactions', 'expenses') != 0.0
                || $this->amount($aggregates, 'wallets', 'balance') != 0.0
                || $this->count($aggregates, 'invoices', 'count') > 0
                || $this->count($aggregates, 'debts', 'borrowed_count') > 0
                || $this->count($aggregates, 'debts', 'lent_count') > 0
                || $this->amount($aggregates, 'debts', 'borrowed_remaining') != 0.0
                || $this->amount($aggregates, 'debts', 'lent_remaining') != 0.0
                || $this->count($aggregates, 'projects', 'count') > 0
            ) {
                return true;
            }
        }

        return false;
    }

    private function signal(string $code, string $severity, array $evidence): array
    {
        return [
            'code' => $code,
            'severity' => $severity,
            'evidence' => $evidence,
        ];
    }

    private function amount(array $aggregates, string $section, string $key): float
    {
        return (float) ($aggregates[$section][$key] ?? 0);
    }

    private function count(array $aggregates, string $section, string $key): int
    {
        return (int) ($aggregates[$section][$key] ?? 0);
    }
}
