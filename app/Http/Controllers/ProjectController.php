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
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Models\ProjectServiceMember;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;
use App\Support\Enums\InvoiceStatus;
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
        $clients     = Client::where('user_id', auth()->id())->where('is_archived', false)->orderBy('name')->get();
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
                    'amount' => $svc['amount'],
                    'type'   => 'income',
                    'notes'  => $svc['notes'] ?? null,
                ];
            }
            $project->services()->sync($syncData);

            // حفظ منفذي كل خدمة
            $this->syncServiceMembers($project, $validated['services']);
        }

        // ── إنشاء فاتورة مسودة تلقائياً عند ربط المشروع بعميل ──
        if (! empty($validated['client_id'])) {
            $this->createDraftInvoice($project, $validated);
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
        // تحميل بيانات المنفذين داخل الـ summary يتم في calcServicesMargin

        $recentTransactions = $project->transactions()
            ->with('category')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        $projectQuotes = \App\Models\Quote::where('project_id', $project->id)
            ->where('user_id', $project->user_id)
            ->orderByDesc('created_at')
            ->get();

        return view('projects.show', compact('project', 'summary', 'recentTransactions', 'projectQuotes'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        $project->load(['client', 'services']);
        $currencies  = ['SAR', 'ILS', 'USD', 'EUR', 'GBP', 'AED', 'KWD'];
        $colors      = $this->defaultColors();
        $clients     = Client::where('user_id', auth()->id())->where('is_archived', false)->orderBy('name')->get();
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
                    'amount' => $svc['amount'],
                    'type'   => 'income',
                    'notes'  => $svc['notes'] ?? null,
                ];
            }
            $project->services()->sync($syncData);

            // تحديث منفذي كل خدمة
            $this->syncServiceMembers($project, $validated['services']);
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

    public function payTeamMember(Project $project, string $memberId): RedirectResponse
    {
        $this->authorize('update', $project);

        $member = ProjectServiceMember::with('teamMember')
            ->whereHas('projectService', fn ($q) => $q->where('project_id', $project->id))
            ->where('id', $memberId)
            ->first();

        if (! $member || ! $member->team_cost || $member->team_cost_paid) {
            return back()->with('error', 'لا يمكن تسجيل الدفعة.');
        }

        $service = $project->services()
            ->wherePivot('id', $member->project_service_id)
            ->first();

        Transaction::create([
            'user_id'          => auth()->id(),
            'project_id'       => $project->id,
            'type'             => TransactionType::Expense,
            'amount'           => $member->team_cost,
            'currency'         => $project->currency,
            'description'      => 'دفعة لـ ' . ($member->teamMember?->name ?? 'فريق') . ' - ' . ($service?->name_ar ?? $service?->name ?? ''),
            'payee'            => $member->teamMember?->name,
            'transaction_date' => now(),
            'reference'        => 'team_member_' . $member->id,
        ]);

        $member->update(['team_cost_paid' => true]);

        return back()->with('success', 'تم تسجيل الدفعة كمصروف على المشروع.');
    }

    // ==================== Private Helpers ====================

    private function syncServiceMembers(Project $project, array $services): void
    {
        foreach ($services as $svc) {
            $pivotRow = DB::table('project_service')
                ->where('project_id', $project->id)
                ->where('service_id', $svc['service_id'])
                ->first();

            if (! $pivotRow) {
                continue;
            }

            // حذف المنفذين القدامى لهذه الخدمة
            ProjectServiceMember::where('project_service_id', $pivotRow->id)->delete();

            // إدراج المنفذين الجدد
            if (! empty($svc['members'])) {
                foreach ($svc['members'] as $memberData) {
                    if (empty($memberData['team_member_id'])) {
                        continue;
                    }
                    ProjectServiceMember::create([
                        'project_service_id' => $pivotRow->id,
                        'team_member_id'     => $memberData['team_member_id'],
                        'team_cost'          => $memberData['team_cost'] ?? null,
                        'team_cost_paid'     => false,
                    ]);
                }
            }
        }
    }

    /**
     * إنشاء فاتورة مسودة مرتبطة بالمشروع والعميل.
     * تُضاف بنود تلقائية من خدمات المشروع (إن وُجدت)،
     * أو بند واحد من قيمة العقد.
     */
    private function createDraftInvoice(Project $project, array $validated): void
    {
        $invoice = Invoice::create([
            'user_id'    => auth()->id(),
            'client_id'  => $project->client_id,
            'project_id' => $project->id,
            'title'      => $project->name,
            'currency'   => $project->currency,
            'status'     => InvoiceStatus::Draft,
            'issue_date' => now()->toDateString(),
            'due_date'   => now()->addDays(30)->toDateString(),
            'subtotal'   => 0,
            'tax_rate'   => 0,
            'tax_amount' => 0,
            'discount'   => 0,
            'total'      => 0,
        ]);

        // فقط خدمات الدخل تُضاف للفاتورة — خدمات expense هي تكاليف تشغيلية لا تُفاتَر للعميل
        $services = array_filter(
            $validated['services'] ?? [],
            fn ($svc) => ($svc['type'] ?? 'income') === 'income'
        );

        if (! empty($services)) {
            // بند لكل خدمة دخل مضافة للمشروع
            $serviceModels = Service::whereIn('id', array_column($services, 'service_id'))
                ->pluck('name_ar', 'id');

            foreach (array_values($services) as $index => $svc) {
                $amount = (float) ($svc['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $serviceModels[$svc['service_id']] ?? 'خدمة',
                    'quantity'    => 1,
                    'unit_price'  => $amount,
                    'total'       => $amount,
                    'sort_order'  => $index,
                ]);
            }
        } elseif (! empty($project->contract_value) && $project->contract_value > 0) {
            // بند واحد من قيمة العقد إن لم توجد خدمات
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'خدمات مشروع ' . $project->name,
                'quantity'    => 1,
                'unit_price'  => $project->contract_value,
                'total'       => $project->contract_value,
                'sort_order'  => 0,
            ]);
        }

        // إعادة حساب المجاميع بناءً على البنود المُضافة
        $invoice->load('items');
        $invoice->recalculate();
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
