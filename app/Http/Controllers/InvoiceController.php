<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Transaction;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\TransactionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    // ==================== List ====================

    public function index(Request $request): View
    {
        $invoices = Invoice::forUser($request->user()->id)
            ->with(['client', 'project'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    // ==================== Create ====================

    public function create(Request $request): View
    {
        $clients  = Client::where('user_id', $request->user()->id)
            ->where('is_archived', false)->orderBy('name')->get();
        $projects = Project::where('user_id', $request->user()->id)
            ->where('is_active', true)->orderBy('name')->get();
        $statuses = InvoiceStatus::cases();

        // pre-fill client if passed via query string
        $selectedClient = $request->query('client_id')
            ? Client::where('user_id', $request->user()->id)
                    ->where('id', $request->query('client_id'))->first()
            : null;

        return view('invoices.create', compact('clients', 'projects', 'statuses', 'selectedClient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'project_id'  => 'nullable|exists:projects,id',
            'title'       => 'nullable|string|max:255',
            'issue_date'  => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:issue_date',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'discount'    => 'nullable|numeric|min:0',
            'currency'    => 'required|string|size:3',
            'notes'       => 'nullable|string',
            'terms'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        // تحقق من أن العميل يخص المستخدم
        $client = Client::where('id', $data['client_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $invoice = Invoice::create([
            'user_id'    => $request->user()->id,
            'client_id'  => $client->id,
            'project_id' => $data['project_id'] ?? null,
            'title'      => $data['title'] ?? null,
            'status'     => InvoiceStatus::Draft,
            'issue_date' => $data['issue_date'],
            'due_date'   => $data['due_date'] ?? null,
            'tax_rate'   => $data['tax_rate'] ?? 0,
            'discount'   => $data['discount'] ?? 0,
            'currency'   => $data['currency'],
            'notes'      => $data['notes'] ?? null,
            'terms'      => $data['terms'] ?? null,
        ]);

        foreach ($data['items'] as $i => $item) {
            $total = $item['quantity'] * $item['unit_price'];
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $total,
                'sort_order'  => $i,
            ]);
        }

        $invoice->load('items');
        $invoice->recalculate();

        return redirect()
            ->route('invoices.show', $invoice->ulid)
            ->with('success', "تم إنشاء الفاتورة {$invoice->number} بنجاح.");
    }

    // ==================== Show ====================

    public function show(Request $request, string $ulid): View
    {
        $invoice = Invoice::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with(['client', 'project', 'items'])
            ->firstOrFail();

        return view('invoices.show', compact('invoice'));
    }

    // ==================== Edit ====================

    public function edit(Request $request, string $ulid): View
    {
        $invoice  = Invoice::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with(['client', 'project', 'items'])
            ->firstOrFail();

        $clients  = Client::where('user_id', $request->user()->id)
            ->where('is_archived', false)->orderBy('name')->get();
        $projects = Project::where('user_id', $request->user()->id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'clients', 'projects'));
    }

    public function update(Request $request, string $ulid): RedirectResponse
    {
        $invoice = Invoice::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'project_id'  => 'nullable|exists:projects,id',
            'title'       => 'nullable|string|max:255',
            'issue_date'  => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:issue_date',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'discount'    => 'nullable|numeric|min:0',
            'currency'    => 'required|string|size:3',
            'notes'       => 'nullable|string',
            'terms'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'client_id'  => $data['client_id'],
            'project_id' => $data['project_id'] ?? null,
            'title'      => $data['title'] ?? null,
            'issue_date' => $data['issue_date'],
            'due_date'   => $data['due_date'] ?? null,
            'tax_rate'   => $data['tax_rate'] ?? 0,
            'discount'   => $data['discount'] ?? 0,
            'currency'   => $data['currency'],
            'notes'      => $data['notes'] ?? null,
            'terms'      => $data['terms'] ?? null,
        ]);

        // أعد بناء البنود
        $invoice->items()->delete();
        foreach ($data['items'] as $i => $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
                'sort_order'  => $i,
            ]);
        }

        $invoice->load('items');
        $invoice->recalculate();

        return redirect()
            ->route('invoices.show', $invoice->ulid)
            ->with('success', 'تم تحديث الفاتورة بنجاح.');
    }

    // ==================== Status Actions ====================

    public function markSent(Request $request, string $ulid): RedirectResponse
    {
        $invoice = Invoice::where('ulid', $ulid)->where('user_id', $request->user()->id)->firstOrFail();
        $invoice->update(['status' => InvoiceStatus::Sent, 'sent_at' => now()]);
        return back()->with('success', 'تم تحديث حالة الفاتورة إلى مُرسَلة.');
    }

    public function markPaid(Request $request, string $ulid): RedirectResponse
    {
        $invoice = Invoice::where('ulid', $ulid)
            ->where('user_id', $request->user()->id)
            ->with('client')
            ->firstOrFail();

        // تجنب تسجيل معاملة مكررة إذا كانت مدفوعة مسبقاً
        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('info', 'الفاتورة مسجّلة كمدفوعة مسبقاً.');
        }

        $paidAt = now();

        $invoice->update([
            'status'  => InvoiceStatus::Paid,
            'paid_at' => $paidAt,
        ]);

        // ── تسجيل معاملة دخل تلقائياً ──────────────────────────────
        // لا نُرسل project_id عندما يكون null تفادياً لـ "Column cannot be null"
        // (إرسال null صراحةً يتعارض مع قيد DB، أما حذف العمود يتركه للـ DEFAULT)
        $txData = [
            'user_id'          => $invoice->user_id,
            'type'             => TransactionType::Income,
            'amount'           => $invoice->total,
            'currency'         => $invoice->currency,
            'description'      => 'فاتورة ' . $invoice->number
                                  . ($invoice->title ? ' — ' . $invoice->title : ''),
            'payee'            => $invoice->client->name,
            'transaction_date' => $paidAt->toDateString(),
            'reference'        => $invoice->number,
            'notes'            => 'تم الإنشاء تلقائياً عند تسجيل دفع الفاتورة.',
        ];

        if ($invoice->project_id) {
            $txData['project_id'] = $invoice->project_id;
        }

        Transaction::create($txData);

        return back()->with('success', '✅ تم تسجيل الدفع وإضافة معاملة الدخل بنجاح.');
    }

    public function cancel(Request $request, string $ulid): RedirectResponse
    {
        $invoice = Invoice::where('ulid', $ulid)->where('user_id', $request->user()->id)->firstOrFail();
        $invoice->update(['status' => InvoiceStatus::Cancelled]);
        return back()->with('success', 'تم إلغاء الفاتورة.');
    }

    public function destroy(Request $request, string $ulid): RedirectResponse
    {
        $invoice = Invoice::where('ulid', $ulid)->where('user_id', $request->user()->id)->firstOrFail();
        $clientId = $invoice->client->public_id;
        $invoice->delete();
        return redirect()->route('clients.show', $clientId)->with('success', 'تم حذف الفاتورة.');
    }
}
