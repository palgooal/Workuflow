<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recurring\StoreRecurringRequest;
use App\Http\Requests\Recurring\UpdateRecurringRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\RecurringTransaction;
use App\Modules\Recurring\Actions\CreateRecurringAction;
use App\Modules\Recurring\Actions\DeleteRecurringAction;
use App\Modules\Recurring\Actions\ProcessRecurringAction;
use App\Modules\Recurring\Actions\UpdateRecurringAction;
use App\Modules\Recurring\DTOs\RecurringData;
use App\Modules\Recurring\Services\RecurringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringController extends Controller
{
    public function __construct(
        private readonly RecurringService       $service,
        private readonly CreateRecurringAction  $createAction,
        private readonly UpdateRecurringAction  $updateAction,
        private readonly DeleteRecurringAction  $deleteAction,
        private readonly ProcessRecurringAction $processAction,
    ) {}

    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all'); // all | active | inactive
        $recurrings = $this->service->getAll($filter === 'active');
        $summary    = $this->service->getSummary();

        $frequencies = [
            'daily'   => 'يومي',
            'weekly'  => 'أسبوعي',
            'monthly' => 'شهري',
            'yearly'  => 'سنوي',
        ];

        return view('recurring.index', compact('recurrings', 'summary', 'filter', 'frequencies'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $projects   = Project::active()->orderBy('name')->get();

        $frequencies = [
            'daily'   => 'يومي',
            'weekly'  => 'أسبوعي',
            'monthly' => 'شهري',
            'yearly'  => 'سنوي',
        ];

        return view('recurring.create', compact('categories', 'projects', 'frequencies'));
    }

    public function store(StoreRecurringRequest $request): RedirectResponse
    {
        $data = RecurringData::fromRequest($request->validated());
        $this->createAction->execute($data);

        return redirect()
            ->route('recurring.index')
            ->with('success', 'تم إنشاء الالتزام المتكرر بنجاح.');
    }

    public function edit(RecurringTransaction $recurring): View
    {
        $this->authorize('update', $recurring);

        $categories = Category::orderBy('type')->orderBy('name')->get();
        $projects   = Project::active()->orderBy('name')->get();

        $frequencies = [
            'daily'   => 'يومي',
            'weekly'  => 'أسبوعي',
            'monthly' => 'شهري',
            'yearly'  => 'سنوي',
        ];

        return view('recurring.edit', compact('recurring', 'categories', 'projects', 'frequencies'));
    }

    public function update(UpdateRecurringRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $data = RecurringData::fromRequest($request->validated());
        $this->updateAction->execute($recurring, $data);

        return redirect()
            ->route('recurring.index')
            ->with('success', 'تم تحديث الالتزام المتكرر.');
    }

    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);
        $this->deleteAction->execute($recurring);

        return redirect()
            ->route('recurring.index')
            ->with('success', 'تم حذف الالتزام المتكرر.');
    }

    /**
     * تفعيل/إيقاف الالتزام المتكرر
     */
    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $recurring->update(['is_active' => ! $recurring->is_active]);

        $msg = $recurring->is_active ? 'تم تفعيل الالتزام.' : 'تم إيقاف الالتزام.';

        return redirect()->route('recurring.index')->with('success', $msg);
    }

    /**
     * معالجة يدوية فورية (تنفيذ المعاملة الآن)
     */
    public function processNow(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->processAction->execute($recurring);

        return redirect()
            ->route('recurring.index')
            ->with('success', 'تم تنفيذ المعاملة وتسجيلها.');
    }
}
