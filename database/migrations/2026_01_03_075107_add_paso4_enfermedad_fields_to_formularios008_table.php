<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->boolean('no_aplica_enfermedad_actual')->default(false)->after('valor_alcochek');

            // Radios
            $table->string('via_aerea')->nullable()->after('no_aplica_enfermedad_actual'); // libre | obstruida
            $table->string('condicion')->nullable()->after('via_aerea'); // estable | inestable

            // Texto grande
            $table->longText('enfermedad_actual_revision')->nullable()->after('condicion');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'no_aplica_enfermedad_actual',
                'via_aerea',
                'condicion',
                'enfermedad_actual_revision',
            ]);
        });
    }
};