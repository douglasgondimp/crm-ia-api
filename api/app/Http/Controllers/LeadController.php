<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService
    ) {}

    /**
     * Listar todos os leads.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'source', 'temperature', 'assigned_to', 'search']);
        $leads = $this->leadService->getAll($filters);

        return response()->json([
            'data' => $leads,
        ]);
    }

    /**
     * Mostrar um lead específico.
     */
    public function show(string $uuid): JsonResponse
    {
        $lead = $this->leadService->findByUuid($uuid);

        return response()->json([
            'data' => $lead,
        ]);
    }

    /**
     * Criar um novo lead.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->create($request->validated());

        return response()->json([
            'message' => 'Lead criado com sucesso.',
            'data' => $lead,
        ], 201);
    }

    /**
     * Atualizar um lead.
     */
    public function update(UpdateLeadRequest $request, string $uuid): JsonResponse
    {
        $lead = $this->leadService->update($uuid, $request->validated());

        return response()->json([
            'message' => 'Lead atualizado com sucesso.',
            'data' => $lead,
        ]);
    }

    /**
     * Deletar um lead.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->leadService->delete($uuid);

        return response()->json([
            'message' => 'Lead deletado com sucesso.',
        ]);
    }
}