<?php

use App\Modules\AiCopilot\DTOs\FinancialSnapshot;
use App\Modules\AiCopilot\Services\OpenAiCopilotService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.openai.api_key', 'sk-step-three-test-key');
    config()->set('services.openai.timeout', 30);
    config()->set('services.openai.max_output_tokens', 1800);
});

test('sends the strict stateless GPT-5.6 request and parses output after reasoning', function () {
    config()->set('services.openai.max_output_tokens', 999999);
    Http::fake([
        OpenAiCopilotService::ENDPOINT => Http::response(openAiProviderResponse(openAiValidResult())),
    ]);

    $result = openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis());

    expect($result)->toBe(openAiValidResult());

    Http::assertSent(function ($request) {
        $payload = $request->data();

        expect($request->url())->toBe(OpenAiCopilotService::ENDPOINT)
            ->and($request->hasHeader('Authorization', 'Bearer sk-step-three-test-key'))->toBeTrue()
            ->and($request->hasHeader('Content-Type', 'application/json'))->toBeTrue()
            ->and($payload['model'])->toBe('gpt-5.6')
            ->and($payload['store'])->toBeFalse()
            ->and($payload['max_output_tokens'])->toBe(OpenAiCopilotService::MAX_MAX_OUTPUT_TOKENS)
            ->and($payload['text']['format']['type'])->toBe('json_schema')
            ->and($payload['text']['format']['strict'])->toBeTrue()
            ->and($payload['text']['format']['name'])->toBe('darahum_financial_copilot')
            ->and($payload['text']['format']['schema']['properties']['health_status']['enum'])
            ->toBe(['critical'])
            ->and($payload['text']['format']['schema']['properties']['insights']['items']['properties']['evidence_codes']['minItems'])
            ->toBe(1)
            ->and($payload['text']['format']['schema']['properties']['limitations_ar']['minItems'])
            ->toBe(1)
            ->and($payload['text']['format']['schema']['properties']['insights']['items']['properties']['currency']['enum'])->toBe(['SAR', null])
            ->and($payload['text']['format']['schema']['properties']['insights']['items']['properties']['evidence_codes']['items']['enum'])->toBe([
                'excluded_cross_currency_transfers',
                'negative_cash_flow',
            ])
            ->and(array_intersect(array_keys($payload), [
                'tools',
                'response_format',
                'previous_response_id',
                'conversation',
                'background',
                'files',
                'web_search',
            ]))->toBe([])
            ->and($payload['input'][0]['role'])->toBe('developer')
            ->and($payload['input'][0]['content'])->toContain('must not contain Latin letters')
            ->and($payload['input'][0]['content'])->toContain('dedicated currency field')
            ->and($payload['input'][1]['role'])->toBe('user');

        assertStrictSchemaObjects($payload['text']['format']['schema']);

        $input = json_decode($payload['input'][1]['content'], true, 512, JSON_THROW_ON_ERROR);
        $encodedInput = json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        expect(array_keys($input))->toBe(['financial_snapshot', 'risk_analysis'])
            ->and($input['financial_snapshot']['currencies']['SAR']['transactions'])->toBe([
                'income' => 100.0,
                'expenses' => 150.0,
                'net' => -50.0,
                'count' => 2,
            ])
            ->and($input['risk_analysis']['currencies']['SAR']['signals'][0]['code'])
            ->toBe('negative_cash_flow');

        foreach (openAiSensitiveSentinels() as $sentinel) {
            expect($encodedInput)->not->toContain($sentinel);
        }

        return true;
    });

    Http::assertSentCount(1);
});

