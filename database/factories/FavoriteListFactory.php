<?php

namespace Database\Factories;

use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteListFactory extends Factory
{
    protected $model = FavoriteList::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory()->client(),
            'name'       => $this->faker->words(3, true),
            'is_private' => $this->faker->boolean(80),
        ];
    }
}
