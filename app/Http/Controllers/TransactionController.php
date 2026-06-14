<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Modules\Transactions\Actions\CreateTransactionAction;
use App\Modules\Transactions\Actions\DeleteTransactionAction;
use App\Modules\Transactions\Actions\UpdateTransactionAction;
use App\Modules\Transactions\DTOs\TransactionData;
use App\Modules\Transactions\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Support\Helpers\Currency;

class TransactionController extends Controller
{
    public function __construct(
        private readonly CreateTransactionAction $createAction,
        private readonly UpdateTransactionAction $updateAction,
        private readonly DeleteTransactionAction $deleteAction,
        private readonly TransactionService      $service,
    ) {}

    public function index(Request $request): View
    {
        $transactions = $this->service->getPaginated($request);
        $summary      = $this->service->getSummary($request);
        $projects     = Project::active()->orderBy('name')->get();
        $categories   = Category::orderBy('type')->orderBy('name')->get();

        return view('transactions.index', compact(
            'transactions', 'summary', 'projects', 'categories'
        ));
    }

    public function create(Request $request): View
    {
        $projects   = Project::active()->orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $wallets    = Wallet::active()->orderBy('name')->get();
        $currencies = Currency::all();
        $preProject = $request->query('project');

        return view('transactions.create', compact('projects', 'categories', 'wallets', 'currencies', 'preProject'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $transaction = $this->createAction->execute(
            TransactionData::fromRequest($request->validated())
        );

        $redirect = $request->filled('redirect_to')
            ? $request->redirect_to
            : route('transactions.index');

        return redirect($redirect)
            ->with('success', 'تم إضافة المعاملة بنجاح.');
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);
        $transaction->load(['project', 'category']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);
        $projects   = Project::active()->orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $wallets    = Wallet::active()->orderBy('name')->get();
        $currencies = Currency::all();

        return view('transactions.edit', compact('transaction', 'projects', 'categories', 'wallets', 'currencies'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $this->updateAction->execute(
            $transaction,
            TransactionData::fromRequest($request->validated())
        );

        return redirect()
            ->route('transactions.index')
            ->with('success', 'تم تحديث المعاملة بنجاح.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $this->deleteAction->execute($transaction);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'تم حذف المعاملة.');
    }
}
