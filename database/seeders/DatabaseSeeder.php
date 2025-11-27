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
        // Criar usuário principal
        \App\Models\User::factory()->create([
            'name' => 'Ruan Higor Silva',
            'email' => 'ruanhigor123@gmail.com',
            'password'=> bcrypt('ajax1233253'),
            'role' => 'admin',
        ]);

        // Executar seeder de dados simulados para estatísticas
        $this->call([
            StatisticsDataSeeder::class,
        ]);
    }
}
