<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'source' => fake()->randomElement([
                'website',
                'referral',
                'social_media',
                'email_campaign',
                'cold_call',
                'event',
                'partner',
                'other',
            ]),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost']),
            'score' => fake()->numberBetween(0, 100),
            'temperature' => fake()->randomElement(['cold', 'warm', 'hot']),
            'assigned_to' => User::factory(),
            'observations' => fake()->optional()->paragraph(),
        ];
    }
}
