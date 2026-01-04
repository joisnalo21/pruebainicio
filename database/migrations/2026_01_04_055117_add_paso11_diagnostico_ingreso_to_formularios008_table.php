<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            // Guardamos 3 filas como JSON
            // [{n:1, dx:"...", cie:"...", tipo:"pre|def"}, ...]
            $table->json('diagnosticos_ingreso')->nullable()->after('examenes_comentarios');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['diagnosticos_ingreso']);
        });
    }
};
