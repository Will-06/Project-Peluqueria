<?php

namespace Database\Factories;

use App\Models\Haircut;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->client(),
            'haircut_id' => Haircut::factory(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
        ]);
    }
}