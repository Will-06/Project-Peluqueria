<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(FavoriteListItem::class);
    }

    public function haircuts()
    {
        return $this->belongsToMany(Haircut::class, 'favorite_list_items');
    }
}