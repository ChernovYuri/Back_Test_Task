<?php

namespace Database\Seeders;

use App\Models\History;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Создание пользователей
        User::factory()->count(10)->create();

        // Создание истории
        foreach (User::all() as $user) {
            History::create([
                'model_id' => $user->id,
                'model_name' => User::class,
                'before' => json_encode([
                    'id' => $user->id,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'middle_name' => $user->middle_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at,
                ]),
                'after' => json_encode([
                    'id' => $user->id,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'middle_name' => $user->middle_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at,
                ]),
                'action' => 'created',
            ]);
        }
    }
}
