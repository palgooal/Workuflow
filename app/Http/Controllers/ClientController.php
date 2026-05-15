<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::where('user_id', auth()->id())
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $data['user_id']   = auth()->id();
        $data['is_active'] = true;

        Client::create($data);

        return redirect()
            ->route('clients.index')
            ->with('success', 'تم إضافة العميل "' . $data['name'] . '" بنجاح.');
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'company'   => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $client->update($data);

        return redirect()
            ->route('clients.index')
            ->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $name = $client->name;
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'تم حذف العميل "' . $name . '".');
    }
}
