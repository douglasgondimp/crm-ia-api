<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Teste de listagem de contatos.
     */
    public function test_user_can_list_contacts(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'uuid',
                            'company_id',
                            'name',
                            'job_title',
                            'email',
                            'phone',
                            'whatsapp',
                            'birthday',
                            'linkedin',
                            'instagram',
                            'decision_maker',
                            'created_by',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de contato.
     */
    public function test_user_can_create_contact(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $name = fake()->name();
        $email = fake()->unique()->safeEmail();

        $response = $this->postJson('/api/contacts', [
            'company_id' => $company->id,
            'name' => $name,
            'job_title' => fake()->jobTitle(),
            'email' => $email,
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'birthday' => fake()->date(),
            'linkedin' => fake()->url(),
            'instagram' => fake()->url(),
            'decision_maker' => fake()->boolean(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Contato criado com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'company_id',
                    'name',
                    'job_title',
                    'email',
                    'phone',
                    'whatsapp',
                    'birthday',
                    'linkedin',
                    'instagram',
                    'decision_maker',
                ],
            ]);

        $this->assertDatabaseHas('contacts', [
            'name' => $name,
            'email' => $email,
        ]);
    }

    /**
     * Teste de criação de contato com dados inválidos.
     */
    public function test_user_cannot_create_contact_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/contacts', [
            'name' => '',
            'email' => 'invalid-email',
            'linkedin' => 'invalid-url',
            'birthday' => 'invalid-date',
            'decision_maker' => 'not-a-boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'linkedin', 'birthday']);
    }

    /**
     * Teste de visualização de contato.
     */
    public function test_user_can_view_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = \App\Models\Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/contacts/' . $contact->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $contact->uuid,
                    'name' => $contact->name,
                ],
            ]);
    }

    /**
     * Teste de atualização de contato.
     */
    public function test_user_can_update_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = \App\Models\Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $name = fake()->name();

        $response = $this->putJson('/api/contacts/' . $contact->uuid, [
            'name' => $name,
            'job_title' => 'Diretor de TI',
            'decision_maker' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contato atualizado com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'name' => $name,
                    'job_title' => 'Diretor de TI',
                    'decision_maker' => true,
                ],
            ]);

        $this->assertDatabaseHas('contacts', [
            'uuid' => $contact->uuid,
            'name' => $name,
            'job_title' => 'Diretor de TI',
        ]);
    }

    /**
     * Teste de atualização de contato com dados inválidos.
     */
    public function test_user_cannot_update_contact_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $contact = \App\Models\Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson('/api/contacts/' . $contact->uuid, [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => str_repeat('1', 30),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    }

    /**
     * Teste de exclusão de contato.
     */
    public function test_user_can_delete_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = \App\Models\Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/contacts/' . $contact->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contato deletado com sucesso.',
            ]);

        $this->assertSoftDeleted('contacts', [
            'uuid' => $contact->uuid,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_contacts(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        \App\Models\Contact::factory()->create([
            'company_id' => $company->id,
            'decision_maker' => true,
            'created_by' => $this->user->id,
        ]);

        \App\Models\Contact::factory()->create([
            'company_id' => $company->id,
            'decision_maker' => false,
            'created_by' => $this->user->id,
        ]);

        // Filtro por decision_maker
        $response = $this->getJson('/api/contacts?decision_maker=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por company_id
        $response = $this->getJson('/api/contacts?company_id=' . $company->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_contacts(): void
    {
        Sanctum::actingAs($this->user);

        \App\Models\Contact::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@empresa.com',
            'phone' => '(11) 98765-4321',
            'job_title' => 'Gerente de TI',
            'created_by' => $this->user->id,
        ]);

        \App\Models\Contact::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@empresa.com',
            'phone' => '(21) 98765-4321',
            'job_title' => 'Diretora de Vendas',
            'created_by' => $this->user->id,
        ]);

        // Busca por nome
        $response = $this->getJson('/api/contacts?search=João');

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
        $response = $this->getJson('/api/contacts?search=maria@empresa');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'email' => 'maria@empresa.com',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_contacts_without_authentication(): void
    {
        $response = $this->getJson('/api/contacts');

        $response->assertStatus(401);
    }
}
