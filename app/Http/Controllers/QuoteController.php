<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Service;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\QuoteStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    // ==================== CRUD ====================

    public function index(Request $request): View
    {
        $quotes = Quote::where('user_id', $request->user()->id)
            ->with(['client', 'project'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // إحصائيات سريعة
        $stats = Quote::where('user_id', $request->user()->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('sent','viewed') THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                SUM(CASE WHEN status = 'accepted' THEN total ELSE 0 END) as accepted_value
            ")
            ->first();

        return view('quotes.index', compact('quotes', 'stats'));
    }

    public function create(Request $request): View
    {
        $currencies  = ['SAR', 'ILS', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];
        $clients     = Client::where('user_id', $request->user()->id)
                             ->where('is_archived', false)
                             ->orderBy('name')->get();
        $projects    = Project::where('user_id', $request->user()->id)
                              ->orderByDesc('created_at')->get();
        $services    = Service::active()->forUser($request->user()->id)->orderBy('name_ar')->get();

        // pre-fill من ملف العميل أو المشروع
        $preClientId  = $request->query('client_id');
        $preProjectId = $request->query('project_id');

        return view('quotes.create', compact(
            'currencies', 'clients', 'projects', 'services',
            'preClientId', 'preProjectId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'   => ['required', 'integer', 'exists:clients,id'],
            'project_id'  => ['nullable', 'string'],
            'title'       => ['nullable', 'string', 'max:255'],
            'issue_date'  => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency'    => ['required', 'string', 'size:3'],
            'tax_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount'    => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'terms'       => ['nullable', 'string'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        // التحقق من ملكية العميل
        $client = Client::where('id', $validated['client_id'])
                        ->where('user_id', $request->user()->id)
                        ->firstOrFail();

        $quote = Quote::create([
            'user_id'     => $request->user()->id,
            'client_id'   => $client->id,
            'project_id'  => $validated['project_id'] ?: null,
            'title'       => $validated['title'] ?? null,
            'status'      => QuoteStatus::Draft,
            'issue_date'  => $validated['issue_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'currency'    => $validated['currency'],
            'tax_rate'    => $validated['tax_rate'] ?? 0,
            'discount'    => $validated['discount'] ?? 0,
            'notes'       => $validated['notes'] ?? null,
            'terms'       => $validated['terms'] ?? null,
            'subtotal'    => 0,
            'tax_amount'  => 0,
            'total'       => 0,
        ]);

        foreach ($validated['items'] as $index => $item) {
            QuoteItem::create([
                'quote_id'    => $quote->id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
                'sort_order'  => $index,
            ]);
        }

        $quote->load('items');
        $quote->recalculate();

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'تم إنشاء عرض السعر "' . $quote->number . '" بنجاح.');
    }

    public function show(Request $request, string $ulid): View
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with(['client', 'project', 'items'])
            ->firstOrFail();

        // تحديث الحالة إلى منتهية الصلاحية تلقائياً
        if ($quote->isExpired() && $quote->status !== QuoteStatus::Expired) {
            $quote->update(['status' => QuoteStatus::Expired]);
            $quote->refresh();
        }

        return view('quotes.show', compact('quote'));
    }

    public function edit(Request $request, string $ulid): View
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        abort_if(! $quote->status->isEditable(), 403, 'لا يمكن تعديل عرض غير مسودة.');

        $currencies = ['SAR', 'ILS', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];
        $clients    = Client::where('user_id', $request->user()->id)
                            ->where('is_archived', false)->orderBy('name')->get();
        $projects   = Project::where('user_id', $request->user()->id)
                             ->orderByDesc('created_at')->get();
        $services   = Service::active()->forUser($request->user()->id)->orderBy('name_ar')->get();

        return view('quotes.edit', compact('quote', 'currencies', 'clients', 'projects', 'services'));
    }

    public function update(Request $request, string $ulid): RedirectResponse
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if(! $quote->status->isEditable(), 403, 'لا يمكن تعديل عرض غير مسودة.');

        $validated = $request->validate([
            'client_id'   => ['required', 'integer', 'exists:clients,id'],
            'project_id'  => ['nullable', 'string'],
            'title'       => ['nullable', 'string', 'max:255'],
            'issue_date'  => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency'    => ['required', 'string', 'size:3'],
            'tax_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount'    => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'terms'       => ['nullable', 'string'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $quote->update([
            'client_id'   => $validated['client_id'],
            'project_id'  => $validated['project_id'] ?: null,
            'title'       => $validated['title'] ?? null,
            'issue_date'  => $validated['issue_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'currency'    => $validated['currency'],
            'tax_rate'    => $validated['tax_rate'] ?? 0,
            'discount'    => $validated['discount'] ?? 0,
            'notes'       => $validated['notes'] ?? null,
            'terms'       => $validated['terms'] ?? null,
        ]);

        $quote->items()->delete();
        foreach ($validated['items'] as $index => $item) {
            QuoteItem::create([
                'quote_id'    => $quote->id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
                'sort_order'  => $index,
            ]);
        }

        $quote->load('items');
        $quote->recalculate();

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'تم تحديث عرض السعر بنجاح.');
    }

    public function destroy(Request $request, string $ulid): RedirectResponse
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $clientId = $quote->client->public_id;
        $quote->delete();

        return redirect()
            ->route('clients.show', $clientId)
            ->with('success', 'تم حذف عرض السعر.');
    }

    // ==================== إجراءات الحالة ====================

    public function markSent(Request $request, string $ulid): RedirectResponse
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if(! $quote->status->canBeSent(), 422, 'لا يمكن إرسال هذا العرض.');

        $quote->update([
            'status'  => QuoteStatus::Sent,
            'sent_at' => now(),
        ]);

        return back()->with('success', '📤 تم تسجيل العرض كمُرسَل. شارك الرابط مع العميل: ' . $quote->portalUrl());
    }

    public function convertToInvoice(Request $request, string $ulid): RedirectResponse
    {
        $quote = Quote::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with(['items'])
            ->firstOrFail();

        abort_if(! $quote->status->canConvert(), 422, 'يمكن تحويل العروض المقبولة فقط.');

        // إنشاء الفاتورة من بيانات العرض
        $invoice = Invoice::create([
            'user_id'    => $quote->user_id,
            'client_id'  => $quote->client_id,
            'project_id' => $quote->project_id,
            'title'      => $quote->title ?? $quote->number,
            'status'     => InvoiceStatus::Draft,
            'issue_date' => now()->toDateString(),
            'due_date'   => now()->addDays(30)->toDateString(),
            'currency'   => $quote->currency,
            'tax_rate'   => $quote->tax_rate,
            'discount'   => $quote->discount,
            'notes'      => $quote->notes,
            'terms'      => $quote->terms,
            'subtotal'   => 0,
            'tax_amount' => 0,
            'total'      => 0,
            'reference'  => $quote->number,    // ربط الفاتورة بالعرض
        ]);

        // نسخ البنود
        foreach ($quote->items as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total'       => $item->total,
                'sort_order'  => $item->sort_order,
            ]);
        }

        $invoice->load('items');
        $invoice->recalculate();

        // تحديث حالة العرض
        $quote->update([
            'status'       => QuoteStatus::Converted,
            'converted_at' => now(),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', '🧾 تم تحويل العرض إلى فاتورة بنجاح.');
    }

    // ==================== بوابة العميل ====================

    /**
     * صفحة العرض العامة — يصل إليها العميل عبر /q/{token} بدون تسجيل دخول
     */
    public function portal(string $token): View
    {
        $quote = Quote::where('token', $token)
            ->with(['client', 'project', 'items', 'user'])
            ->firstOrFail();

        // تسجيل أول مشاهدة
        if (! $quote->viewed_at && $quote->status === QuoteStatus::Sent) {
            $quote->update([
                'status'    => QuoteStatus::Viewed,
                'viewed_at' => now(),
            ]);
            $quote->refresh();
        }

        // هل انتهت الصلاحية؟
        $isExpired = $quote->isExpired();

        return view('quotes.portal', compact('quote', 'isExpired'));
    }

    /**
     * العميل يقبل العرض
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $quote = Quote::where('token', $token)->firstOrFail();

        abort_if($quote->isExpired(), 422, 'انتهت صلاحية هذا العرض.');
        abort_if(
            ! in_array($quote->status, [QuoteStatus::Sent, QuoteStatus::Viewed]),
            422,
            'لا يمكن قبول هذا العرض في حالته الحالية.'
        );

        $quote->update([
            'status'      => QuoteStatus::Accepted,
            'accepted_at' => now(),
            'client_ip'   => $request->ip(),
        ]);

        return redirect()
            ->route('quotes.portal', $token)
            ->with('portal_success', '✅ شكراً! تم قبول العرض بنجاح. سنتواصل معك قريباً.');
    }

    /**
     * العميل يرفض العرض
     */
    public function reject(Request $request, string $token): RedirectResponse
    {
        $quote = Quote::where('token', $token)->firstOrFail();

        abort_if($quote->isExpired(), 422, 'انتهت صلاحية هذا العرض.');
        abort_if(
            ! in_array($quote->status, [QuoteStatus::Sent, QuoteStatus::Viewed]),
            422,
            'لا يمكن رفض هذا العرض في حالته الحالية.'
        );

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $quote->update([
            'status'           => QuoteStatus::Rejected,
            'rejected_at'      => now(),
            'client_ip'        => $request->ip(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()
            ->route('quotes.portal', $token)
            ->with('portal_info', 'تم تسجيل رفضك للعرض. يمكنك التواصل معنا لأي استفسار.');
    }
}
