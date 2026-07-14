<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Content\PageContentSanitizer;

function invoiceStorePayload(User $user, Client $client, array $overrides = []): array
{
    return array_merge([
        'client_id'  => $client->id,
        'issue_date' => now()->toDateString(),
        'currency'   => $user->currency ?? 'SAR',
        'items'      => [
            ['description' => 'خدمة تصميم', 'quantity' => 1, 'unit_price' => 500],
        ],
    ], $overrides);
}

test('script tags are stripped from notes and terms on invoice creation', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)->post(route('invoices.store'), invoiceStorePayload($user, $client, [
        'notes' => '<p>شكراً لكم</p><script>alert(1)</script>',
        'terms' => '<p onclick="alert(1)">الدفع خلال 14 يوم</p>',
    ]))->assertRedirect();

    $invoice = Invoice::where('user_id', $user->id)->latest('id')->first();

    expect($invoice->notes)->not->toContain('<script')
        ->and($invoice->notes)->toContain('شكراً لكم')
        ->and($invoice->terms)->not->toContain('onclick')
        ->and($invoice->terms)->toContain('الدفع خلال 14 يوم');
});

test('safe rich-text formatting from the editor survives sanitization', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)->post(route('invoices.store'), invoiceStorePayload($user, $client, [
        'notes' => '<p><strong>مهم:</strong> يرجى التحويل خلال أسبوع.</p><ul><li>بند أول</li><li>بند ثانٍ</li></ul>',
        'terms' => '<p>راجع <a href="https://example.com">الشروط الكاملة</a> هنا.</p>',
    ]))->assertRedirect();

    $invoice = Invoice::where('user_id', $user->id)->latest('id')->first();

    expect($invoice->notes)->toContain('<strong>مهم:</strong>')
        ->and($invoice->notes)->toContain('<li>بند أول</li>')
        ->and($invoice->terms)->toContain('href="https://example.com"');
});

test('the invoice show page renders sanitized notes as real HTML, not escaped text', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->withItem()->create([
        'notes' => PageContentSanitizer::clean('<p><strong>ملاحظة منسّقة</strong></p>', 'invoice_notes'),
    ]);

    $response = $this->actingAs($user)->get(route('invoices.show', $invoice->ulid));

    $response->assertOk();
    $response->assertSee('<strong>ملاحظة منسّقة</strong>', false);
    $response->assertDontSee('&lt;strong&gt;');
});

test('legacy plain-text notes with line breaks render safely with <br> instead of disappearing', function () {
    $rendered = PageContentSanitizer::renderInvoiceField("السطر الأول\nالسطر الثاني");

    expect($rendered)->toContain('<br')
        ->and($rendered)->toContain('السطر الأول')
        ->and($rendered)->toContain('السطر الثاني');
});

test('legacy plain-text notes containing a stray "<" character are escaped, not executed as HTML', function () {
    $rendered = PageContentSanitizer::renderInvoiceField('القيمة يجب أن تكون < 100 دولار');

    expect($rendered)->not->toContain('<100')
        ->and($rendered)->toContain('100');
});

test('empty or null notes render as an empty string', function () {
    expect(PageContentSanitizer::renderInvoiceField(null))->toBe('');
    expect(PageContentSanitizer::renderInvoiceField(''))->toBe('');
});
