<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransfer;
use App\Support\Enums\WalletType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    // ==================== Index ====================

    public function index(): View
    {
        $wallets = Wallet::withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at')
            ->get();

        // ملخص per-currency
        $summary = $wallets->groupBy('currency')->map(function ($group) {
            return [
                'currency' => $group->first()->currency,
                'balance'  => $group->sum(fn($w) => $w->balance()),
                'count'    => $group->count(),
            ];
        })->values();

        $types = WalletType::cases();

        return view('wallets.index', compact('wallets', 'summary', 'types'));
    }

    // ==================== Create ====================

    public function create(): View
    {
        $types = WalletType::cases();
        return view('wallets.create', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'type'            => ['required', 'in:cash,bank,custom'],
            'currency'        => ['required', 'string', 'size:3'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'color'           => ['required', 'string', 'max:7'],
            'icon'            => ['nullable', 'string', 'max:10'],
            'description'     => ['nullable', 'string', 'max:500'],
        ]);

        $data['user_id'] = auth()->id();
        Wallet::create($data);

        return redirect()->route('wallets.index')
            ->with('success', 'تم إنشاء الصندوق "' . $data['name'] . '" بنجاح.');
    }

    // ==================== Show ====================

    public function show(Wallet $wallet): View
    {
        $this->authorize('view', $wallet);

        $transactions = $wallet->transactions()
            ->with('category', 'project')
            ->latest('transaction_date')
            ->paginate(20);

        $transfers = WalletTransfer::where('from_wallet_id', $wallet->id)
            ->orWhere('to_wallet_id', $wallet->id)
            ->with('fromWallet', 'toWallet')
            ->latest('transferred_at')
            ->paginate(10);

        return view('wallets.show', compact('wallet', 'transactions', 'transfers'));
    }

    // ==================== Edit ====================

    public function edit(Wallet $wallet): View
    {
        $this->authorize('update', $wallet);
        $types = WalletType::cases();
        return view('wallets.edit', compact('wallet', 'types'));
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $this->authorize('update', $wallet);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'type'            => ['required', 'in:cash,bank,custom'],
            'currency'        => ['required', 'string', 'size:3'],
            'initial_balance' => ['required', 'numeric'],
            'color'           => ['required', 'string', 'max:7'],
            'icon'            => ['nullable', 'string', 'max:10'],
            'description'     => ['nullable', 'string', 'max:500'],
            'is_active'       => ['boolean'],
        ]);

        $wallet->update($data);

        return redirect()->route('wallets.index')
            ->with('success', 'تم تحديث الصندوق "' . $wallet->name . '".');
    }

    // ==================== Destroy ====================

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);
        $name = $wallet->name;
        $wallet->delete();

        return redirect()->route('wallets.index')
            ->with('success', 'تم حذف الصندوق "' . $name . '".');
    }

    // ==================== Transfer ====================

    public function transferCreate(): View
    {
        $wallets = Wallet::active()->orderBy('name')->get();
        return view('wallets.transfer', compact('wallets'));
    }

    public function transferStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_wallet_id' => ['required', 'ulid', 'exists:wallets,id'],
            'to_wallet_id'   => ['required', 'ulid', 'exists:wallets,id', 'different:from_wallet_id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'fee'            => ['nullable', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string', 'max:255'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'transferred_at' => ['required', 'date'],
        ]);

        // التحقق من ملكية الصناديق
        $from = Wallet::findOrFail($data['from_wallet_id']);
        $to   = Wallet::findOrFail($data['to_wallet_id']);
        $this->authorize('update', $from);
        $this->authorize('update', $to);

        $data['user_id'] = auth()->id();
        $data['fee']     = $data['fee'] ?? 0;

        WalletTransfer::create($data);

        return redirect()->route('wallets.index')
            ->with('success', 'تم التحويل من "' . $from->name . '" إلى "' . $to->name . '" بنجاح.');
    }
}