test('health status is derived locally and constrained to one schema value', function () {
    $warningSignal = [
        'code' => 'overdue_invoices',
        'severity' => 'warning',
        'evidence' => ['overdue_count' => 1, 'overdue_amount' => 20.0],
    ];
    $dataQualityWarning = [
        'code' => 'excluded_cross_currency_transfers',
        'severity' => 'warning',
        'evidence' => ['count' => 1],
    ];

    $scenarios = [
        'critical signal' => [
            openAiSnapshot(0),
            openAiRiskFixture(signals: [openAiRiskAnalysis()['currencies']['SAR']['signals'][0]]),
            openAiResultFixture('critical', 'negative_cash_flow', 'critical', 'SAR'),
            'critical',
        ],
        'financial warning' => [
            openAiSnapshot(0),
            openAiRiskFixture(signals: [$warningSignal]),
            openAiResultFixture('attention', 'overdue_invoices', 'warning', 'SAR'),
            'attention',
        ],
        'data quality warning' => [
            openAiSnapshot(1),
            openAiRiskFixture(warnings: [$dataQualityWarning]),
            openAiResultFixture(
                'attention',
                'excluded_cross_currency_transfers',
                'warning',
                null,
                ['توجد قيود في بيانات التحويلات بين العملات.']
            ),
            'attention',
        ],
        'insufficient data' => [
            openAiEmptySnapshot(),
            openAiRiskFixture(state: 'insufficient_data', currencies: []),
            openAiResultFixture('insufficient_data'),
            'insufficient_data',
        ],
        'healthy data' => [
            openAiHealthySnapshot(),
            openAiRiskFixture(),
            openAiResultFixture('stable'),
            'stable',
        ],
    ];

    foreach ($scenarios as [$snapshot, $riskAnalysis, $providerResult, $expectedStatus]) {
        Http::swap(new Factory);
        Http::fake([
            OpenAiCopilotService::ENDPOINT => Http::response(openAiProviderResponse($providerResult)),
        ]);

        $result = openAiService()->generateInsights($snapshot, $riskAnalysis);
        $request = Http::recorded()->last()[0];

        expect($result['health_status'])->toBe($expectedStatus)
            ->and($request->data()['text']['format']['schema']['properties']['health_status']['enum'])
            ->toBe([$expectedStatus]);
    }
});

test('missing API key fails before making an HTTP request', function () {
    config()->set('services.openai.api_key', '');
    Http::fake();

    expect(fn () => openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis()))
        ->toThrow(RuntimeException::class, 'OpenAI Copilot is not configured.');

    Http::assertNothingSent();
});

test('authentication and authorization failures never retry', function (int $status) {
    Http::fakeSequence()->push(['provider_secret' => 'raw-auth-secret'], $status);

    expect(fn () => openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis()))
        ->toThrow(RuntimeException::class, 'OpenAI Copilot request failed.');

    Http::assertSentCount(1);
})->with([401, 403]);

test('rate limits and server failures retry exactly once', function (int $status) {
    Http::fakeSequence()
        ->push(['provider_secret' => 'transient-raw-secret'], $status)
        ->push(openAiProviderResponse(openAiValidResult()));

    $result = openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis());

    expect($result)->toBe(openAiValidResult());
    Http::assertSentCount(2);
})->with([429, 500, 503]);

test('a connection failure retries exactly once and can recover', function () {
    Http::fakeSequence()
        ->pushFailedConnection('connection-failure-sensitive-detail')
        ->push(openAiProviderResponse(openAiValidResult()));

    $result = openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis());

    expect($result)->toBe(openAiValidResult());
    Http::assertSentCount(2);
});

test('timeout failure retries at most once and remains sanitized', function () {
    Http::fakeSequence()
        ->pushFailedConnection('timeout sk-step-three-test-key foreign-financial-payload-987654')
        ->pushFailedConnection('timeout raw-provider-response-secret');

    try {
        openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis());
        $this->fail('Expected a controlled timeout failure.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('OpenAI Copilot request failed.')
            ->not->toContain('sk-step-three-test-key')
            ->not->toContain('987654')
            ->not->toContain('raw-provider-response-secret');
    }

    Http::assertSentCount(2);
});

test('provider failures never expose credentials financial input or raw response', function () {
    Http::fakeSequence()
        ->push(['error' => 'raw-provider-response-secret'], 500)
        ->push(['error' => 'raw-provider-response-secret'], 500);

    try {
        openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis());
        $this->fail('Expected a controlled provider failure.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('OpenAI Copilot request failed.')
            ->not->toContain('sk-step-three-test-key')
            ->not->toContain('foreign-financial-payload-987654')
            ->not->toContain('raw-provider-response-secret');
    }

    Http::assertSentCount(2);
});

