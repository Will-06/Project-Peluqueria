<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HaircutImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'haircut_id',
        'image_url',
        'order',
    ];

    public function haircut()
    {
        return $this->belongsTo(Haircut::class);
    }
}