<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar o enum para incluir 'cliente' usando SQL direto (compatível com MySQL)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'support', 'assistant', 'cliente') DEFAULT 'assistant'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o enum original sem 'cliente'
        // Nota: Isso pode falhar se houver usuários com role 'cliente'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'support', 'assistant') DEFAULT 'assistant'");
    }
};
