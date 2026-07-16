<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->company(),
            'trade_name' => fake()->company(),
            'document' => fake()->unique()->cnpj(),
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
            'state' => fake()->state(),
            'country' => fake()->country(),
            'created_by' => User::factory(),
        ];
    }
}
