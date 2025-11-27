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
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('tempo_resolucao')->nullable()->after('priority')
                ->comment('Tempo de resolução em minutos. Opcional.');
            $table->timestamp('resolvido_em')->nullable()->after('tempo_resolucao')
                ->comment('Data e horário em que o ticket foi resolvido. Opcional.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['tempo_resolucao', 'resolvido_em']);
        });
    }
};
