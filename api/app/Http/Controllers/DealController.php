<?php

namespace App\Http\Controllers;

use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Requests\Deal\UpdateDealRequest;
use App\Services\DealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function __construct(
        private readonly DealService $dealService
    ) {}

    /**
     * Listar todos os deals.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'company_id', 'contact_id', 'pipeline_stage_id', 'assigned_to', 'search']);
        $deals = $this->dealService->getAll($filters);

        return response()->json([
            'data' => $deals,
        ]);
    }

    /**
     * Mostrar um deal específico.
     */
    public function show(string $uuid): JsonResponse
    {
        $deal = $this->dealService->findByUuid($uuid);

        return response()->json([
            'data' => $deal,
        ]);
    }

    /**
     * Criar um novo deal.
     */
    public function store(StoreDealRequest $request): JsonResponse
    {
        $deal = $this->dealService->create($request->validated());

        return response()->json([
            'message' => 'Deal criado com sucesso.',
            'data' => $deal,
        ], 201);
    }

    /**
     * Atualizar um deal.
     */
    public function update(UpdateDealRequest $request, string $uuid): JsonResponse
    {
        $deal = $this->dealService->update($uuid, $request->validated());

        return response()->json([
            'message' => 'Deal atualizado com sucesso.',
            'data' => $deal,
        ]);
    }

    /**
     * Deletar um deal.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->dealService->delete($uuid);

        return response()->json([
            'message' => 'Deal deletado com sucesso.',
        ]);
    }
}