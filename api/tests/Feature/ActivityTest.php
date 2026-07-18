<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Teste de listagem de atividades.
     */
    public function test_user_can_list_activities(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/activities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'deal_id',
                            'company_id',
                            'contact_id',
                            'user_id',
                            'type',
                            'title',
                            'description',
                            'starts_at',
                            'ends_at',
                            'completed_at',
                            'priority',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de atividade.
     */
    public function test_user_can_create_activity(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create();

        $contact = \App\Models\Contact::factory()->create([
            'company_id' => $company->id,
        ]);

        $deal = \App\Models\Deal::factory()->create([
            'company_id' => $company->id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->postJson('/api/activities', [
            'deal_id' => $deal->id,
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'user_id' => $this->user->id,
            'type' => 'call',
            'title' => 'Ligação de acompanhamento',
            'description' => 'Ligar para o cliente para verificar andamento',
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-01 11:00:00',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Atividade criada com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'deal_id',
                    'company_id',
                    'contact_id',
                    'user_id',
                    'type',
                    'title',
                    'description',
                    'starts_at',
                    'ends_at',
                    'priority',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('activities', [
            'title' => 'Ligação de acompanhamento',
            'type' => 'call',
        ]);
    }

    /**
     * Teste de criação de atividade com dados inválidos.
     */
    public function test_user_cannot_create_activity_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/activities', [
            'type' => '',
            'title' => '',
            'priority' => 'invalid',
            'status' => 'invalid',
            'ends_at' => '2026-07-01 10:00:00',
            'starts_at' => '2026-08-01 10:00:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'title', 'priority', 'status', 'ends_at']);
    }

    /**
     * Teste de visualização de atividade.
     */
    public function test_user_can_view_activity(): void
    {
        Sanctum::actingAs($this->user);

        $activity = \App\Models\Activity::factory()->create();

        $response = $this->getJson('/api/activities/' . $activity->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $activity->id,
                    'title' => $activity->title,
                ],
            ]);
    }

    /**
     * Teste de atualização de atividade.
     */
    public function test_user_can_update_activity(): void
    {
        Sanctum::actingAs($this->user);

        $activity = \App\Models\Activity::factory()->create();

        $response = $this->putJson('/api/activities/' . $activity->id, [
            'title' => 'Atividade Atualizada',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Atividade atualizada com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Atividade Atualizada',
                    'status' => 'in_progress',
                    'priority' => 'high',
                ],
            ]);

        $this->assertDatabaseHas('activities', [
            'title' => 'Atividade Atualizada',
            'status' => 'in_progress',
        ]);
    }

    /**
     * Teste de atualização de atividade com dados inválidos.
     */
    public function test_user_cannot_update_activity_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $activity = \App\Models\Activity::factory()->create();

        $response = $this->putJson('/api/activities/' . $activity->id, [
            'title' => '',
            'type' => '',
            'priority' => 'invalid',
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'type', 'priority', 'status']);
    }

    /**
     * Teste de exclusão de atividade.
     */
    public function test_user_can_delete_activity(): void
    {
        Sanctum::actingAs($this->user);

        $activity = \App\Models\Activity::factory()->create();

        $response = $this->deleteJson('/api/activities/' . $activity->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Atividade deletada com sucesso.',
            ]);

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_activities(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create();

        \App\Models\Activity::factory()->create([
            'company_id' => $company->id,
            'type' => 'call',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        \App\Models\Activity::factory()->create([
            'company_id' => $company->id,
            'type' => 'meeting',
            'priority' => 'low',
            'status' => 'completed',
        ]);

        // Filtro por tipo
        $response = $this->getJson('/api/activities?type=call');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por status
        $response = $this->getJson('/api/activities?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por prioridade
        $response = $this->getJson('/api/activities?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_activities(): void
    {
        Sanctum::actingAs($this->user);

        \App\Models\Activity::factory()->create([
            'title' => 'Reunião de planejamento',
            'description' => 'Discutir metas do trimestre',
            'type' => 'meeting',
        ]);

        \App\Models\Activity::factory()->create([
            'title' => 'Ligação de vendas',
            'description' => 'Apresentar proposta comercial',
            'type' => 'call',
        ]);

        // Busca por título
        $response = $this->getJson('/api/activities?search=Reunião');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'title' => 'Reunião de planejamento',
                        ],
                    ],
                ],
            ]);

        // Busca por descrição
        $response = $this->getJson('/api/activities?search=proposta');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'description' => 'Apresentar proposta comercial',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_activities_without_authentication(): void
    {
        $response = $this->getJson('/api/activities');

        $response->assertStatus(401);
    }
}
