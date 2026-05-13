<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TransactionResource;
use App\Models\Transaction;
use App\Modules\Transactions\Actions\CreateTransactionAction;
use App\Modules\Transactions\Actions\DeleteTransactionAction;
use App\Modules\Transactions\Actions\UpdateTransactionAction;
use App\Modules\Transactions\DTOs\TransactionData;
use App\Support\Enums\TransactionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;

class TransactionApiController extends Controller
{
    public function __construct(
        private readonly CreateTransactionAction $createAction,
        private readonly UpdateTransactionAction $updateAction,
        private readonly DeleteTransactionAction $deleteAction,
    ) {}

    /**
     * GET /api/v1/transactions
     * يدعم: ?type=income|expense&from=Y-m-d&to=Y-m-d&project_id=&per_page=20
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = Transaction::with(['project', 'category'])
            ->when($request->get('type'), fn($q, $t) => $q->where('type', $t))
            ->when($request->get('project_id'), fn($q, $id) => $q->where('project_id', $id))
            ->when($request->get('from'), fn($q, $from) => $q->whereDate('transaction_date', '>=', $from))
            ->when($request->get('to'), fn($q, $to) => $q->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date')
            ->paginate($request->integer('per_page', 20));

        return TransactionResource::collection($transactions);
    }

    /**
     * POST /api/v1/transactions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'             => ['required', new Enum(TransactionType::class)],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'currency'         => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'description'      => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'project_id'       => ['nullable', 'string', 'exists:projects,id'],
            'category_id'      => ['nullable', 'string', 'exists:categories,id'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference'        => ['nullable', 'string', 'max:100'],
        ]);

        $transaction = $this->createAction->execute(
            TransactionData::fromRequest($validated)
        );

        return response()->json(
            new TransactionResource($transaction->load(['project', 'category'])),
            201
        );
    }

    /**
     * GET /api/v1/transactions/{transaction}
     */
    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json(
            new TransactionResource($transaction->load(['project', 'category']))
        );
    }

    /**
     * PUT /api/v1/transactions/{transaction}
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type'             => ['sometimes', new Enum(TransactionType::class)],
            'amount'           => ['sometimes', 'numeric', 'min:0.01', 'max:999999999'],
            'currency'         => ['sometimes', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'description'      => ['sometimes', 'string', 'max:255'],
            'transaction_date' => ['sometimes', 'date'],
            'project_id'       => ['nullable', 'string', 'exists:projects,id'],
            'category_id'      => ['nullable', 'string', 'exists:categories,id'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference'        => ['nullable', 'string', 'max:100'],
        ]);

        // دمج القيم الحالية مع القيم المُحدَّثة
        $validated = array_merge([
            'type'             => $transaction->type->value,
            'amount'           => $transaction->amount,
            'currency'         => $transaction->currency,
            'description'      => $transaction->description,
            'transaction_date' => $transaction->transaction_date->toDateString(),
            'project_id'       => $transaction->project_id,
            'category_id'      => $transaction->category_id,
        ], $validated);

        $transaction = $this->updateAction->execute(
            $transaction,
            TransactionData::fromRequest($validated)
        );

        return response()->json(
            new TransactionResource($transaction->load(['project', 'category']))
        );
    }

    /**
     * DELETE /api/v1/transactions/{transaction}
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);
        $this->deleteAction->execute($transaction);

        return response()->json(['message' => 'تم حذف المعاملة.']);
    }
}
