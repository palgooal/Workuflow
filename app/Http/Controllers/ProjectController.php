<?php

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Actions\DeleteProjectAction;
use App\Modules\Projects\Actions\UpdateProjectAction;
use App\Modules\Projects\DTOs\ProjectData;
use App\Modules\Projects\Services\ProjectFinancialService;
use App\Models\Client;
use App\Models\Service;
use App\Models\TeamMember;
use App\Support\Enums\ProjectType;
use App\Support\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly CreateProjectAction      $createAction,
        private readonly UpdateProjectAction      $updateAction,
        private readonly DeleteProjectAction      $deleteAction,
        private readonly ProjectFinancialService  $financialService,
    ) {}

    public function index(): View
    {
        $projects = Project::withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($p) => $p->type->value);

        $portfolio = $this->financialService->getPortfolioSummary();

        return view('projects.index', compact('projects', 'portfolio'));
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        $currencies  = ['SAR', 'ILS', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];
        $colors      = $this->defaultColors();
        $clients     = Client::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $services    = Service::active()->forUser(auth()->id())->orderBy('name_ar')->get();
        $teamMembers = TeamMember::where('user_id', auth()->id())->active()->orderBy('name')->get();

        return view('projects.create', compact('currencies', 'colors', 'clients', 'services', 'teamMembers'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validated();

        $project = $this->createAction->execute(
            ProjectData::fromRequest($validated)
        );

        // حفظ العميل
        if (! empty($validated['client_id'])) {
            $project->update(['client_id' => $validated['client_id']]);
        }

        // حفظ الخدمات
        if (! empty($validated['services'])) {
            $syncData = [];
            foreach ($validated['services'] as $svc) {
                $syncData[$svc['service_id']] = [
                    'amount'         => $svc['amount'],
                    'type'           => $svc['type'],
                    'notes'          => $svc['notes'] ?? null,
                    'team_member_id' => $svc['team_member_id'] ?? null,
                    'team_cost'      => $svc['team_cost'] ?? null,
                    'team_cost_paid' => false,
                ];
            }
            $project->services()->sync($syncData);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'تم إنشاء المشروع "' . $project->name . '" بنجاح.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['client', 'services']);
        $project->loadCount('transactions');
        $summary = $this->financialService->getSummary($project);

        $recentTransactions = $project->transactions()
            ->with('category')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        return view('projects.show', compact('project', 'summary', 'recentTransactions'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        $project->load(['client', 'services']);
        $currencies  = ['SAR', 'ILS', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];
        $colors      = $this->defaultColors();
        $clients     = Client::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $services    = Service::active()->forUser(auth()->id())->orderBy('name_ar')->get();
        $teamMembers = TeamMember::where('user_id', auth()->id())->active()->orderBy('name')->get();

        return view('projects.edit', compact('project', 'currencies', 'colors', 'clients', 'services', 'teamMembers'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $this->updateAction->execute(
            $project,
            ProjectData::fromRequest($validated)
        );

        // تحديث العميل
        $project->update(['client_id' => $validated['client_id'] ?? null]);

        // تحديث الخدمات
        if (isset($validated['services'])) {
            $syncData = [];
            foreach ($validated['services'] as $svc) {
                $syncData[$svc['service_id']] = [
                    'amount'         => $svc['amount'],
                    'type'           => $svc['type'],
                    'notes'          => $svc['notes'] ?? null,
                    'team_member_id' => $svc['team_member_id'] ?? null,
                    'team_cost'      => $svc['team_cost'] ?? null,
                    'team_cost_paid' => false,
                ];
            }
            $project->services()->sync($syncData);
        } else {
            $project->services()->detach();
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'تم تحديث المشروع بنجاح.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $name = $project->name;
        $this->deleteAction->execute($project);

        return redirect()
            ->route('projects.index')
            ->with('success', 'تم حذف المشروع "' . $name . '".');
    }

    public function payTeamMember(Project $project, string $serviceId): RedirectResponse
    {
        $this->authorize('update', $project);

        $service = $project->services()->where('service_id', $serviceId)->first();
        if (! $service || ! $service->pivot->team_cost || $service->pivot->team_cost_paid) {
            return back()->with('error', 'لا يمكن تسجيل الدفعة.');
        }

        $member = TeamMember::find($service->pivot->team_member_id);

        Transaction::create([
            'user_id'          => auth()->id(),
            'project_id'       => $project->id,
            'type'             => TransactionType::Expense,
            'amount'           => $service->pivot->team_cost,
            'currency'         => $project->currency,
            'description'      => 'دفعة لـ ' . ($member?->name ?? 'فريق') . ' - ' . ($service->name_ar ?? $service->name),
            'payee'            => $member?->name,
            'transaction_date' => now(),
            'reference'        => 'team_service_' . $serviceId,
        ]);

        $project->services()->updateExistingPivot($serviceId, ['team_cost_paid' => true]);

        return back()->with('success', 'تم تسجيل الدفعة كمصروف على المشروع.');
    }

    private function defaultColors(): array
    {
        return [
            '#6366F1', // indigo
            '#8B5CF6', // violet
            '#EC4899', // pink
            '#EF4444', // red
            '#F97316', // orange
            '#F59E0B', // amber
            '#10B981', // emerald
            '#14B8A6', // teal
            '#3B82F6', // blue
            '#64748B', // slate
        ];
    }
}
