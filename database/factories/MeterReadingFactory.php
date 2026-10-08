<?php

namespace Database\Factories;

use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeterReading>
 */
class MeterReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'read_at' => fake()->unique()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'reading' => fake()->randomFloat(3, 1000, 20000),
            'notes' => null,
        ];
    }

    /**
     * Take the reading on a specific date.
     */
    public function on(string $date): static
    {
        return $this->state(['read_at' => $date]);
    }
}
