<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HaircutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'featured_image_url' => $this->faker->imageUrl(400, 400, 'haircut'),
            'is_published' => $this->faker->boolean(80),
            'like_count' => $this->faker->numberBetween(0, 100),
            'favorite_count' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'is_published' => true,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state([
            'is_published' => false,
        ]);
    }
}