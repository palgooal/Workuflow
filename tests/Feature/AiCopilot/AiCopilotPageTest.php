<?php

use App\Models\User;
use App\Support\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

test('authenticated verified users can open the arabic rtl copilot page', function () {
    $user = User::factory()->create(['phone' => '0500000000']);

    $response = $this->actingAs($user)->get(route('ai-copilot.index'));

    $response->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('المساعد المالي الذكي')
        ->assertSee('ابدأ التحليل')
        ->assertSee('ai-copilot\\/analyze', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('aria-current="page"', false);
});

test('guest unverified and suspended users retain existing page protections', function () {
    $this->get(route('ai-copilot.index'))->assertRedirect(route('login'));

    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified)
        ->get(route('ai-copilot.index'))
        ->assertRedirect(route('verification.notice'));

    $suspended = User::factory()->create(['status' => UserStatus::Suspended]);
    $this->actingAs($suspended)
        ->get(route('ai-copilot.index'))
        ->assertRedirect(route('login'));
    $this->assertGuest();
});

test('get page route has the expected name method and middleware protection', function () {
    $route = Route::getRoutes()->getByName('ai-copilot.index');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('ai-copilot')
        ->and($route->methods())->toBe(['GET', 'HEAD'])
        ->and($route->gatherMiddleware())->toContain('web', 'auth', 'verified');
});

test('page renders only the normalized public result contract', function () {
    $user = User::factory()->create(['phone' => '0500000001']);

    $response = $this->actingAs($user)->get(route('ai-copilot.index'));

    foreach ([
        'result?.health_status',
        'result?.summary_ar',
        'result?.insights',
        'result?.actions',
        'result?.limitations_ar',
        'result?.disclaimer_ar',
        'insight.severity',
        'insight.currency',
    ] as $binding) {
        $response->assertSee($binding, false);
    }

    foreach ([
        'financial_snapshot',
        'risk_analysis',
        'provider_response',
        'response_id',
        'api_key',
        'raw_prompt',
    ] as $forbidden) {
        $response->assertDontSee($forbidden, false);
    }
});

test('page includes every result and failure state in arabic', function () {
    $user = User::factory()->create(['phone' => '0500000002']);

    $response = $this->actingAs($user)->get(route('ai-copilot.index'));

    foreach ([
        'لم يبدأ التحليل بعد',
        'جارٍ إعداد التحليل',
        'مستقرة',
        'تحتاج إلى انتباه',
        'حرجة',
        'بيانات غير كافية',
        'تم بلوغ حد التحليل المؤقت',
        'يمكن إجراء خمسة تحليلات كل ساعة',
        'الخدمة غير متاحة الآن',
        'تعذر الوصول إلى خدمة التحليل حالياً',
        'تعذر إكمال التحليل',
        'حدث خطأ غير متوقع',
    ] as $message) {
        $response->assertSee($message);
    }
});

test('page load never starts analysis or persists a result', function () {
    $user = User::factory()->create(['phone' => '0500000003']);
    Http::fake();
    $source = file_get_contents(resource_path('views/ai-copilot/index.blade.php'));

    $response = $this->actingAs($user)->get(route('ai-copilot.index'));

    $response->assertOk()
        ->assertSee('@click="analyze"', false);
    expect($source)
        ->toContain('@click="analyze"')
        ->not->toContain('x-init="analyze')
        ->not->toContain('localStorage')
        ->not->toContain('sessionStorage')
        ->not->toContain('document.cookie')
        ->not->toContain('navigator.sendBeacon');
    Http::assertNothingSent();
});

test('insufficient data remains local and page analysis does not mutate financial tables', function () {
    $user = User::factory()->create(['phone' => '0500000004']);
    $tables = ['transactions', 'wallets', 'wallet_transfers', 'projects', 'invoices', 'debts'];
    $before = aiCopilotPageFinancialTableState($tables);
    Http::fake();

    $this->actingAs($user)->get(route('ai-copilot.index'))->assertOk();

    $this->postJson(route('ai-copilot.analyze'))
        ->assertOk()
        ->assertJsonPath('health_status', 'insufficient_data')
        ->assertJsonPath('insights', [])
        ->assertJsonPath('actions', []);

    expect(aiCopilotPageFinancialTableState($tables))->toBe($before);
    Http::assertNothingSent();
});

function aiCopilotPageFinancialTableState(array $tables): array
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
