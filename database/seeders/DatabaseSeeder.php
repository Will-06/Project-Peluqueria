<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentMessage;
use App\Models\FavoriteList;
use App\Models\FavoriteListItem;
use App\Models\Haircut;
use App\Models\HaircutImage;
use App\Models\Like;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as FakerFactory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create(); // ✅ Creamos Faker

         // Crear roles si no existen
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'client']);
        
        // Crear usuario administrador principal
        $mainAdmin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@barbershop.com',
            'role' => 'admin',
        ]);
        $mainAdmin->assignRole('admin');

        // Crear más usuarios administradores
        $adminUsers = User::factory(2)->admin()->create();
        foreach ($adminUsers as $admin) {
            $admin->assignRole('admin');
        }

        // Crear usuarios clientes
        $clientUsers = User::factory(10)->client()->create();
        foreach ($clientUsers as $client) {
            $client->assignRole('client');
        }

        // Crear tags
        $tags = [
            'Hombres', 'Mujeres', 'Niños', 'Fade', 'Low Fade', 'High Fade',
            'Taper', 'Buzz Cut', 'Undercut', 'Pompadour', 'Quiff', 'Crew Cut',
            'French Crop', 'Side Part', 'Slick Back', 'Textured', 'Layered', 'Modern'
        ];

        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }

        // Crear cortes de pelo
        $haircuts = Haircut::factory(25)->published()->create([
            'admin_id' => $mainAdmin->id,
        ]);

        // Asignar tags aleatorios a los cortes
        $tagIds = Tag::pluck('id')->toArray();
        foreach ($haircuts as $haircut) {
            $haircut->tags()->attach(
                $this->getRandomElements($tagIds, rand(2, 5))
            );

            // Agregar imágenes adicionales a algunos cortes
            if (rand(0, 1)) {
                HaircutImage::factory(rand(1, 3))->create([
                    'haircut_id' => $haircut->id,
                ]);
            }
        }

        // Crear likes
        foreach ($clientUsers as $user) {
            $haircutsToLike = $haircuts->random(rand(5, 15));
            foreach ($haircutsToLike as $haircut) {
                Like::create([
                    'user_id' => $user->id,
                    'haircut_id' => $haircut->id,
                    'type' => $faker->randomElement(['like', 'love']),
                ]);
                
                // Actualizar contador
                $haircut->increment('like_count');
            }
        }

        // Crear listas de favoritos
        foreach ($clientUsers as $user) {
            $favoriteLists = FavoriteList::factory(rand(1, 3))->create([
                'user_id' => $user->id,
            ]);

            foreach ($favoriteLists as $list) {
                $haircutsToAdd = $haircuts->random(rand(3, 8));
                foreach ($haircutsToAdd as $haircut) {
                    FavoriteListItem::create([
                        'favorite_list_id' => $list->id,
                        'haircut_id' => $haircut->id,
                    ]);
                    
                    // Actualizar contador
                    $haircut->increment('favorite_count');
                }
            }
        }

        // Crear reseñas
        foreach ($clientUsers as $user) {
            $haircutsToReview = $haircuts->random(rand(2, 6));
            foreach ($haircutsToReview as $haircut) {
                Review::create([
                    'user_id' => $user->id,
                    'haircut_id' => $haircut->id,
                    'rating' => rand(3, 5),
                    'comment' => $faker->paragraph(),
                ]);
            }
        }

        // Crear citas
        foreach ($clientUsers as $user) {
            $appointments = Appointment::factory(rand(1, 4))->create([
                'user_id' => $user->id,
                'haircut_id' => $haircuts->random()->id,
            ]);

            // Crear mensajes para las citas
            foreach ($appointments as $appointment) {
                AppointmentMessage::factory(rand(1, 3))->create([
                    'appointment_id' => $appointment->id,
                    'author_id' => $faker->randomElement([$user->id, $mainAdmin->id]),
                ]);
            }
        }

        // Crear horarios
        Schedule::create([
            'admin_id' => $mainAdmin->id,
            'image_url' => $faker->imageUrl(800, 600, 'schedule'),
            'is_active' => true,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('👤 Admin user: admin@barbershop.com / password');
        $this->command->info('🔑 Client users use: email from factory / password');
    }

    private function getRandomElements(array $array, int $count): array
    {
        $keys = array_rand($array, $count);
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        return array_intersect_key($array, array_flip($keys));
    }
}
