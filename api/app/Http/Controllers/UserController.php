<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Listar todos os usuários.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['role', 'status', 'search']);
        $users = $this->userService->getAll($filters);

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * Mostrar um usuário específico.
     */
    public function show(string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuid($uuid);

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Criar um novo usuário.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'data' => $user,
        ], 201);
    }

    /**
     * Atualizar um usuário.
     */
    public function update(UpdateUserRequest $request, string $uuid): JsonResponse
    {
        $user = $this->userService->update($uuid, $request->validated());

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'data' => $user,
        ]);
    }

    /**
     * Deletar um usuário.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->userService->delete($uuid);

        return response()->json([
            'message' => 'Usuário deletado com sucesso.',
        ]);
    }
}
