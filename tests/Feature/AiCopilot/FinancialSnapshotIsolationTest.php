<?php

use App\Models\Client;
use App\Models\Debt;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransfer;
use App\Modules\AiCopilot\Services\FinancialSnapshotService;
use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\ProjectStatus;
use App\Support\Enums\TransactionType;
use Illuminate\Support\Facades\DB;

test('financial snapshot is aggregate-only, per-currency, isolated, and read-only', function () {
    $user = User::factory()->create([
        'email' => 'snapshot-owner-sensitive@example.test',
        'phone' => '0599000001',
    ]);
    $other = User::factory()->create([
        'email' => 'other-user-secret@example.test',
        'phone' => '0599999999',
    ]);

    $sarWallet = Wallet::factory()->for($user)->create([
        'name' => 'owner-wallet-sensitive-name',
        'currency' => 'SAR',
        'initial_balance' => 1000,
        'description' => 'owner-wallet-sensitive-description',
    ]);
    $usdWallet = Wallet::factory()->for($user)->create([
        'currency' => 'USD',
        'initial_balance' => 200,
    ]);
    $sarReserveWallet = Wallet::factory()->for($user)->create([
        'currency' => 'SAR',
        'initial_balance' => 100,
    ]);
    $otherWallet = Wallet::factory()->for($other)->create([
        'name' => 'other-wallet-secret-name',
        'currency' => 'SAR',
        'initial_balance' => 987654.32,
        'description' => 'other-wallet-secret-description',
    ]);
    $otherUsdWallet = Wallet::factory()->for($other)->create([
        'name' => 'other-usd-wallet-secret-name',
        'currency' => 'USD',
        'initial_balance' => 123456.78,
    ]);

    Transaction::factory()->for($user)->for($sarWallet)->create([
        'type' => TransactionType::Income,
        'currency' => 'SAR',
        'amount' => 500,
        'description' => 'owner-transaction-sensitive-description',
    ]);
    Transaction::factory()->for($user)->for($sarWallet)->create([
        'type' => TransactionType::Expense,
        'currency' => 'SAR',
        'amount' => 125,
    ]);
    Transaction::factory()->for($user)->for($usdWallet)->create([
        'type' => TransactionType::Income,
        'currency' => 'USD',
        'amount' => 75,
    ]);
    Transaction::factory()->for($other)->for($otherWallet)->create([
        'type' => TransactionType::Income,
        'currency' => 'SAR',
        'amount' => 876543.21,
        'description' => 'other-transaction-secret-description',
    ]);

    WalletTransfer::create([
        'user_id' => $user->id,
        'from_wallet_id' => $sarWallet->id,
        'to_wallet_id' => $usdWallet->id,
        'amount' => 20,
        'fee' => 2,
        'description' => 'owner-transfer-sensitive-description',
        'transferred_at' => now()->toDateString(),
    ]);
    WalletTransfer::create([
        'user_id' => $user->id,
        'from_wallet_id' => $sarWallet->id,
        'to_wallet_id' => $sarReserveWallet->id,
        'amount' => 50,
        'fee' => 3,
        'description' => 'owner-same-currency-transfer-sensitive-description',
        'transferred_at' => now()->toDateString(),
    ]);
    WalletTransfer::create([
        'user_id' => $other->id,
        'from_wallet_id' => $otherWallet->id,
        'to_wallet_id' => $otherUsdWallet->id,
        'amount' => 345678.91,
        'fee' => 4,
        'description' => 'other-cross-currency-transfer-secret-description',
        'transferred_at' => now()->toDateString(),
    ]);

    $ownerClient = Client::factory()->for($user)->create([
        'name' => 'owner-client-sensitive-name',
        'email' => 'owner-client-sensitive@example.test',
        'phone' => '0599111111',
        'address' => 'owner-client-sensitive-address',
    ]);
    $otherClient = Client::factory()->for($other)->create([
        'name' => 'other-client-secret-name',
        'email' => 'other-client-secret@example.test',
        'phone' => '0599222222',
        'address' => 'other-client-secret-address',
    ]);

    Invoice::factory()->for($user)->for($ownerClient)->create([
        'status' => InvoiceStatus::Sent,
        'currency' => 'SAR',
        'total' => 600,
        'due_date' => today()->subDay(),
        'notes' => 'owner-invoice-sensitive-notes',
        'terms' => 'owner-invoice-sensitive-terms',
    ]);
    Invoice::factory()->for($user)->for($ownerClient)->paid()->create([
        'currency' => 'USD',
        'total' => 300,
    ]);
    Invoice::factory()->for($other)->for($otherClient)->create([
        'status' => InvoiceStatus::Overdue,
        'currency' => 'SAR',
        'total' => 765432.10,
        'due_date' => today()->subMonth(),
        'notes' => 'other-invoice-secret-notes',
        'terms' => 'other-invoice-secret-terms',
    ]);

    Debt::factory()->for($user)->create([
        'type' => DebtType::Borrowed,
        'status' => DebtStatus::Active,
        'currency' => 'SAR',
        'amount' => 400,
        'remaining_amount' => 250,
        'due_date' => today()->subDay(),
        'party_name' => 'owner-debt-sensitive-party',
        'notes' => 'owner-debt-sensitive-notes',
    ]);
    Debt::factory()->for($user)->create([
        'type' => DebtType::Lent,
        'status' => DebtStatus::Active,
        'currency' => 'USD',
        'amount' => 90,
        'remaining_amount' => 80,
    ]);
    Debt::factory()->for($other)->create([
        'type' => DebtType::Borrowed,
        'status' => DebtStatus::Active,
        'currency' => 'SAR',
        'amount' => 654321.09,
        'remaining_amount' => 654321.09,
        'party_name' => 'other-debt-secret-party',
        'notes' => 'other-debt-secret-notes',
    ]);

    Project::factory()->for($user)->create([
        'name' => 'owner-project-sensitive-name',
        'description' => 'owner-project-sensitive-description',
        'currency' => 'SAR',
        'status' => ProjectStatus::Active,
        'contract_value' => 2000,
        'expense_budget' => 800,
    ]);
    Project::factory()->for($other)->create([
        'name' => 'other-project-secret-name',
        'description' => 'other-project-secret-description',
        'currency' => 'SAR',
        'status' => ProjectStatus::Active,
        'contract_value' => 543210.98,
        'expense_budget' => 432109.87,
    ]);

    $financialTables = ['transactions', 'wallets', 'wallet_transfers', 'projects', 'invoices', 'debts'];
    $before = financialTableState($financialTables);

    // Deliberately authenticate as the other user. The service must use only
    // the explicitly supplied User instance, never ambient authentication.
    $this->actingAs($other);
    $snapshot = app(FinancialSnapshotService::class)->build($user);
    $data = $snapshot->toArray();
    $json = json_encode($snapshot, JSON_THROW_ON_ERROR);

    expect($data)->toHaveKeys(['schema_version', 'data_quality', 'currencies'])
        ->and($data['schema_version'])->toBe('1.0')
        ->and($data['data_quality'])->toBe([
            'excluded_cross_currency_transfers' => 1,
        ])
        ->and(array_keys($data['currencies']))->toBe(['SAR', 'USD'])
        ->and($data['currencies']['SAR']['transactions'])->toBe([
            'income' => 500.0,
            'expenses' => 125.0,
            'net' => 375.0,
            'count' => 2,
        ])
        ->and($data['currencies']['USD']['transactions'])->toBe([
            'income' => 75.0,
            'expenses' => 0.0,
            'net' => 75.0,
            'count' => 1,
        ])
        ->and($data['currencies']['SAR']['wallets'])->toBe([
            // The cross-currency transfer has no stored destination amount or
            // exchange rate, so it is excluded. The SAR-to-SAR amount cancels
            // across both wallets, while its fee reduces their total balance.
            'balance' => 1472.0,
            'count' => 2,
        ])
        ->and($data['currencies']['USD']['wallets'])->toBe([
            'balance' => 275.0,
            'count' => 1,
        ])
        ->and($data['currencies']['SAR']['invoices']['outstanding_amount'])->toBe(600.0)
        ->and($data['currencies']['SAR']['invoices']['overdue_amount'])->toBe(600.0)
        ->and($data['currencies']['USD']['invoices']['outstanding_amount'])->toBe(0.0)
        ->and($data['currencies']['SAR']['debts']['borrowed_remaining'])->toBe(250.0)
        ->and($data['currencies']['USD']['debts']['lent_remaining'])->toBe(80.0)
        ->and($data['currencies']['SAR']['projects']['contract_value'])->toBe(2000.0);

    $forbiddenKeys = [
        'user_id', 'id', 'notes', 'description', 'terms', 'address', 'email',
        'phone', 'client', 'client_id', 'party_name', 'name', 'secret',
    ];
    assertSnapshotHasNoForbiddenKeys($data, $forbiddenKeys);

    foreach ([
        '987654.32', '123456.78', '345678.91', '876543.21', '765432.1', '654321.09',
        '543210.98', '432109.87', 'other-wallet-secret-name', 'other-usd-wallet-secret-name',
        'other-cross-currency-transfer-secret-description', 'other-transaction-secret-description',
        'other-client-secret-name', 'other-client-secret@example.test',
        'other-invoice-secret-notes', 'other-invoice-secret-terms',
        'other-debt-secret-party', 'other-debt-secret-notes',
        'other-project-secret-name', 'other-project-secret-description',
        'owner-transaction-sensitive-description', 'owner-invoice-sensitive-notes',
        'owner-invoice-sensitive-terms', 'owner-client-sensitive@example.test',
    ] as $sentinel) {
        expect($json)->not->toContain($sentinel);
    }

    expect(financialTableState($financialTables))->toBe($before);
});

test('financial snapshot keeps zero excluded transfer metadata when there are no transfers', function () {
    $user = User::factory()->create();

    $data = app(FinancialSnapshotService::class)->build($user)->toArray();

    expect($data['data_quality'])->toBe([
        'excluded_cross_currency_transfers' => 0,
    ]);
});

function financialTableState(array $tables): array
{
    $state = [];

    foreach ($tables as $table) {
        $state[$table] = DB::table($table)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    return $state;
}

function assertSnapshotHasNoForbiddenKeys(array $value, array $forbiddenKeys): void
{
    foreach ($value as $key => $item) {
        if (is_string($key)) {
            expect($forbiddenKeys)->not->toContain($key);
        }

        if (is_array($item)) {
            assertSnapshotHasNoForbiddenKeys($item, $forbiddenKeys);
        }
    }
}
