<?php

namespace Database\Factories;

use App\Models\AppointmentMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentMessageFactory extends Factory
{
    protected $model = AppointmentMessage::class;

    public function definition(): array
    {
        return [
            'appointment_id' => null,
            'author_id'      => null,
            'message'        => $this->faker->paragraph(),
        ];
    }
}
