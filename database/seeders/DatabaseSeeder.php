<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Ruan Higor Silva',
            'email' => 'ruanhigor123@gmail.com',
            'password'=> bcrypt('ajax1233253'),
            'role' => 'admin',
        ]);
    }
}