test('unsafe or malformed provider output fails safely without retry', function (array $providerResponse) {
    Http::fake([
        OpenAiCopilotService::ENDPOINT => Http::response($providerResponse),
    ]);

    expect(fn () => openAiService()->generateInsights(openAiSnapshot(), openAiRiskAnalysis()))
        ->toThrow(RuntimeException::class, 'OpenAI Copilot returned an unusable response.');

    Http::assertSentCount(1);
})->with([
    'refusal' => fn () => [[
        'status' => 'completed',
        'output' => [[
            'type' => 'message',
            'content' => [['type' => 'refusal', 'refusal' => 'raw refusal detail']],
        ]],
    ]],
    'incomplete response' => fn () => [[
        'status' => 'incomplete',
        'incomplete_details' => ['reason' => 'max_output_tokens'],
        'output' => [],
    ]],
    'missing output text' => fn () => [[
        'status' => 'completed',
        'output' => [['type' => 'reasoning', 'summary' => []]],
    ]],
    'malformed JSON' => fn () => [openAiProviderResponseText('{not-json')],
    'unknown evidence code' => function () {
        $result = openAiValidResult();
        $result['insights'][0]['evidence_codes'] = ['invented_signal'];

        return [openAiProviderResponse($result)];
    },
    'unsupported health enum' => function () {
        $result = openAiValidResult();
        $result['health_status'] = 'excellent';

        return [openAiProviderResponse($result)];
    },
    'mismatched derived health status' => function () {
        $result = openAiValidResult();
        $result['health_status'] = 'attention';

        return [openAiProviderResponse($result)];
    },
    'insight without evidence when evidence exists' => function () {
        $result = openAiValidResult();
        $result['insights'][0]['evidence_codes'] = [];

        return [openAiProviderResponse($result)];
    },
    'financial evidence with null currency' => function () {
        $result = openAiValidResult();
        $result['insights'][0]['currency'] = null;

        return [openAiProviderResponse($result)];
    },
    'data quality evidence with financial currency' => function () {
        $result = openAiValidResult();
        $result['insights'][0] = [
            'title_ar' => 'قيد في جودة البيانات',
            'explanation_ar' => 'تم استبعاد تحويل لا يملك سعر صرف موثوقا.',
            'severity' => 'warning',
            'currency' => 'SAR',
            'evidence_codes' => ['excluded_cross_currency_transfers'],
        ];

        return [openAiProviderResponse($result)];
    },
    'severity inconsistent with cited evidence' => function () {
        $result = openAiValidResult();
        $result['insights'][0]['severity'] = 'warning';

        return [openAiProviderResponse($result)];
    },
    'missing required data quality limitation' => function () {
        $result = openAiValidResult();
        $result['limitations_ar'] = [];

        return [openAiProviderResponse($result)];
    },
    'unsupported priority enum' => function () {
        $result = openAiValidResult();
        $result['actions'][0]['priority'] = 'urgent';

        return [openAiProviderResponse($result)];
    },
    'non Arabic content' => function () {
        $result = openAiValidResult();
        $result['summary_ar'] = 'English only';

        return [openAiProviderResponse($result)];
    },
    'mixed Arabic and Latin content' => function () {
        $result = openAiValidResult();
        $result['summary_ar'] = 'ملخص English مختلط';

        return [openAiProviderResponse($result)];
    },
    'empty Arabic content' => function () {
        $result = openAiValidResult();
        $result['summary_ar'] = '   ';

        return [openAiProviderResponse($result)];
    },
]);

test('zero evidence requires an empty insights array', function () {
    $result = openAiResultFixture('stable');
    $result['insights'] = [[
        'title_ar' => 'معلومة عامة',
        'explanation_ar' => 'لا يوجد دليل رقمي يدعم هذه المعلومة.',
        'severity' => 'info',
        'currency' => null,
        'evidence_codes' => [],
    ]];
    Http::fake([
        OpenAiCopilotService::ENDPOINT => Http::response(openAiProviderResponse($result)),
    ]);

    expect(fn () => openAiService()->generateInsights(openAiSnapshot(0), openAiRiskFixture()))
        ->toThrow(RuntimeException::class, 'OpenAI Copilot returned an unusable response.');

    Http::assertSent(function ($request) {
        $schema = $request->data()['text']['format']['schema'];

        return $schema['properties']['insights']['maxItems'] === 0
            && $schema['properties']['insights']['items']['properties']['evidence_codes']['minItems'] === 0;
    });
});

