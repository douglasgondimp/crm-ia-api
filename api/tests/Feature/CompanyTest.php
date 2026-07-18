<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Teste de listagem de empresas.
     */
    public function test_user_can_list_companies(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'uuid',
                            'name',
                            'trade_name',
                            'document',
                            'email',
                            'phone',
                            'website',
                            'segment',
                            'employees',
                            'annual_revenue',
                            'description',
                            'zipcode',
                            'address',
                            'number',
                            'district',
                            'city',
                            'state',
                            'country',
                            'created_by',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de criação de empresa.
     */
    public function test_user_can_create_company(): void
    {
        Sanctum::actingAs($this->user);

        $companyName = fake()->company();
        $companyDocument = fake()->unique()->numerify('##############');

        $response = $this->postJson('/api/companies', [
            'name' => $companyName,
            'trade_name' => fake()->company(),
            'document' => $companyDocument,
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'segment' => fake()->randomElement([
                'Tecnologia',
                'Saúde',
                'Educação',
                'Finanças',
                'Varejo',
                'Indústria',
                'Serviços',
                'Construção Civil',
                'Agronegócio',
                'Logística',
            ]),
            'employees' => fake()->numberBetween(1, 10000),
            'annual_revenue' => fake()->randomFloat(2, 10000, 100000000),
            'description' => fake()->paragraph(),
            'zipcode' => fake()->postcode(),
            'address' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'district' => fake()->word(),
            'city' => fake()->city(),
            'state' => 'SP',
            'country' => 'BR',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Empresa criada com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'name',
                    'trade_name',
                    'document',
                    'email',
                    'phone',
                    'website',
                    'segment',
                    'employees',
                    'annual_revenue',
                    'description',
                    'zipcode',
                    'address',
                    'number',
                    'district',
                    'city',
                    'state',
                    'country',
                ],
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => $companyName,
            'document' => $companyDocument,
        ]);
    }

    /**
     * Teste de criação de empresa com dados inválidos.
     */
    public function test_user_cannot_create_company_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/companies', [
            'name' => '',
            'email' => 'invalid-email',
            'website' => 'invalid-url',
            'employees' => -10,
            'annual_revenue' => -100,
            'state' => 'SPSP',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'website', 'employees', 'annual_revenue', 'state']);
    }

    /**
     * Teste de visualização de empresa.
     */
    public function test_user_can_view_company(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/companies/' . $company->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $company->uuid,
                    'name' => $company->name,
                ],
            ]);
    }

    /**
     * Teste de atualização de empresa.
     */
    public function test_user_can_update_company(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $companyName = fake()->company();

        $response = $this->putJson('/api/companies/' . $company->uuid, [
            'name' => $companyName,
            'segment' => 'Consultoria',
            'employees' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Empresa atualizada com sucesso.',
            ])
            ->assertJson([
                'data' => [
                    'name' => $companyName,
                    'segment' => 'Consultoria',
                    'employees' => 100,
                ],
            ]);

        $this->assertDatabaseHas('companies', [
            'uuid' => $company->uuid,
            'name' => $companyName,
            'segment' => 'Consultoria',
            'employees' => 100,
        ]);
    }

    /**
     * Teste de atualização de empresa com dados inválidos.
     */
    public function test_user_cannot_update_company_with_invalid_data(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson('/api/companies/' . $company->uuid, [
            'name' => '',
            'email' => 'invalid-email',
            'employees' => -50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'employees']);
    }

    /**
     * Teste de exclusão de empresa.
     */
    public function test_user_can_delete_company(): void
    {
        Sanctum::actingAs($this->user);

        $company = \App\Models\Company::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/companies/' . $company->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Empresa deletada com sucesso.',
            ]);

        $this->assertSoftDeleted('companies', [
            'uuid' => $company->uuid,
        ]);
    }

    /**
     * Teste de listagem com filtros.
     */
    public function test_user_can_filter_companies(): void
    {
        Sanctum::actingAs($this->user);

        \App\Models\Company::factory()->create([
            'segment' => 'Tecnologia',
            'city' => 'São Paulo',
            'state' => 'SP',
            'created_by' => $this->user->id,
        ]);

        \App\Models\Company::factory()->create([
            'segment' => 'Consultoria',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'created_by' => $this->user->id,
        ]);

        // Filtro por segmento
        $response = $this->getJson('/api/companies?segment=Tecnologia');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por cidade
        $response = $this->getJson('/api/companies?city=São Paulo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Filtro por estado
        $response = $this->getJson('/api/companies?state=RJ');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    /**
     * Teste de busca por texto.
     */
    public function test_user_can_search_companies(): void
    {
        Sanctum::actingAs($this->user);

        \App\Models\Company::factory()->create([
            'name' => 'Empresa ABC',
            'trade_name' => 'ABC',
            'document' => '12345678901234',
            'email' => 'contato@abc.com',
            'created_by' => $this->user->id,
        ]);

        \App\Models\Company::factory()->create([
            'name' => 'Empresa XYZ',
            'trade_name' => 'XYZ',
            'document' => '98765432109876',
            'email' => 'contato@xyz.com',
            'created_by' => $this->user->id,
        ]);

        // Busca por nome
        $response = $this->getJson('/api/companies?search=ABC');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'name' => 'Empresa ABC',
                        ],
                    ],
                ],
            ]);

        // Busca por documento
        $response = $this->getJson('/api/companies?search=123456');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJson([
                'data' => [
                    'data' => [
                        0 => [
                            'document' => '12345678901234',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Teste de acesso sem autenticação.
     */
    public function test_user_cannot_access_companies_without_authentication(): void
    {
        $response = $this->getJson('/api/companies');

        $response->assertStatus(401);
    }
}
