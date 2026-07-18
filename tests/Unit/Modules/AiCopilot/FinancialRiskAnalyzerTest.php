<?php

use App\Modules\AiCopilot\DTOs\FinancialSnapshot;
use App\Modules\AiCopilot\Services\FinancialRiskAnalyzer;

test('empty snapshot returns a deterministic insufficient data state', function () {
    $result = riskAnalyzer()->analyze(riskSnapshot());

    expect($result)->toBe([
        'state' => 'insufficient_data',
        'data_quality_warnings' => [],
        'currencies' => [],
    ]);
});

test('healthy aggregates produce no false risk warnings', function () {
    $snapshot = riskSnapshot([
        'SAR' => riskCurrency(
            income: 1000,
            expenses: 500,
            walletBalance: 800,
            invoiceCount: 1,
            outstandingInvoiceAmount: 400,
            projectCount: 1,
            projectContractValue: 5000,
            projectExpenseBudget: 9000,
        ),
    ]);

    $result = riskAnalyzer()->analyze($snapshot);

    expect($result['state'])->toBe('analyzed')
        ->and($result['currencies']['SAR']['signals'])->toBe([]);
});

test('negative cash flow uses aggregate transaction evidence', function () {
    $signals = riskSignals(riskCurrency(income: 0, expenses: 150));

    expect($signals)->toBe([
        [
            'code' => 'negative_cash_flow',
            'severity' => 'critical',
            'evidence' => [
                'income' => 0.0,
                'expenses' => 150.0,
                'net' => -150.0,
            ],
        ],
    ]);
});

test('expense ratio thresholds are inclusive and division by zero is guarded', function () {
    $below = riskSignals(riskCurrency(income: 1000, expenses: 799));
    $warning = riskSignals(riskCurrency(income: 1000, expenses: 800));
    $critical = riskSignals(riskCurrency(income: 1000, expenses: 1000));
    $zeroIncome = riskSignals(riskCurrency(income: 0, expenses: 1));

    expect($below)->toBe([])
        ->and($warning)->toHaveCount(1)
        ->and($warning[0]['code'])->toBe('high_expense_ratio')
        ->and($warning[0]['severity'])->toBe('warning')
        ->and($critical)->toHaveCount(1)
        ->and($critical[0]['code'])->toBe('high_expense_ratio')
        ->and($critical[0]['severity'])->toBe('critical')
        ->and(array_column($zeroIncome, 'code'))->toBe(['negative_cash_flow']);
});

test('negative aggregate wallet balance is critical', function () {
    expect(riskSignals(riskCurrency(walletBalance: -10)))->toBe([
        [
            'code' => 'negative_wallet_balance',
            'severity' => 'critical',
            'evidence' => ['wallet_balance' => -10.0],
        ],
    ]);
});

test('overdue invoices use only overdue aggregates', function () {
    $signals = riskSignals(riskCurrency(
        invoiceCount: 2,
        overdueInvoiceCount: 1,
        overdueInvoiceAmount: 250,
    ));

    expect($signals)->toContain([
        'code' => 'overdue_invoices',
        'severity' => 'warning',
        'evidence' => ['overdue_count' => 1, 'overdue_amount' => 250.0],
    ]);
});

test('overdue debts use only overdue aggregates', function () {
    $signals = riskSignals(riskCurrency(
        borrowedCount: 1,
        overdueDebtCount: 1,
        overdueDebtRemaining: 75,
    ));

    expect($signals)->toContain([
        'code' => 'overdue_debts',
        'severity' => 'warning',
        'evidence' => ['overdue_count' => 1, 'overdue_remaining' => 75.0],
    ]);
});

test('debt liquidity pressure respects warning and critical thresholds', function () {
    $below = riskSignals(riskCurrency(walletBalance: 100, borrowedCount: 1, borrowedRemaining: 124));
    $warning = riskSignals(riskCurrency(walletBalance: 100, borrowedCount: 1, borrowedRemaining: 125));
    $critical = riskSignals(riskCurrency(walletBalance: 100, borrowedCount: 1, borrowedRemaining: 200));
    $zeroLiquidity = riskSignals(riskCurrency(walletBalance: 0, borrowedCount: 1, borrowedRemaining: 1));

    expect($below)->toBe([])
        ->and($warning[0]['code'])->toBe('debt_liquidity_pressure')
        ->and($warning[0]['severity'])->toBe('warning')
        ->and($critical[0]['severity'])->toBe('critical')
        ->and($zeroLiquidity[0]['severity'])->toBe('critical');
});

