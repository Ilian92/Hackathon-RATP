<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\Bus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ComplaintStatus::cases()),
            'incident_time' => fake()->dateTimeBetween('-6 months', 'now'),
            'bus_id' => Bus::factory(),
            'complaint_type_id' => ComplaintType::factory(),
            'user_id' => User::factory()->chauffeur(),
            'client_id' => Client::factory(),
        ];
    }

    public function severe(): static
    {
        return $this->state(fn (array $attributes) => []);
    }
}
