<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->json('examen_fisico_checks')->nullable()->after('valor_alcochek');
            $table->text('examen_fisico_descripcion')->nullable()->after('examen_fisico_checks');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['examen_fisico_checks', 'examen_fisico_descripcion']);
        });
    }
};