test('currencies are analyzed independently without cross currency liquidity contamination', function () {
    $snapshot = riskSnapshot([
        'USD' => riskCurrency(income: 1000, expenses: 100, walletBalance: 5000),
        'SAR' => riskCurrency(walletBalance: 100, borrowedCount: 1, borrowedRemaining: 200),
    ]);

    $result = riskAnalyzer()->analyze($snapshot);

    expect(array_keys($result['currencies']))->toBe(['SAR', 'USD'])
        ->and(array_column($result['currencies']['SAR']['signals'], 'code'))
        ->toBe(['debt_liquidity_pressure'])
        ->and($result['currencies']['USD']['signals'])->toBe([]);
});

test('excluded cross currency transfers are reported only as aggregate data quality', function () {
    $snapshot = riskSnapshot(
        ['SAR' => riskCurrency(walletBalance: 100)],
        excludedCrossCurrencyTransfers: 2,
    );

    $result = riskAnalyzer()->analyze($snapshot);

    expect($result['data_quality_warnings'])->toBe([
        [
            'code' => 'excluded_cross_currency_transfers',
            'severity' => 'warning',
            'evidence' => ['count' => 2],
        ],
    ])->and($result['currencies']['SAR']['signals'])->toBe([]);
});

test('signal ordering and repeated output are stable', function () {
    $snapshot = riskSnapshot([
        'SAR' => riskCurrency(
            income: 100,
            expenses: 150,
            walletBalance: -10,
            invoiceCount: 1,
            overdueInvoiceCount: 1,
            overdueInvoiceAmount: 20,
            borrowedCount: 1,
            borrowedRemaining: 50,
            overdueDebtCount: 1,
            overdueDebtRemaining: 25,
        ),
    ], 1);
    $analyzer = riskAnalyzer();

    $first = $analyzer->analyze($snapshot);
    $second = $analyzer->analyze($snapshot);

    expect(array_column($first['currencies']['SAR']['signals'], 'code'))->toBe([
        'negative_cash_flow',
        'high_expense_ratio',
        'negative_wallet_balance',
        'overdue_invoices',
        'overdue_debts',
        'debt_liquidity_pressure',
    ])->and($second)->toBe($first)
        ->and(json_encode($second, JSON_THROW_ON_ERROR))
        ->toBe(json_encode($first, JSON_THROW_ON_ERROR));
});

test('analyzer has no database auth or model dependency', function () {
    $reflection = new ReflectionClass(FinancialRiskAnalyzer::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->not->toContain('App\\Models')
        ->not->toContain('DB::')
        ->not->toContain('Auth::')
        ->not->toContain('auth(');

    expect(riskAnalyzer()->analyze(riskSnapshot()))->toBeArray();
});

function riskAnalyzer(): FinancialRiskAnalyzer
{
    return new FinancialRiskAnalyzer;
}

function riskSnapshot(array $currencies = [], int $excludedCrossCurrencyTransfers = 0): FinancialSnapshot
{
    return new FinancialSnapshot(
        currencies: $currencies,
        dataQuality: [
            'excluded_cross_currency_transfers' => $excludedCrossCurrencyTransfers,
        ],
    );
}

function riskSignals(array $currency): array
{
    return riskAnalyzer()->analyze(riskSnapshot(['SAR' => $currency]))['currencies']['SAR']['signals'];
}

function riskCurrency(
    float $income = 0,
    float $expenses = 0,
    float $walletBalance = 0,
    int $invoiceCount = 0,
    float $outstandingInvoiceAmount = 0,
    int $overdueInvoiceCount = 0,
    float $overdueInvoiceAmount = 0,
    int $borrowedCount = 0,
    float $borrowedRemaining = 0,
    int $overdueDebtCount = 0,
    float $overdueDebtRemaining = 0,
    int $projectCount = 0,
    float $projectContractValue = 0,
    float $projectExpenseBudget = 0,
): array {
    return [
        'transactions' => [
            'income' => $income,
            'expenses' => $expenses,
            'net' => $income - $expenses,
            'count' => ($income != 0.0 || $expenses != 0.0) ? 1 : 0,
        ],
        'wallets' => ['balance' => $walletBalance, 'count' => $walletBalance != 0.0 ? 1 : 0],
        'invoices' => [
            'count' => $invoiceCount,
            'outstanding_count' => $outstandingInvoiceAmount > 0 ? 1 : 0,
            'outstanding_amount' => $outstandingInvoiceAmount,
            'overdue_count' => $overdueInvoiceCount,
            'overdue_amount' => $overdueInvoiceAmount,
        ],
        'debts' => [
            'borrowed_count' => $borrowedCount,
            'borrowed_remaining' => $borrowedRemaining,
            'lent_count' => 0,
            'lent_remaining' => 0.0,
            'overdue_count' => $overdueDebtCount,
            'overdue_remaining' => $overdueDebtRemaining,
        ],
        'projects' => [
            'count' => $projectCount,
            'active_count' => $projectCount,
            'contract_value' => $projectContractValue,
            'expense_budget' => $projectExpenseBudget,
        ],
    ];
}
