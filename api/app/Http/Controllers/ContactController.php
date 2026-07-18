<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {}

    /**
     * Listar todos os contatos.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['company_id', 'decision_maker', 'search']);
        $contacts = $this->contactService->getAll($filters);

        return response()->json([
            'data' => $contacts,
        ]);
    }

    /**
     * Mostrar um contato específico.
     */
    public function show(string $uuid): JsonResponse
    {
        $contact = $this->contactService->findByUuid($uuid);

        return response()->json([
            'data' => $contact,
        ]);
    }

    /**
     * Criar um novo contato.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->contactService->create($request->validated());

        return response()->json([
            'message' => 'Contato criado com sucesso.',
            'data' => $contact,
        ], 201);
    }

    /**
     * Atualizar um contato.
     */
    public function update(UpdateContactRequest $request, string $uuid): JsonResponse
    {
        $contact = $this->contactService->update($uuid, $request->validated());

        return response()->json([
            'message' => 'Contato atualizado com sucesso.',
            'data' => $contact,
        ]);
    }

    /**
     * Deletar um contato.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->contactService->delete($uuid);

        return response()->json([
            'message' => 'Contato deletado com sucesso.',
        ]);
    }
}
