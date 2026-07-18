<?php

namespace App\Modules\AiCopilot\Services;

use App\Models\Debt;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransfer;
use App\Modules\AiCopilot\DTOs\FinancialSnapshot;
use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\ProjectStatus;
use App\Support\Enums\TransactionType;
use Illuminate\Support\Collection;

final class FinancialSnapshotService
{
    public function build(User $user): FinancialSnapshot
    {
        $userId = (int) $user->getKey();
        $currencies = [];

        $this->addTransactions($currencies, $userId);
        $excludedCrossCurrencyTransfers = $this->addWallets($currencies, $userId);
        $this->addInvoices($currencies, $userId);
        $this->addDebts($currencies, $userId);
        $this->addProjects($currencies, $userId);

        ksort($currencies);

        return new FinancialSnapshot(
            currencies: $currencies,
            dataQuality: [
                'excluded_cross_currency_transfers' => $excludedCrossCurrencyTransfers,
            ],
        );
    }

    private function addTransactions(array &$currencies, int $userId): void
    {
        $rows = Transaction::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->selectRaw('currency, type, COUNT(*) as aggregate_count, SUM(amount) as aggregate_amount')
            ->groupBy('currency', 'type')
            ->get();

        foreach ($rows as $row) {
            $currency = (string) $row->currency;
            $bucket = &$this->currencyBucket($currencies, $currency);
            $type = $row->type instanceof TransactionType ? $row->type->value : (string) $row->type;
            $amount = $this->money($row->aggregate_amount);

            if ($type === TransactionType::Income->value) {
                $bucket['transactions']['income'] += $amount;
            } elseif ($type === TransactionType::Expense->value) {
                $bucket['transactions']['expenses'] += $amount;
            }

            $bucket['transactions']['count'] += (int) $row->aggregate_count;
            $bucket['transactions']['net'] = $this->money(
                $bucket['transactions']['income'] - $bucket['transactions']['expenses']
            );
            unset($bucket);
        }
    }

    private function addWallets(array &$currencies, int $userId): int
    {
        $wallets = Wallet::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->select(['id', 'currency', 'initial_balance'])
            ->get();

        if ($wallets->isEmpty()) {
            return 0;
        }

        $walletIds = $wallets->pluck('id')->all();

        $transactions = Transaction::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->whereIn('wallet_id', $walletIds)
            ->selectRaw('wallet_id, currency, type, SUM(amount) as aggregate_amount')
            ->groupBy('wallet_id', 'currency', 'type')
            ->get()
            ->groupBy('wallet_id');

        $walletCurrencies = $wallets->mapWithKeys(
            fn ($wallet) => [(string) $wallet->id => (string) $wallet->currency]
        );

        $transfers = WalletTransfer::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->whereIn('from_wallet_id', $walletIds)
            ->whereIn('to_wallet_id', $walletIds)
            ->selectRaw(
                'from_wallet_id, to_wallet_id, COUNT(*) as aggregate_count,
                SUM(amount) as aggregate_amount, SUM(fee) as aggregate_fee'
            )
            ->groupBy('from_wallet_id', 'to_wallet_id')
            ->get();

        $transfersOut = [];
        $transfersIn = [];
        $excludedCrossCurrencyTransfers = 0;

        foreach ($transfers as $transfer) {
            $fromWalletId = (string) $transfer->from_wallet_id;
            $toWalletId = (string) $transfer->to_wallet_id;

            // Cross-currency transfers store only one nominal amount and no
            // authoritative destination amount or rate, so they cannot be
            // assigned safely to either currency aggregate.
            if ($walletCurrencies->get($fromWalletId) !== $walletCurrencies->get($toWalletId)) {
                $excludedCrossCurrencyTransfers += (int) $transfer->aggregate_count;

                continue;
            }

            $transfersOut[$fromWalletId]['amount'] = ($transfersOut[$fromWalletId]['amount'] ?? 0)
                + (float) $transfer->aggregate_amount;
            $transfersOut[$fromWalletId]['fee'] = ($transfersOut[$fromWalletId]['fee'] ?? 0)
                + (float) $transfer->aggregate_fee;
            $transfersIn[$toWalletId] = ($transfersIn[$toWalletId] ?? 0)
                + (float) $transfer->aggregate_amount;
        }

        foreach ($wallets as $wallet) {
            $currency = (string) $wallet->currency;
            $walletTransactions = $transactions->get($wallet->id, collect())
                ->filter(fn ($row) => (string) $row->currency === $currency);

            $income = $this->transactionAmount($walletTransactions, TransactionType::Income);
            $expenses = $this->transactionAmount($walletTransactions, TransactionType::Expense);
            $walletId = (string) $wallet->id;

            $balance = $this->money(
                (float) $wallet->initial_balance
                + $income
                - $expenses
                + ($transfersIn[$walletId] ?? 0)
                - ($transfersOut[$walletId]['amount'] ?? 0)
                - ($transfersOut[$walletId]['fee'] ?? 0)
            );

            $bucket = &$this->currencyBucket($currencies, $currency);
            $bucket['wallets']['balance'] = $this->money($bucket['wallets']['balance'] + $balance);
            $bucket['wallets']['count']++;
            unset($bucket);
        }

        return $excludedCrossCurrencyTransfers;
    }

