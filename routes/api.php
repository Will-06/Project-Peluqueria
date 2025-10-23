<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteListController;
use App\Http\Controllers\HaircutController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::get('/haircuts', [HaircutController::class, 'index']);
Route::get('/haircuts/popular', [HaircutController::class, 'popular']);
Route::get('/haircuts/featured', [HaircutController::class, 'featured']);
Route::get('/haircuts/{haircut}', [HaircutController::class, 'show']);
Route::get('/haircuts/{haircut}/reviews', [ReviewController::class, 'index']);

Route::get('/tags', [TagController::class, 'index']);
Route::get('/schedules', [ScheduleController::class, 'index']);

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware(['auth:sanctum'])->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Haircuts (Admin only)
    Route::post('/haircuts', [HaircutController::class, 'store'])->middleware('can:create,App\Models\Haircut');
    Route::put('/haircuts/{haircut}', [HaircutController::class, 'update'])->middleware('can:update,haircut');
    Route::delete('/haircuts/{haircut}', [HaircutController::class, 'destroy'])->middleware('can:delete,haircut');
    Route::post('/haircuts/{haircut}/publish', [HaircutController::class, 'publish'])->middleware('can:update,haircut');
    Route::post('/haircuts/{haircut}/unpublish', [HaircutController::class, 'unpublish'])->middleware('can:update,haircut');
    Route::post('/haircuts/{haircut}/images', [HaircutController::class, 'addImage'])->middleware('can:update,haircut');
    Route::delete('/haircuts/{haircut}/images/{image}', [HaircutController::class, 'removeImage'])->middleware('can:update,haircut');

    // Reviews
    Route::get('/reviews/user', [ReviewController::class, 'userReviews']);
    Route::post('/reviews', [ReviewController::class, 'store'])->middleware('can:create,App\Models\Review');
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->middleware('can:update,review');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware('can:delete,review');

    // Likes
    Route::post('/likes/toggle', [LikeController::class, 'toggle']);
    Route::get('/likes/user', [LikeController::class, 'userLikes']);
    Route::get('/haircuts/{haircut}/like-status', [LikeController::class, 'checkLike']);

    // Favorite Lists
    Route::get('/favorite-lists', [FavoriteListController::class, 'index']);
    Route::post('/favorite-lists', [FavoriteListController::class, 'store']);
    Route::get('/favorite-lists/{favoriteList}', [FavoriteListController::class, 'show'])->middleware('can:view,favoriteList');
    Route::put('/favorite-lists/{favoriteList}', [FavoriteListController::class, 'update'])->middleware('can:update,favoriteList');
    Route::delete('/favorite-lists/{favoriteList}', [FavoriteListController::class, 'destroy'])->middleware('can:delete,favoriteList');
    Route::post('/favorite-lists/{favoriteList}/haircuts', [FavoriteListController::class, 'addHaircut'])->middleware('can:update,favoriteList');
    Route::delete('/favorite-lists/{favoriteList}/haircuts/{haircutId}', [FavoriteListController::class, 'removeHaircut'])->middleware('can:update,favoriteList');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/user', [AppointmentController::class, 'userAppointments']);
    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('can:create,App\Models\Appointment');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->middleware('can:view,appointment');
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->middleware('can:update,appointment');
    Route::post('/appointments/{appointment}/messages', [AppointmentController::class, 'addMessage'])->middleware('can:addMessage,appointment');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->middleware('can:delete,appointment');

    // Schedules (Admin only)
    Route::post('/schedules', [ScheduleController::class, 'store'])->middleware('can:manage-schedules');
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->middleware('can:manage-schedules');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->middleware('can:manage-schedules');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->middleware('can:update,notification');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->middleware('can:delete,notification');
});

// Health check (pública)
Route::get('/health', [HealthCheckController::class, '__invoke']);

// Rutas de administración (solo admin)
Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
    Route::get('/admin/stats', function (Request $request) {
        $stats = [
            'total_haircuts' => \App\Models\Haircut::count(),
            'published_haircuts' => \App\Models\Haircut::published()->count(),
            'total_users' => \App\Models\User::count(),
            'total_clients' => \App\Models\User::clients()->count(),
            'total_admins' => \App\Models\User::admins()->count(),
            'total_appointments' => \App\Models\Appointment::count(),
            'pending_appointments' => \App\Models\Appointment::pending()->count(),
            'total_reviews' => \App\Models\Review::count(),
            'total_likes' => \App\Models\Like::count(),
        ];

        return response()->json(['data' => $stats]);
    });
});