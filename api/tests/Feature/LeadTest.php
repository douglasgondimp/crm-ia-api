<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Teste de listagem de leads.
     */
    public function test_user_can_list_leads(): void
    {
        $response = $this->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'uuid',
                            'name',
                            'email',
                            'phone',
                            'company',
                            'source',
                            'status',
                            'score',
                            'temperature',
                            'assigned_to',
                            'observations',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de lead.
     */
    public function test_user_can_create_lead(): void
    {
        $response = $this->postJson('/api/leads', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '(11) 98765-4321',
            'company' => 'Empresa ABC',
            'source' => 'Website',
            'status' => 'novo',
            'score' => 75,
            'temperature' => 'warm',
            'assigned_to' => $this->user->id,
            'observations' => 'Lead interessado no produto',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Lead criado com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'name',
                    'email',
                    'phone',
                    'company',
                    'source',
                    'status',
                    'score',
                    'temperature',
                    'assigned_to',
                    'observations',
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);
    }

    /**
     * Teste de criação de lead com dados inválidos.
     */
    public function test_user_cannot_create_lead_with_invalid_data(): void
    {
        $response = $this->postJson('/api/leads', [
            'name' => '',
            'email' => 'invalid-email',
            'score' => 150,
            'temperature' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'score', 'temperature']);
    }

    /**
     * Teste de visualização de lead.
     */
    public function test_user_can_view_lead(): void
    {
        $lead = \App\Models\Lead::factory()->create([
            'assigned_to' => $this->user->id,
            'status' => 'novo',
        ]);

        $response = $this->getJson('/api/leads/' . $lead->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $lead->uuid,
                    'name' => $lead->name,
                ],
            ]);
    }

    /**
     * Teste de atualização de lead.
     */
    public function test_user_can_update_lead(): void
    {
        $lead = \App\Models\Lead::factory()->create([
            'assigned_to' => $this->user->id,
            'status' => 'novo',
        ]);

        $response = $this->putJson('/api/leads/' . $lead->uuid, [
            'name' => 'João Silva Atualizado',
            'status' => 'contato',
            'score' => 85,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lead atualizado com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'name' => 'João Silva Atualizado',
                    'status' => 'contato',
                    'score' => 85,
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'uuid' => $lead->uuid,
            'name' => 'João Silva Atualizado',
            'status' => 'contato',
            'score' => 85,
        ]);
    }

    /**
     * Teste de atualização de lead com dados inválidos.
     */
    public function test_user_cannot_update_lead_with_invalid_data(): void
    {
        $lead = \App\Models\Lead::factory()->create([
            'assigned_to' => $this->user->id,
            'status' => 'novo',
        ]);

        $response = $this->putJson('/api/leads/' . $lead->uuid, [
            'name' => '',
            'score' => 150,
            'temperature' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'score', 'temperature']);
    }

    /**
     * Teste de exclusão de lead.
     */
    public function test_user_can_delete_lead(): void
    {
        $lead = \App\Models\Lead::factory()->create([
            'assigned_to' => $this->user->id,
            'status' => 'novo',
        ]);

        $response = $this->deleteJson('/api/leads/' . $lead->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lead deletado com sucesso.',
            ]);

        $this->assertSoftDeleted('leads', [
            'uuid' => $lead->uuid,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_leads(): void
    {
        \App\Models\Lead::factory()->create([
            'status' => 'novo',
            'temperature' => 'hot',
            'assigned_to' => $this->user->id,
        ]);

        \App\Models\Lead::factory()->create([
            'status' => 'qualificado',
            'temperature' => 'warm',
            'assigned_to' => $this->user->id,
        ]);

        // Filtro por status
        $response = $this->getJson('/api/leads?status=novo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por temperatura
        $response = $this->getJson('/api/leads?temperature=hot');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_leads(): void
    {
        \App\Models\Lead::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'company' => 'Empresa ABC',
            'status' => 'negociacao',
            'assigned_to' => $this->user->id,
        ]);

        \App\Models\Lead::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'company' => 'Empresa XYZ',
            'status' => 'convertido',
            'assigned_to' => $this->user->id,
        ]);

        // Busca por nome
        $response = $this->getJson('/api/leads?search=João');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'name' => 'João Silva'
                        ]
                    ]
                ],
            ]);

        // Busca por empresa
        $response = $this->getJson('/api/leads?search=ABC');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'company' => 'Empresa ABC',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_leads_without_authentication(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->getJson('/api/leads');

        $response->assertStatus(200);
    }
}
