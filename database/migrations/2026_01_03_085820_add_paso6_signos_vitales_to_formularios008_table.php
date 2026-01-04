<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {

            // Signos vitales
            $table->unsignedSmallInteger('pa_sistolica')->nullable()->after('valor_alcochek');
            $table->unsignedSmallInteger('pa_diastolica')->nullable()->after('pa_sistolica');

            $table->unsignedSmallInteger('frecuencia_cardiaca')->nullable()->after('pa_diastolica');
            $table->unsignedSmallInteger('frecuencia_respiratoria')->nullable()->after('frecuencia_cardiaca');

            $table->decimal('temp_bucal', 4, 1)->nullable()->after('frecuencia_respiratoria'); // 36.5
            $table->decimal('temp_axilar', 4, 1)->nullable()->after('temp_bucal');

            $table->decimal('peso', 6, 2)->nullable()->after('temp_axilar'); // kg
            $table->decimal('talla', 4, 2)->nullable()->after('peso'); // m (1.70)

            $table->unsignedTinyInteger('saturacion_oxigeno')->nullable()->after('talla'); // 0-100
            $table->decimal('tiempo_llenado_capilar', 3, 1)->nullable()->after('saturacion_oxigeno'); // 2.0

            // Glasgow
            $table->unsignedTinyInteger('glasgow_ocular')->nullable()->after('tiempo_llenado_capilar'); // 1-4
            $table->unsignedTinyInteger('glasgow_verbal')->nullable()->after('glasgow_ocular'); // 1-5
            $table->unsignedTinyInteger('glasgow_motora')->nullable()->after('glasgow_verbal'); // 1-6
            $table->unsignedTinyInteger('glasgow_total')->nullable()->after('glasgow_motora'); // 3-15

            // Pupilas (lo guardamos como texto simple)
            $table->string('reaccion_pupila_der', 30)->nullable()->after('glasgow_total');
            $table->string('reaccion_pupila_izq', 30)->nullable()->after('reaccion_pupila_der');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'pa_sistolica','pa_diastolica',
                'frecuencia_cardiaca','frecuencia_respiratoria',
                'temp_bucal','temp_axilar',
                'peso','talla',
                'saturacion_oxigeno','tiempo_llenado_capilar',
                'glasgow_ocular','glasgow_verbal','glasgow_motora','glasgow_total',
                'reaccion_pupila_der','reaccion_pupila_izq',
            ]);
        });
    }
};
