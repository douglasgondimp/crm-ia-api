<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Listar todas as atividades.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['type', 'status', 'priority', 'deal_id', 'company_id', 'contact_id', 'user_id', 'search']);
        $activities = $this->activityService->getAll($filters);

        return response()->json([
            'data' => $activities,
        ]);
    }

    /**
     * Mostrar uma atividade específica.
     */
    public function show(int $id): JsonResponse
    {
        $activity = $this->activityService->findById($id);

        return response()->json([
            'data' => $activity,
        ]);
    }

    /**
     * Criar uma nova atividade.
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $activity = $this->activityService->create($request->validated());

        return response()->json([
            'message' => 'Atividade criada com sucesso.',
            'data' => $activity,
        ], 201);
    }

    /**
     * Atualizar uma atividade.
     */
    public function update(UpdateActivityRequest $request, int $id): JsonResponse
    {
        $activity = $this->activityService->update($id, $request->validated());

        return response()->json([
            'message' => 'Atividade atualizada com sucesso.',
            'data' => $activity,
        ]);
    }

    /**
     * Deletar uma atividade.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->activityService->delete($id);

        return response()->json([
            'message' => 'Atividade deletada com sucesso.',
        ]);
    }
}
