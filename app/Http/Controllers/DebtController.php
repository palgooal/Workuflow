<?php

namespace App\Http\Controllers;

use App\Http\Requests\Debts\RecordPaymentRequest;
use App\Http\Requests\Debts\StoreDebtRequest;
use App\Models\Debt;
use App\Modules\Debts\Actions\CreateDebtAction;
use App\Modules\Debts\Actions\DeleteDebtAction;
use App\Modules\Debts\Actions\MarkDebtAsPaidAction;
use App\Modules\Debts\Actions\RecordPartialPaymentAction;
use App\Modules\Debts\DTOs\DebtData;
use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function __construct(
        private readonly CreateDebtAction           $createAction,
        private readonly DeleteDebtAction           $deleteAction,
        private readonly RecordPartialPaymentAction $recordPaymentAction,
        private readonly MarkDebtAsPaidAction       $markAsPaidAction,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'borrowed');

        $statusOrder = "CASE status WHEN 'active' THEN 1 WHEN 'partially_paid' THEN 2 WHEN 'paid' THEN 3 ELSE 4 END";

        $borrowed = Debt::borrowed()
            ->with('project')
            ->orderByRaw($statusOrder)
            ->orderBy('due_date')
            ->get();

        $lent = Debt::lent()
            ->with('project')
            ->orderByRaw($statusOrder)
            ->orderBy('due_date')
            ->get();

        $summary = [
            'borrowed_total'    => $borrowed->where('status', '!=', DebtStatus::Paid)->sum('remaining_amount'),
            'lent_total'        => $lent->where('status', '!=', DebtStatus::Paid)->sum('remaining_amount'),
            'borrowed_overdue'  => $borrowed->filter(fn($d) => $d->isOverdue())->count(),
            'lent_overdue'      => $lent->filter(fn($d) => $d->isOverdue())->count(),
        ];

        return view('debts.index', compact('borrowed', 'lent', 'summary', 'tab'));
    }

    public function create(): View
    {
        $currencies = ['SAR', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];

        return view('debts.create', compact('currencies'));
    }

    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->createAction->execute(
            DebtData::fromRequest($request->validated())
        );

        return redirect()
            ->route('debts.index')
            ->with('success', 'تم إضافة الدين بنجاح.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);
        $this->deleteAction->execute($debt);

        return redirect()
            ->route('debts.index')
            ->with('success', 'تم حذف الدين.');
    }

    public function recordPayment(RecordPaymentRequest $request, Debt $debt): RedirectResponse
    {
        $this->authorize('update', $debt);

        $this->recordPaymentAction->execute($debt, (float) $request->amount);

        return redirect()
            ->route('debts.index')
            ->with('success', 'تم تسجيل الدفعة بنجاح.');
    }

    public function markAsPaid(Debt $debt): RedirectResponse
    {
        $this->authorize('update', $debt);

        $this->markAsPaidAction->execute($debt);

        return redirect()
            ->route('debts.index')
            ->with('success', 'تم تحديد الدين كمدفوع بالكامل.');
    }
}
