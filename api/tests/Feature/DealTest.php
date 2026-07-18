<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DealTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Teste de listagem de deals.
     */
    public function test_user_can_list_deals(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/deals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'uuid',
                            'company_id',
                            'contact_id',
                            'pipeline_stage_id',
                            'assigned_to',
                            'title',
                            'description',
                            'value',
                            'expected_close_date',
                            'status',
                            'won_at',
                            'lost_reason',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de deal.
     */
    public function test_user_can_create_deal(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $contact = \App\Models\Contact::factory()->create([
            'company_id' => $company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson('/api/deals', [
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'title' => 'Deal de Venda',
            'description' => 'Venda de produto X',
            'value' => 50000.00,
            'expected_close_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Deal criado com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'company_id',
                    'contact_id',
                    'title',
                    'description',
                    'value',
                    'expected_close_date',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('deals', [
            'title' => 'Deal de Venda',
            'value' => 50000.00,
        ]);
    }

    /**
     * Teste de criação de deal com dados inválidos.
     */
    public function test_user_cannot_create_deal_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/deals', [
            'title' => '',
            'value' => -100,
            'status' => 'invalid',
            'expected_close_date' => 'invalid-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'value', 'status', 'expected_close_date']);
    }

    /**
     * Teste de visualização de deal.
     */
    public function test_user_can_view_deal(): void
    {
        Sanctum::actingAs($this->user);

        $deal = \App\Models\Deal::factory()->create();

        $response = $this->getJson('/api/deals/' . $deal->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $deal->uuid,
                    'title' => $deal->title,
                ],
            ]);
    }

    /**
     * Teste de atualização de deal.
     */
    public function test_user_can_update_deal(): void
    {
        Sanctum::actingAs($this->user);

        $deal = \App\Models\Deal::factory()->create();

        $response = $this->putJson('/api/deals/' . $deal->uuid, [
            'title' => 'Deal Atualizado',
            'value' => 75000.00,
            'status' => 'won',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Deal atualizado com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Deal Atualizado',
                    'value' => 75000.00,
                    'status' => 'won',
                ],
            ]);

        $this->assertDatabaseHas('deals', [
            'uuid' => $deal->uuid,
            'title' => 'Deal Atualizado',
            'value' => 75000.00,
            'status' => 'won',
        ]);
    }

    /**
     * Teste de atualização de deal com dados inválidos.
     */
    public function test_user_cannot_update_deal_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $deal = \App\Models\Deal::factory()->create();

        $response = $this->putJson('/api/deals/' . $deal->uuid, [
            'title' => '',
            'value' => -50,
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'value', 'status']);
    }

    /**
     * Teste de exclusão de deal.
     */
    public function test_user_can_delete_deal(): void
    {
        Sanctum::actingAs($this->user);

        $deal = \App\Models\Deal::factory()->create();

        $response = $this->deleteJson('/api/deals/' . $deal->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Deal deletado com sucesso.',
            ]);

        $this->assertSoftDeleted('deals', [
            'uuid' => $deal->uuid,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_deals(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        \App\Models\Deal::factory()->create([
            'company_id' => $company->id,
            'status' => 'open',
        ]);

        \App\Models\Deal::factory()->create([
            'company_id' => $company->id,
            'status' => 'won',
        ]);

        // Filtro por status
        $response = $this->getJson('/api/deals?status=open');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por company_id
        $response = $this->getJson('/api/deals?company_id=' . $company->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_deals(): void
    {
        Sanctum::actingAs($this->user);

        \App\Models\Deal::factory()->create([
            'title' => 'Venda de Software',
            'description' => 'Licença anual',
        ]);

        \App\Models\Deal::factory()->create([
            'title' => 'Consultoria',
            'description' => 'Serviços de implementação',
        ]);

        // Busca por título
        $response = $this->getJson('/api/deals?search=Software');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'title' => 'Venda de Software',
                        ],
                    ],
                ],
            ]);

        // Busca por descrição
        $response = $this->getJson('/api/deals?search=implementação');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'description' => 'Serviços de implementação',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_deals_without_authentication(): void
    {
        $response = $this->getJson('/api/deals');

        $response->assertStatus(401);
    }
}
