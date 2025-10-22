#!/bin/bash

echo "🛠️ Generando modelos y migraciones para el Proyecto Peluquería en el orden correcto..."
echo "⏳ Este proceso tomará algunos segundos debido a las pausas para mantener el orden..."

# Paso 1: Users (base de todo)
php artisan make:model User -a
sleep 1

# Paso 2: Tags (no depende de nadie)
php artisan make:model Tag -a
sleep 1

# Paso 3: Haircuts (depende de users -> admin_id)
php artisan make:model Haircut -a
sleep 1

# Paso 4: Haircut Images (depende de haircuts)
php artisan make:model HaircutImage --migration
sleep 1

# Paso 5: Haircut Tags (pivote: haircuts + tags)
php artisan make:model HaircutTag --migration
sleep 1

# Paso 6: Likes (depende de users + haircuts)
php artisan make:model Like --migration
sleep 1

# Paso 7: Favorite Lists (depende de users)
php artisan make:model FavoriteList -a
sleep 1

# Paso 8: Favorite List Items (depende de favorite_lists + haircuts)
php artisan make:model FavoriteListItem --migration
sleep 1

# Paso 9: Reviews (depende de users + haircuts)
php artisan make:model Review -a
sleep 1

# Paso 10: Appointments (depende de users + haircuts)
php artisan make:model Appointment -a
sleep 1

# Paso 11: Appointment Messages (depende de appointments + users)
php artisan make:model AppointmentMessage --migration
sleep 1

# Paso 12: Schedules (depende de users -> admin_id)
php artisan make:model Schedule -a
sleep 1

# Paso 13: Notifications (depende de users)
php artisan make:model Notification -a
sleep 1

echo "✅ Modelos y migraciones generados en el orden correcto."
echo "📁 Revisa 'database/migrations' para personalizar las migraciones si es necesario."
echo "🚀 Cuando estés listo, ejecuta: php artisan migrate"
