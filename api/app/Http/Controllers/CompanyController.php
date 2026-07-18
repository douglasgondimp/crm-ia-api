<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {}

    /**
     * Listar todas as empresas.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['segment', 'city', 'state', 'search']);
        $companies = $this->companyService->getAll($filters);

        return response()->json([
            'data' => $companies,
        ]);
    }

    /**
     * Mostrar uma empresa específica.
     */
    public function show(string $uuid): JsonResponse
    {
        $company = $this->companyService->findByUuid($uuid);

        return response()->json([
            'data' => $company,
        ]);
    }

    /**
     * Criar uma nova empresa.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->create($request->validated());

        return response()->json([
            'message' => 'Empresa criada com sucesso.',
            'data' => $company,
        ], 201);
    }

    /**
     * Atualizar uma empresa.
     */
    public function update(UpdateCompanyRequest $request, string $uuid): JsonResponse
    {
        $company = $this->companyService->update($uuid, $request->validated());

        return response()->json([
            'message' => 'Empresa atualizada com sucesso.',
            'data' => $company,
        ]);
    }

    /**
     * Deletar uma empresa.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->companyService->delete($uuid);

        return response()->json([
            'message' => 'Empresa deletada com sucesso.',
        ]);
    }
}
