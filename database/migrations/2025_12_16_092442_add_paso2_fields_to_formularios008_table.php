<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {

            // ===== Paso 2: Inicio de atención y motivo (Sección 2) =====
            $table->time('hora_inicio_atencion')->nullable()->after('entrega_telefono');
            $table->string('motivo_causa')->nullable()->after('hora_inicio_atencion'); // trauma|clinica|obstetrica|quirurgica|otro
            $table->boolean('notificacion_policia')->default(false)->after('motivo_causa');
            $table->string('otro_motivo_detalle')->nullable()->after('notificacion_policia');
            $table->string('grupo_sanguineo')->nullable()->after('otro_motivo_detalle'); // A+, O-, etc

            // ===== Paso 2: Accidente/Violencia/Intoxicación... (Sección 3) =====
            $table->dateTime('evento_fecha_hora')->nullable()->after('grupo_sanguineo');
            $table->string('evento_lugar')->nullable()->after('evento_fecha_hora');
            $table->string('evento_direccion')->nullable()->after('evento_lugar');

            // Lista de checks (guardamos como JSON array)
            $table->json('evento_tipos')->nullable()->after('evento_direccion');

            $table->boolean('no_aplica_custodia_policial')->default(false)->after('evento_tipos');
            $table->text('evento_observaciones')->nullable()->after('no_aplica_custodia_policial');

            $table->boolean('aliento_etilico')->default(false)->after('evento_observaciones');
            $table->decimal('valor_alcochek', 6, 2)->nullable()->after('aliento_etilico');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'hora_inicio_atencion',
                'motivo_causa',
                'notificacion_policia',
                'otro_motivo_detalle',
                'grupo_sanguineo',
                'evento_fecha_hora',
                'evento_lugar',
                'evento_direccion',
                'evento_tipos',
                'no_aplica_custodia_policial',
                'evento_observaciones',
                'aliento_etilico',
                'valor_alcochek',
            ]);
        });
    }
};
