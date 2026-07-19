<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Modules\AiCopilot\Services\OpenAiCopilotService;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\TransactionType;
use App\Support\Enums\UserStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config()->set('services.openai.api_key', 'sk-ai-copilot-endpoint-test');
});

test('the analysis route is post only and uses the required name and middleware', function () {
    $route = Route::getRoutes()->getByName('ai-copilot.analyze');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('ai-copilot/analyze')
        ->and($route->methods())->toBe(['POST'])
        ->and($route->gatherMiddleware())->toContain(
            'web',
            'auth',
            'verified',
            'throttle:ai-copilot',
        );

    $this->getJson('/ai-copilot/analyze')->assertMethodNotAllowed();
});

test('guests and unverified users cannot analyze', function () {
    $this->postJson(route('ai-copilot.analyze'))->assertUnauthorized();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson(route('ai-copilot.analyze'))
        ->assertForbidden();

    Http::assertNothingSent();
});

test('suspended users retain the existing active account behavior', function () {
    $user = User::factory()->create(['status' => UserStatus::Suspended]);

    $this->actingAs($user)
        ->postJson(route('ai-copilot.analyze'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    Http::assertNothingSent();
});

test('impersonation is blocked before financial snapshot or openai work', function () {
    $user = User::factory()->create();
    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $response = $this->actingAs($user)
        ->withSession(['impersonator_id' => 999])
        ->postJson(route('ai-copilot.analyze'));

    $response->assertForbidden()
        ->assertExactJson(['message' => 'لا يمكن إجراء التحليل المالي أثناء انتحال الحساب.']);
    expect($queries)->toBe([]);
    Http::assertNothingSent();
});

test('a verified user receives only normalized json and the gpt boundary is called once', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user)->create(['initial_balance' => 100]);

    Http::fake([
        OpenAiCopilotService::ENDPOINT => Http::response(aiEndpointProviderResponse(aiEndpointStableResult())),
    ]);

    $response = $this->actingAs($user)->postJson(route('ai-copilot.analyze'));

    $response->assertOk()->assertExactJson(aiEndpointStableResult());
    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->url() === OpenAiCopilotService::ENDPOINT);

    $json = $response->getContent();
    foreach (['financial_snapshot', 'risk_analysis', 'resp_', 'api_key', 'provider', 'raw', 'sk-ai-copilot'] as $forbidden) {
        expect($json)->not->toContain($forbidden);
    }
});

test('insufficient financial data is returned locally without an http request', function () {
    $user = User::factory()->create();
    Http::fake();

    $response = $this->actingAs($user)->postJson(route('ai-copilot.analyze'));

    $response->assertOk()
        ->assertJsonPath('health_status', 'insufficient_data')
        ->assertJsonPath('insights', [])
        ->assertJsonPath('actions', [])
        ->assertJsonStructure([
            'health_status', 'summary_ar', 'insights', 'actions', 'limitations_ar', 'disclaimer_ar',
        ]);
    expect(array_keys($response->json()))->toBe([
        'health_status', 'summary_ar', 'insights', 'actions', 'limitations_ar', 'disclaimer_ar',
    ]);
    Http::assertNothingSent();
});

test('outbound analysis preserves user and invoice isolation and leaves financial tables unchanged', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create([
        'initial_balance' => 100,
        'description' => 'owner-wallet-sensitive-text',
    ]);
    $otherWallet = Wallet::factory()->for($other)->create([
        'initial_balance' => 987654.32,
        'description' => 'foreign-wallet-sensitive-text',
    ]);

    Transaction::factory()->for($user)->for($wallet)->create([
        'type' => TransactionType::Expense,
        'amount' => 200,
        'description' => 'owner-transaction-sensitive-text',
    ]);
    Transaction::factory()->for($other)->for($otherWallet)->create([
        'type' => TransactionType::Income,
        'amount' => 876543.21,
        'description' => 'foreign-transaction-sensitive-text',
    ]);

    $client = Client::factory()->for($user)->create(['name' => 'owner-client-sensitive-text']);
    $otherClient = Client::factory()->for($other)->create(['name' => 'foreign-client-sensitive-text']);
    Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Overdue,
        'due_date' => today()->subDay(),
        'total' => 300,
        'notes' => 'owner-invoice-sensitive-text',
    ]);
    Invoice::factory()->for($other)->for($otherClient)->create([
        'status' => InvoiceStatus::Overdue,
        'due_date' => today()->subDay(),
        'total' => 765432.10,
        'notes' => 'foreign-invoice-sensitive-text',
    ]);

    $tables = ['transactions', 'wallets', 'wallet_transfers', 'projects', 'invoices', 'debts'];
    $before = aiEndpointFinancialTableState($tables);
    $outbound = null;

    Http::fake(function (Request $request) use (&$outbound) {
        $outbound = $request->data();

        return Http::response(aiEndpointProviderResponse(aiEndpointCriticalResult()));
    });

    $response = $this->actingAs($user)->postJson(route('ai-copilot.analyze'));

    $response->assertOk()->assertExactJson(aiEndpointCriticalResult());
    Http::assertSentCount(1);
    expect(aiEndpointFinancialTableState($tables))->toBe($before);

    $outboundJson = json_encode($outbound, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    foreach ([
        '987654.32', '876543.21', '765432.1',
        'foreign-wallet-sensitive-text', 'foreign-transaction-sensitive-text',
        'foreign-client-sensitive-text', 'foreign-invoice-sensitive-text',
        'owner-wallet-sensitive-text', 'owner-transaction-sensitive-text',
        'owner-client-sensitive-text', 'owner-invoice-sensitive-text',
    ] as $sentinel) {
        expect($outboundJson)->not->toContain($sentinel);
    }

    $input = json_decode($outbound['input'][1]['content'], true, 512, JSON_THROW_ON_ERROR);
    expect($input['financial_snapshot']['currencies']['SAR']['invoices']['overdue_amount'])->toBe(300.0)
        ->and($input['financial_snapshot']['currencies']['SAR']['invoices']['count'])->toBe(1)
        ->and($input['financial_snapshot']['currencies']['SAR']['transactions']['expenses'])->toBe(200.0);
});

