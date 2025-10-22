<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Haircut extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'featured_image_url',
        'is_published',
        'like_count',
        'favorite_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // Relaciones
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function images()
    {
        return $this->hasMany(HaircutImage::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'haircut_tag');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function favoriteListItems()
    {
        return $this->hasMany(FavoriteListItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('like_count', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->whereHas('tags', function ($q) {
            $q->where('name', 'featured');
        });
    }

    // Métodos de utilidad
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function incrementLikeCount()
    {
        $this->increment('like_count');
    }

    public function decrementLikeCount()
    {
        $this->decrement('like_count');
    }
}