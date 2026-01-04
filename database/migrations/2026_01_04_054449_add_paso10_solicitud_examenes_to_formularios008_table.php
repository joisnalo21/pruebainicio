<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->boolean('no_aplica_examenes')->default(false)->after('obst_texto');

            // Guardamos como array JSON de números/keys seleccionados
            $table->json('examenes_solicitados')->nullable()->after('no_aplica_examenes');

            // Comentarios/resultados
            $table->longText('examenes_comentarios')->nullable()->after('examenes_solicitados');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'no_aplica_examenes',
                'examenes_solicitados',
                'examenes_comentarios',
            ]);
        });
    }
};