test('configuration and provider failures return one generic arabic service error', function (string $failure) {
    $user = User::factory()->create();
    Wallet::factory()->for($user)->create(['initial_balance' => 100]);

    if ($failure === 'configuration') {
        config()->set('services.openai.api_key', '');
        Http::fake();
    } else {
        Http::fake([
            OpenAiCopilotService::ENDPOINT => Http::response([
                'provider_secret' => 'raw-provider-payload-sentinel',
            ], 401),
        ]);
    }

    $response = $this->actingAs($user)->postJson(route('ai-copilot.analyze'));

    $response->assertStatus(503)->assertExactJson([
        'message' => 'تعذر إكمال التحليل المالي الآن. يرجى المحاولة لاحقاً.',
    ]);
    expect($response->getContent())
        ->not->toContain('raw-provider-payload-sentinel')
        ->not->toContain('OpenAI')
        ->not->toContain('sk-ai-copilot');

    if ($failure === 'configuration') {
        Http::assertNothingSent();
    } else {
        Http::assertSentCount(1);
    }
})->with(['configuration', 'provider']);

test('five requests per user per hour are allowed and quotas are isolated by user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Wallet::factory()->for($user)->create(['initial_balance' => 100]);
    Wallet::factory()->for($other)->create(['initial_balance' => 100]);

    Http::fake([
        OpenAiCopilotService::ENDPOINT => Http::response(aiEndpointProviderResponse(aiEndpointStableResult())),
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->actingAs($user)
            ->postJson(route('ai-copilot.analyze'))
            ->assertOk();
    }

    $this->actingAs($user)
        ->postJson(route('ai-copilot.analyze'))
        ->assertTooManyRequests();

    $this->actingAs($other)
        ->postJson(route('ai-copilot.analyze'))
        ->assertOk();

    Http::assertSentCount(6);
});

function aiEndpointStableResult(): array
{
    return [
        'health_status' => 'stable',
        'summary_ar' => 'الوضع المالي مستقر وفق البيانات المالية المجمعة المتاحة.',
        'insights' => [],
        'actions' => [],
        'limitations_ar' => [],
        'disclaimer_ar' => 'هذه إرشادات تشغيلية تعليمية وليست نصيحة مهنية متخصصة.',
    ];
}

function aiEndpointCriticalResult(): array
{
    return [
        'health_status' => 'critical',
        'summary_ar' => 'يحتاج الوضع المالي إلى مراجعة بسبب التدفق النقدي السلبي.',
        'insights' => [[
            'title_ar' => 'تدفق نقدي سلبي',
            'explanation_ar' => 'تجاوزت المصروفات الدخل ضمن البيانات المالية المجمعة.',
            'severity' => 'critical',
            'currency' => 'SAR',
            'evidence_codes' => ['negative_cash_flow'],
        ]],
        'actions' => [],
        'limitations_ar' => [],
        'disclaimer_ar' => 'هذه إرشادات تشغيلية تعليمية وليست نصيحة مهنية متخصصة.',
    ];
}

function aiEndpointProviderResponse(array $result): array
{
    return [
        'id' => 'resp_private_provider_id',
        'status' => 'completed',
        'incomplete_details' => null,
        'output' => [[
            'id' => 'msg_private_provider_id',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [[
                'type' => 'output_text',
                'text' => json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]],
        ]],
    ];
}

function aiEndpointFinancialTableState(array $tables): array
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
