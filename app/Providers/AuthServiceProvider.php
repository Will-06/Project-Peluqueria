<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\FavoriteList;
use App\Models\Haircut;
use App\Models\Notification;
use App\Models\Review;
use App\Policies\AppointmentPolicy;
use App\Policies\FavoriteListPolicy;
use App\Policies\HaircutPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Haircut::class => HaircutPolicy::class,
        Review::class => ReviewPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        FavoriteList::class => FavoriteListPolicy::class,
        Notification::class => NotificationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para administradores
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Gate para clientes
        Gate::define('client', function ($user) {
            return $user->isClient();
        });

        // Gate para gestión de horarios
        Gate::define('manage-schedules', function ($user) {
            return $user->isAdmin();
        });

        // Gate para gestión de notificaciones
        Gate::define('manage-notifications', function ($user) {
            return $user->isAdmin();
        });

        // Gate por defecto para admin
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });
    }
}