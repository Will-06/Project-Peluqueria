<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones
    public function haircuts()
    {
        return $this->hasMany(Haircut::class, 'admin_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function favoriteLists()
    {
        return $this->hasMany(FavoriteList::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function appointmentMessages()
    {
        return $this->hasMany(AppointmentMessage::class, 'author_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'admin_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes
    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Métodos de utilidad
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function isClient(): bool
    {
        return $this->role === 'client' || $this->hasRole('client');
    }

    // Asignar rol basado en el campo role
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role === 'admin' && !$user->hasRole('admin')) {
                $user->assignRole('admin');
            } elseif ($user->role === 'client' && !$user->hasRole('client')) {
                $user->assignRole('client');
            }
        });

        static::updated(function ($user) {
            if ($user->isDirty('role')) {
                $user->syncRoles([$user->role]);
            }
        });
    }
}