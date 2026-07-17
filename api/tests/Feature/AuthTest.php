<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de registro de usuário com sucesso.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $name = fake()->name();
        $email = fake()->safeEmail();

        $response = $this->postJson('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ])
            ->assertJson([
                'message' => 'Cadastro realizado com sucesso.',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
        ]);
    }

    /**
     * Teste de registro com dados inválidos.
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Teste de login com credenciais válidas.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $email = fake()->safeEmail();
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ])
            ->assertJson([
                'message' => 'Login realizado com sucesso.',
            ]);
    }

    /**
     * Teste de login com credenciais inválidas.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $email = fake()->safeEmail();

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas.',
            ]);
    }

    /**
     * Teste de login com email não cadastrado.
     */
    public function test_user_cannot_login_with_unregistered_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => fake()->safeEmail(),
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas.',
            ]);
    }

    /**
     * Teste de logout com sucesso.
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso.',
            ]);
    }

    /**
     * Teste de acesso a rota protegida sem autenticação.
     */
    public function test_user_cannot_access_protected_route_without_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /**
     * Teste de acesso a rota protegida com token válido.
     */
    public function test_user_can_access_protected_route_with_valid_token(): void
    {
        $name = fake()->name();
        $email = fake()->safeEmail();

        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $name,
                    'email' => $email,
                ],
            ]);
    }

    /**
     * Teste de atualização de perfil com sucesso.
     */
    public function test_user_can_update_profile_successfully(): void
    {
        $name = fake()->name();
        $email = fake()->safeEmail();

        $user = User::factory()->create([
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/me', [
            'name' => $name,
            'email' => $email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Perfil atualizado com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'name' => $name,
                    'email' => $email,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $name,
            'email' => $email,
        ]);
    }

    /**
     * Teste de atualização de senha com sucesso.
     */
    public function test_user_can_update_password_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/me/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Senha atualizada com sucesso.',
            ]);

        // Verifica se a senha foi atualizada no banco
        $this->assertTrue(password_verify('newpassword123', $user->fresh()->password));
    }

    /**
     * Teste de atualização de senha com senha atual incorreta.
     */
    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/me/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /**
     * Teste de login com email duplicado.
     */
    public function test_user_cannot_register_with_duplicate_email(): void
    {
        $email = fake()->safeEmail();

        User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->postJson('/api/register', [
            'name' => fake()->name(),
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
