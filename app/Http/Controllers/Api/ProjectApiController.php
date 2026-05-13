<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Actions\DeleteProjectAction;
use App\Modules\Projects\Actions\UpdateProjectAction;
use App\Modules\Projects\DTOs\ProjectData;
use App\Support\Enums\ProjectType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;

class ProjectApiController extends Controller
{
    public function __construct(
        private readonly CreateProjectAction $createAction,
        private readonly UpdateProjectAction $updateAction,
        private readonly DeleteProjectAction $deleteAction,
    ) {}

    /**
     * GET /api/v1/projects
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->when($request->get('type'), fn($q, $type) => $q->where('type', $type))
            ->orderBy('name')
            ->get();

        return ProjectResource::collection($projects);
    }

    /**
     * POST /api/v1/projects
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency'    => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'type'        => ['required', new Enum(ProjectType::class)],
            'is_active'   => ['boolean'],
        ]);

        // لون افتراضي
        $validated['color'] ??= '#6366F1';

        $project = $this->createAction->execute(
            ProjectData::fromRequest($validated)
        );

        return response()->json(new ProjectResource($project), 201);
    }

    /**
     * GET /api/v1/projects/{project}
     */
    public function show(Project $project): JsonResponse
    {
        return response()->json(new ProjectResource($project));
    }

    /**
     * PUT /api/v1/projects/{project}
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency'    => ['sometimes', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'type'        => ['sometimes', new Enum(ProjectType::class)],
            'is_active'   => ['boolean'],
        ]);

        // الحقول غير المُرسَلة تبقى من القيم الحالية
        $validated = array_merge([
            'name'      => $project->name,
            'type'      => $project->type->value,
            'currency'  => $project->currency,
            'color'     => $project->color,
            'is_active' => $project->is_active,
        ], $validated);

        $project = $this->updateAction->execute($project, ProjectData::fromRequest($validated));

        return response()->json(new ProjectResource($project));
    }

    /**
     * DELETE /api/v1/projects/{project}
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $this->deleteAction->execute($project);

        return response()->json(['message' => 'تم حذف المشروع.']);
    }
}
