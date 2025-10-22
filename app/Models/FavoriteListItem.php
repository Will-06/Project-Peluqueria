<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'favorite_list_id',
        'haircut_id',
    ];

    public function favoriteList()
    {
        return $this->belongsTo(FavoriteList::class);
    }

    public function haircut()
    {
        return $this->belongsTo(Haircut::class);
    }
}