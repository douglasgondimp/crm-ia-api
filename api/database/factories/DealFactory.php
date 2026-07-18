<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
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
            'company_id' => Company::factory(),
            'contact_id' => Contact::factory(),
            'pipeline_stage_id' => null,
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'value' => fake()->randomFloat(2, 1000, 100000),
            'expected_close_date' => fake()->date(),
            'status' => fake()->randomElement(['open', 'won', 'lost']),
            'won_at' => null,
            'lost_reason' => null,
        ];
    }
}
