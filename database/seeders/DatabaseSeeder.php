<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\FavoriteList;
use App\Models\Haircut;
use App\Models\Like;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $clientRole = Role::create(['name' => 'client']);

        // Crear usuarios administradores
        $adminUsers = User::factory(3)->admin()->create();
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
            'French Crop', 'Side Part', 'Slick Back', 'Textured', 'Layered'
        ];

        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }

        // Crear cortes de pelo
        $haircuts = Haircut::factory(20)->published()->create();

        // Asignar tags aleatorios a los cortes
        $tagIds = Tag::pluck('id')->toArray();
        foreach ($haircuts as $haircut) {
            $haircut->tags()->attach(
                $this->faker->randomElements($tagIds, rand(2, 5))
            );
        }

        // Crear likes
        foreach ($clientUsers as $user) {
            $haircutsToLike = $haircuts->random(rand(5, 15));
            foreach ($haircutsToLike as $haircut) {
                Like::create([
                    'user_id' => $user->id,
                    'haircut_id' => $haircut->id,
                    'type' => $this->faker->randomElement(['like', 'love']),
                ]);
            }
        }

        // Crear listas de favoritos
        foreach ($clientUsers as $user) {
            $favoriteLists = FavoriteList::factory(rand(1, 3))->create([
                'user_id' => $user->id,
                'name' => $this->faker->words(2, true), // ✅ AGREGADO
            ]);

            foreach ($favoriteLists as $list) {
                $haircutsToAdd = $haircuts->random(rand(3, 8));
                foreach ($haircutsToAdd as $haircut) {
                    $list->haircuts()->attach($haircut->id);
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
                    'comment' => $this->faker->paragraph(),
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
                \App\Models\AppointmentMessage::factory(rand(1, 3))->create([
                    'appointment_id' => $appointment->id,
                    'author_id' => $this->faker->randomElement([$user->id, $adminUsers->random()->id]),
                ]);
            }
        }
    }
}
