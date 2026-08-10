<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Charge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Charge>
 */
class ChargeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'apartment_id' => Apartment::factory(),
            'charged_at' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'kwh' => fake()->randomFloat(3, 3, 60),
            'notes' => null,
        ];
    }

    /**
     * Record the charge on a specific date.
     */
    public function on(string $date): static
    {
        return $this->state(['charged_at' => $date]);
    }
}
