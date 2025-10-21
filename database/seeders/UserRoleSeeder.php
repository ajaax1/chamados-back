<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        // Create Support user
        \App\Models\User::create([
            'name' => 'Support Agent',
            'email' => 'support@example.com',
            'password' => bcrypt('password123'),
            'role' => 'support'
        ]);

        // Create Assistant user
        \App\Models\User::create([
            'name' => 'Assistant',
            'email' => 'assistant@example.com',
            'password' => bcrypt('password123'),
            'role' => 'assistant'
        ]);

        $this->command->info('Users with different roles created successfully!');
        $this->command->info('Admin: admin@example.com / password123');
        $this->command->info('Support: support@example.com / password123');
        $this->command->info('Assistant: assistant@example.com / password123');
    }
}