    private function addInvoices(array &$currencies, int $userId): void
    {
        $paid = InvoiceStatus::Paid->value;
        $cancelled = InvoiceStatus::Cancelled->value;

        $rows = Invoice::query()
            ->where('user_id', $userId)
            ->selectRaw(
                'currency,
                COUNT(*) as aggregate_count,
                SUM(CASE WHEN status NOT IN (?, ?) THEN 1 ELSE 0 END) as outstanding_count,
                SUM(CASE WHEN status NOT IN (?, ?) THEN total ELSE 0 END) as outstanding_amount,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status NOT IN (?, ?) THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status NOT IN (?, ?) THEN total ELSE 0 END) as overdue_amount',
                [$paid, $cancelled, $paid, $cancelled, today()->toDateString(), $paid, $cancelled, today()->toDateString(), $paid, $cancelled]
            )
            ->groupBy('currency')
            ->get();

        foreach ($rows as $row) {
            $bucket = &$this->currencyBucket($currencies, (string) $row->currency);
            $bucket['invoices'] = [
                'count' => (int) $row->aggregate_count,
                'outstanding_count' => (int) $row->outstanding_count,
                'outstanding_amount' => $this->money($row->outstanding_amount),
                'overdue_count' => (int) $row->overdue_count,
                'overdue_amount' => $this->money($row->overdue_amount),
            ];
            unset($bucket);
        }
    }

    private function addDebts(array &$currencies, int $userId): void
    {
        $paid = DebtStatus::Paid->value;
        $borrowed = DebtType::Borrowed->value;
        $lent = DebtType::Lent->value;

        $rows = Debt::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->selectRaw(
                'currency,
                SUM(CASE WHEN type = ? AND status != ? THEN 1 ELSE 0 END) as borrowed_count,
                SUM(CASE WHEN type = ? AND status != ? THEN remaining_amount ELSE 0 END) as borrowed_remaining,
                SUM(CASE WHEN type = ? AND status != ? THEN 1 ELSE 0 END) as lent_count,
                SUM(CASE WHEN type = ? AND status != ? THEN remaining_amount ELSE 0 END) as lent_remaining,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status != ? THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status != ? THEN remaining_amount ELSE 0 END) as overdue_remaining',
                [$borrowed, $paid, $borrowed, $paid, $lent, $paid, $lent, $paid, today()->toDateString(), $paid, today()->toDateString(), $paid]
            )
            ->groupBy('currency')
            ->get();

        foreach ($rows as $row) {
            $bucket = &$this->currencyBucket($currencies, (string) $row->currency);
            $bucket['debts'] = [
                'borrowed_count' => (int) $row->borrowed_count,
                'borrowed_remaining' => $this->money($row->borrowed_remaining),
                'lent_count' => (int) $row->lent_count,
                'lent_remaining' => $this->money($row->lent_remaining),
                'overdue_count' => (int) $row->overdue_count,
                'overdue_remaining' => $this->money($row->overdue_remaining),
            ];
            unset($bucket);
        }
    }

    private function addProjects(array &$currencies, int $userId): void
    {
        $rows = Project::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->selectRaw(
                'currency,
                COUNT(*) as aggregate_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_count,
                COALESCE(SUM(contract_value), 0) as contract_value,
                COALESCE(SUM(expense_budget), 0) as expense_budget',
                [ProjectStatus::Active->value]
            )
            ->groupBy('currency')
            ->get();

        foreach ($rows as $row) {
            $bucket = &$this->currencyBucket($currencies, (string) $row->currency);
            $bucket['projects'] = [
                'count' => (int) $row->aggregate_count,
                'active_count' => (int) $row->active_count,
                'contract_value' => $this->money($row->contract_value),
                'expense_budget' => $this->money($row->expense_budget),
            ];
            unset($bucket);
        }
    }

    private function &currencyBucket(array &$currencies, string $currency): array
    {
        if (! isset($currencies[$currency])) {
            $currencies[$currency] = [
                'transactions' => [
                    'income' => 0.0,
                    'expenses' => 0.0,
                    'net' => 0.0,
                    'count' => 0,
                ],
                'wallets' => [
                    'balance' => 0.0,
                    'count' => 0,
                ],
                'invoices' => [
                    'count' => 0,
                    'outstanding_count' => 0,
                    'outstanding_amount' => 0.0,
                    'overdue_count' => 0,
                    'overdue_amount' => 0.0,
                ],
                'debts' => [
                    'borrowed_count' => 0,
                    'borrowed_remaining' => 0.0,
                    'lent_count' => 0,
                    'lent_remaining' => 0.0,
                    'overdue_count' => 0,
                    'overdue_remaining' => 0.0,
                ],
                'projects' => [
                    'count' => 0,
                    'active_count' => 0,
                    'contract_value' => 0.0,
                    'expense_budget' => 0.0,
                ],
            ];
        }

        return $currencies[$currency];
    }

    private function transactionAmount(Collection $transactions, TransactionType $type): float
    {
        return $this->money($transactions
            ->filter(function ($row) use ($type) {
                $rowType = $row->type instanceof TransactionType ? $row->type->value : (string) $row->type;

                return $rowType === $type->value;
            })
            ->sum('aggregate_amount'));
    }

    private function money(float|int|string|null $amount): float
    {
        return round((float) ($amount ?? 0), 3);
    }
}
