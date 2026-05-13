<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DebtResource;
use App\Models\Debt;
use App\Modules\Debts\Actions\CreateDebtAction;
use App\Modules\Debts\Actions\DeleteDebtAction;
use App\Modules\Debts\Actions\MarkDebtAsPaidAction;
use App\Modules\Debts\Actions\RecordPartialPaymentAction;
use App\Modules\Debts\DTOs\DebtData;
use App\Support\Enums\DebtType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;

class DebtApiController extends Controller
{
    public function __construct(
        private readonly CreateDebtAction           $createAction,
        private readonly DeleteDebtAction           $deleteAction,
        private readonly RecordPartialPaymentAction $recordPaymentAction,
        private readonly MarkDebtAsPaidAction       $markAsPaidAction,
    ) {}

    /**
     * GET /api/v1/debts
     * يدعم: ?type=borrowed|lent&status=active|partially_paid|paid
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $debts = Debt::with('project')
            ->when($request->get('type'), fn($q, $t) => $q->where('type', $t))
            ->when($request->get('status'), fn($q, $s) => $q->where('status', $s))
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'partially_paid' THEN 2 WHEN 'paid' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->get();

        return DebtResource::collection($debts);
    }

    /**
     * POST /api/v1/debts
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => ['required', new Enum(DebtType::class)],
            'party_name' => ['required', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'currency'   => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'due_date'   => ['nullable', 'date', 'after:today'],
            'project_id' => ['nullable', 'string', 'exists:projects,id'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $debt = $this->createAction->execute(
            DebtData::fromRequest($validated)
        );

        return response()->json(new DebtResource($debt), 201);
    }

    /**
     * GET /api/v1/debts/{debt}
     */
    public function show(Debt $debt): JsonResponse
    {
        return response()->json(new DebtResource($debt->load('project')));
    }

    /**
     * POST /api/v1/debts/{debt}/record-payment
     */
    public function recordPayment(Request $request, Debt $debt): JsonResponse
    {
        $this->authorize('update', $debt);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$debt->remaining_amount}"],
        ]);

        $this->recordPaymentAction->execute($debt, (float) $validated['amount']);
        $debt->refresh();

        return response()->json(new DebtResource($debt));
    }

    /**
     * POST /api/v1/debts/{debt}/mark-paid
     */
    public function markAsPaid(Debt $debt): JsonResponse
    {
        $this->authorize('update', $debt);
        $this->markAsPaidAction->execute($debt);
        $debt->refresh();

        return response()->json(new DebtResource($debt));
    }

    /**
     * DELETE /api/v1/debts/{debt}
     */
    public function destroy(Debt $debt): JsonResponse
    {
        $this->authorize('delete', $debt);
        $this->deleteAction->execute($debt);

        return response()->json(['message' => 'تم حذف الدين.']);
    }
}
