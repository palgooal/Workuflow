<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        $teamMembers = TeamMember::orderBy('name')->get();

        return view('team.index', compact('teamMembers'));
    }

    public function create(): View
    {
        return view('team.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active', true);

        TeamMember::create($validated);

        return redirect()
            ->route('team.index')
            ->with('success', 'تم إضافة عضو الفريق بنجاح.');
    }

    public function edit(TeamMember $team): View
    {
        return view('team.edit', ['teamMember' => $team]);
    }

    public function update(Request $request, TeamMember $team): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active', false);

        $team->update($validated);

        return redirect()
            ->route('team.index')
            ->with('success', 'تم تحديث بيانات عضو الفريق.');
    }

    public function destroy(TeamMember $team): RedirectResponse
    {
        $team->delete();

        return redirect()
            ->route('team.index')
            ->with('success', 'تم حذف عضو الفريق.');
    }

    private function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'type'         => ['required', 'in:employee,freelancer'],
            'specialty'    => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email'],
            'default_rate' => ['nullable', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }
}
