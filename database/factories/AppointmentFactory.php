<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Haircut;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory()->client(),
            'haircut_id' => Haircut::factory(),
            'status'     => $this->faker->randomElement(['pending','approved','rejected','cancelled']),
        ];
    }
}
