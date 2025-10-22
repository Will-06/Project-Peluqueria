<?php

namespace Database\Factories;

use App\Models\FavoriteListItem;
use App\Models\FavoriteList;
use App\Models\Haircut;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteListItemFactory extends Factory
{
    protected $model = FavoriteListItem::class;

    public function definition(): array
    {
        return [
            'favorite_list_id' => FavoriteList::factory(),
            'haircut_id'       => Haircut::factory(),
        ];
    }
}
