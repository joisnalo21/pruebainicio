<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->string('alta_destino')->nullable()->after('plan_tratamiento');

            $table->string('alta_servicio_referencia')->nullable()->after('alta_destino');
            $table->string('alta_establecimiento_referencia')->nullable()->after('alta_servicio_referencia');

            $table->string('alta_resultado')->nullable()->after('alta_establecimiento_referencia'); // vivo | muerto_emergencia
            $table->string('alta_condicion')->nullable()->after('alta_resultado'); // estable | inestable
            $table->string('alta_causa')->nullable()->after('alta_condicion');

            $table->unsignedTinyInteger('alta_dias_incapacidad')->nullable()->after('alta_causa');

            $table->date('alta_fecha_control')->nullable()->after('alta_dias_incapacidad');
            $table->time('alta_hora_finalizacion')->nullable()->after('alta_fecha_control');
            $table->string('alta_profesional_codigo')->nullable()->after('alta_hora_finalizacion');

            $table->unsignedInteger('alta_numero_hoja')->nullable()->after('alta_profesional_codigo');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'alta_destino',
                'alta_servicio_referencia',
                'alta_establecimiento_referencia',
                'alta_resultado',
                'alta_condicion',
                'alta_causa',
                'alta_dias_incapacidad',
                'alta_fecha_control',
                'alta_hora_finalizacion',
                'alta_profesional_codigo',
                'alta_numero_hoja',
            ]);
        });
    }
};