test('service has no database model Auth logging or direct container dependency', function () {
    $reflection = new ReflectionClass(OpenAiCopilotService::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->not->toContain('App\\Models')
        ->not->toContain('DB::')
        ->not->toContain('Auth::')
        ->not->toContain('auth(')
        ->not->toContain('Log::')
        ->not->toContain('logger(')
        ->not->toContain('app(')
        ->not->toContain('Container');
});

function openAiService(): OpenAiCopilotService
{
    return new OpenAiCopilotService;
}

function openAiSnapshot(int $excludedCrossCurrencyTransfers = 1): FinancialSnapshot
{
    return new FinancialSnapshot(
        currencies: [
            'SAR' => [
                'transactions' => [
                    'income' => 100.0,
                    'expenses' => 150.0,
                    'net' => -50.0,
                    'count' => 2,
                    'description' => 'owner-description-sensitive',
                ],
                'wallets' => [
                    'balance' => 75.0,
                    'count' => 1,
                    'wallet_id' => 'wallet-record-id-sensitive',
                ],
                'invoices' => [
                    'count' => 1,
                    'outstanding_count' => 1,
                    'outstanding_amount' => 20.0,
                    'overdue_count' => 0,
                    'overdue_amount' => 0.0,
                    'client_email' => 'foreign-client@example.test',
                ],
                'debts' => [
                    'borrowed_count' => 0,
                    'borrowed_remaining' => 0.0,
                    'lent_count' => 0,
                    'lent_remaining' => 0.0,
                    'overdue_count' => 0,
                    'overdue_remaining' => 0.0,
                    'notes' => 'foreign-financial-payload-987654',
                ],
                'projects' => [
                    'count' => 0,
                    'active_count' => 0,
                    'contract_value' => 0.0,
                    'expense_budget' => 0.0,
                    'project_name' => 'project-sensitive-name',
                ],
            ],
        ],
        dataQuality: [
            'excluded_cross_currency_transfers' => $excludedCrossCurrencyTransfers,
            'api_key' => 'snapshot-injected-secret',
        ],
    );
}

function openAiHealthySnapshot(): FinancialSnapshot
{
    $snapshot = openAiSnapshot(0);
    $currencies = $snapshot->currencies;
    $currencies['SAR']['transactions'] = [
        'income' => 100.0,
        'expenses' => 20.0,
        'net' => 80.0,
        'count' => 2,
    ];

    return new FinancialSnapshot(
        currencies: $currencies,
        dataQuality: ['excluded_cross_currency_transfers' => 0],
    );
}

function openAiEmptySnapshot(): FinancialSnapshot
{
    return new FinancialSnapshot(
        currencies: [],
        dataQuality: ['excluded_cross_currency_transfers' => 0],
    );
}

function openAiRiskAnalysis(): array
{
    return [
        'state' => 'analyzed',
        'data_quality_warnings' => [[
            'code' => 'excluded_cross_currency_transfers',
            'severity' => 'warning',
            'evidence' => ['count' => 1, 'description' => 'warning-sensitive-description'],
        ]],
        'currencies' => [
            'SAR' => [
                'signals' => [[
                    'code' => 'negative_cash_flow',
                    'severity' => 'critical',
                    'evidence' => [
                        'income' => 100.0,
                        'expenses' => 150.0,
                        'net' => -50.0,
                        'user_id' => 998877,
                    ],
                    'label' => 'risk-sensitive-label',
                ]],
            ],
        ],
        'user_email' => 'risk-owner@example.test',
    ];
}

function openAiRiskFixture(
    string $state = 'analyzed',
    array $signals = [],
    array $warnings = [],
    array $currencies = ['SAR'],
): array {
    $currencyAnalysis = [];

    foreach ($currencies as $currency) {
        $currencyAnalysis[$currency] = ['signals' => $signals];
    }

    return [
        'state' => $state,
        'data_quality_warnings' => $warnings,
        'currencies' => $currencyAnalysis,
    ];
}

function openAiResultFixture(
    string $healthStatus,
    ?string $evidenceCode = null,
    string $severity = 'info',
    ?string $currency = null,
    array $limitations = [],
): array {
    $insights = [];

    if ($evidenceCode !== null) {
        $insights[] = [
            'title_ar' => 'نتيجة مدعومة بالبيانات',
            'explanation_ar' => 'تعتمد هذه النتيجة على الإشارة الرقمية المحددة في البيانات.',
            'severity' => $severity,
            'currency' => $currency,
            'evidence_codes' => [$evidenceCode],
        ];
    }

    return [
        'health_status' => $healthStatus,
        'summary_ar' => 'هذا ملخص للحالة المالية استنادا إلى البيانات المجمعة المتاحة.',
        'insights' => $insights,
        'actions' => [],
        'limitations_ar' => $limitations,
        'disclaimer_ar' => 'هذه إرشادات تعليمية تشغيلية وليست نصيحة مهنية متخصصة.',
    ];
}

function openAiValidResult(): array
{
    return [
        'health_status' => 'critical',
        'summary_ar' => 'يحتاج الوضع المالي إلى الانتباه بسبب التدفق النقدي السلبي.',
        'insights' => [[
            'title_ar' => 'التدفق النقدي سلبي',
            'explanation_ar' => 'تجاوزت المصروفات الدخل ضمن البيانات المجمعة المتاحة.',
            'severity' => 'critical',
            'currency' => 'SAR',
            'evidence_codes' => ['negative_cash_flow'],
        ]],
        'actions' => [[
            'title_ar' => 'راجع المصروفات',
            'rationale_ar' => 'يساعد ذلك على فهم البنود التشغيلية التي يمكن ضبطها دون تغيير السجلات.',
            'priority' => 'high',
        ]],
        'limitations_ar' => ['تم استبعاد تحويل بعملتين مختلفتين لعدم وجود سعر صرف موثوق.'],
        'disclaimer_ar' => 'هذه إرشادات تعليمية تشغيلية وليست نصيحة محاسبية أو استثمارية أو ضريبية أو قانونية.',
    ];
}

function openAiProviderResponse(array $result): array
{
    return openAiProviderResponseText(json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
}

function openAiProviderResponseText(string $text): array
{
    return [
        'id' => 'resp_test',
        'status' => 'completed',
        'incomplete_details' => null,
        'output' => [
            ['id' => 'rs_test', 'type' => 'reasoning', 'summary' => []],
            [
                'id' => 'msg_test',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'output_text', 'text' => $text]],
            ],
        ],
    ];
}

function openAiSensitiveSentinels(): array
{
    return [
        'owner-description-sensitive',
        'wallet-record-id-sensitive',
        'foreign-client@example.test',
        'foreign-financial-payload-987654',
        'project-sensitive-name',
        'snapshot-injected-secret',
        'warning-sensitive-description',
        '998877',
        'risk-sensitive-label',
        'risk-owner@example.test',
        'sk-step-three-test-key',
    ];
}

function assertStrictSchemaObjects(array $schema): void
{
    if (($schema['type'] ?? null) === 'object') {
        expect($schema['additionalProperties'] ?? null)->toBeFalse();
        $propertyKeys = array_keys($schema['properties'] ?? []);
        $requiredKeys = $schema['required'] ?? [];
        sort($propertyKeys);
        sort($requiredKeys);
        expect($requiredKeys)->toBe($propertyKeys);
    }

    foreach ($schema as $value) {
        if (is_array($value)) {
            if (array_is_list($value)) {
                foreach ($value as $item) {
                    if (is_array($item)) {
                        assertStrictSchemaObjects($item);
                    }
                }
            } else {
                assertStrictSchemaObjects($value);
            }
        }
    }
}
