<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Teste de listagem de usuários.
     */
    public function test_user_can_list_users(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'uuid',
                            'name',
                            'email',
                            'avatar',
                            'phone',
                            'role',
                            'status',
                            'last_login_at',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de usuário.
     */
    public function test_user_can_create_user(): void
    {
        Sanctum::actingAs($this->user);

        $name = fake()->name();
        $email = fake()->safeEmail();

        $response = $this->postJson('/api/users', [
            'name'                  => $name,
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'phone'                 => fake()->phoneNumber(),
            'role'                  => 'seller',
            'status'                => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Usuário criado com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name'  => $name,
            'email' => $email,
        ]);
    }

    /**
     * Teste de criação de usuário com dados inválidos.
     */
    public function test_user_cannot_create_user_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/users', [
            'name'                  => '',
            'email'                 => 'invalid-email',
            'password'              => '123',
            'password_confirmation' => 'different',
            'role'                  => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    /**
     * Teste de visualização de usuário.
     */
    public function test_user_can_view_user(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid'  => $user->uuid,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Teste de atualização de usuário.
     */
    public function test_user_can_update_user(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $name  = fake()->name();
        $phone = fake()->phoneNumber();

        $response = $this->putJson('/api/users/' . $user->uuid, [
            'name'  => $name,
            'phone' => $phone,
            'role'  => 'manager',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuário atualizado com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'name'  => $name,
                    'phone' => $phone,
                    'role'  => 'manager',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'uuid'  => $user->uuid,
            'name'  => $name,
            'phone' => $phone,
        ]);
    }

    /**
     * Teste de atualização de usuário com dados inválidos.
     */
    public function test_user_cannot_update_user_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $response = $this->putJson('/api/users/' . $user->uuid, [
            'name'  => '',
            'email' => 'invalid-email',
            'role'  => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'role']);
    }

    /**
     * Teste de exclusão de usuário.
     */
    public function test_user_can_delete_user(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $response = $this->deleteJson('/api/users/' . $user->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuário deletado com sucesso.',
            ]);

        $this->assertSoftDeleted('users', [
            'uuid' => $user->uuid,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_users(): void
    {
        Sanctum::actingAs($this->user);

        User::factory()->create(['role' => 'admin',   'status' => true]);
        User::factory()->create(['role' => 'manager', 'status' => true]);
        User::factory()->create(['role' => 'seller',  'status' => false]);

        // Filtro por role
        $response = $this->getJson('/api/users?role=manager');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por status
        $response = $this->getJson('/api/users?status=1');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_users(): void
    {
        Sanctum::actingAs($this->user);

        User::factory()->create([
            'name'  => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        User::factory()->create([
            'name'  => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        // Busca por nome
        $response = $this->getJson('/api/users?search=João');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'name' => 'João Silva',
                        ],
                    ],
                ],
            ]);

        // Busca por email
        $response = $this->getJson('/api/users?search=maria@example');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'email' => 'maria@example.com',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_users_without_authentication(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
