<?php

namespace Database\Factories;

use App\Models\HaircutImage;
use App\Models\Haircut;
use Illuminate\Database\Eloquent\Factories\Factory;

class HaircutImageFactory extends Factory
{
    protected $model = HaircutImage::class;

    public function definition(): array
    {
        return [
            'haircut_id' => Haircut::factory(),
            'image_url'  => $this->faker->imageUrl(640, 480, 'haircut', true),
            'order'      => $this->faker->numberBetween(0, 10),
        ];
    }
}
