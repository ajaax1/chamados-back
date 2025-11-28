<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, updated, deleted, viewed, assigned, etc.
            $table->string('model_type'); // Ticket, TicketMessage, TicketAttachment, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable(); // Valores antes da mudança
            $table->json('new_values')->nullable(); // Valores depois da mudança
            $table->text('description')->nullable(); // Descrição da ação
            $table->string('ip_address', 45)->nullable(); // IPv4 ou IPv6
            $table->text('user_agent')->nullable(); // Navegador/dispositivo
            $table->json('metadata')->nullable(); // Dados extras (ex: relacionamentos)
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
