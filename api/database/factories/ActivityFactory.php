<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deal_id'      => Deal::factory(),
            'company_id'   => Company::factory(),
            'contact_id'   => Contact::factory(),
            'user_id'      => User::factory(),
            'type'         => fake()->randomElement(['call', 'email', 'meeting', 'task', 'note']),
            'title'        => fake()->sentence(),
            'description'  => fake()->paragraph(),
            'starts_at'    => fake()->dateTime(),
            'ends_at'      => fake()->dateTime(),
            'completed_at' => null,
            'priority'     => fake()->randomElement(['low', 'medium', 'high']),
            'status'       => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
        ];
    }
}
