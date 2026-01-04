<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {

            $table->boolean('no_aplica_obstetrica')->default(false)->after('valor_alcochek');

            $table->unsignedTinyInteger('obst_gestas')->nullable()->after('no_aplica_obstetrica');
            $table->unsignedTinyInteger('obst_partos')->nullable()->after('obst_gestas');
            $table->unsignedTinyInteger('obst_abortos')->nullable()->after('obst_partos');
            $table->unsignedTinyInteger('obst_cesareas')->nullable()->after('obst_abortos');

            $table->date('obst_fum')->nullable()->after('obst_cesareas'); // Fecha última menstruación
            $table->unsignedTinyInteger('obst_semanas_gestacion')->nullable()->after('obst_fum');

            $table->string('obst_movimiento_fetal', 20)->nullable()->after('obst_semanas_gestacion'); // presente|ausente|no_eval

            $table->unsignedSmallInteger('obst_frecuencia_fetal')->nullable()->after('obst_movimiento_fetal');
            $table->decimal('obst_altura_uterina', 5, 2)->nullable()->after('obst_frecuencia_fetal');

            $table->boolean('obst_membranas_rotas')->default(false)->after('obst_altura_uterina');
            $table->string('obst_tiempo_membranas_rotas')->nullable()->after('obst_membranas_rotas'); // texto libre: "2h", "desde ayer", etc.

            $table->string('obst_presentacion')->nullable()->after('obst_tiempo_membranas_rotas');

            $table->decimal('obst_dilatacion_cm', 4, 1)->nullable()->after('obst_presentacion');
            $table->unsignedTinyInteger('obst_borramiento_pct')->nullable()->after('obst_dilatacion_cm');

            $table->string('obst_plano')->nullable()->after('obst_borramiento_pct');
            $table->string('obst_pelvis_util', 20)->nullable()->after('obst_plano'); // si|no|no_eval

            $table->boolean('obst_sangrado_vaginal')->default(false)->after('obst_pelvis_util');
            $table->boolean('obst_contracciones')->default(false)->after('obst_sangrado_vaginal');

            $table->longText('obst_texto')->nullable()->after('obst_contracciones'); // “Describir abajo…”
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'no_aplica_obstetrica',
                'obst_gestas','obst_partos','obst_abortos','obst_cesareas',
                'obst_fum','obst_semanas_gestacion','obst_movimiento_fetal',
                'obst_frecuencia_fetal','obst_altura_uterina',
                'obst_membranas_rotas','obst_tiempo_membranas_rotas',
                'obst_presentacion','obst_dilatacion_cm','obst_borramiento_pct',
                'obst_plano','obst_pelvis_util',
                'obst_sangrado_vaginal','obst_contracciones',
                'obst_texto',
            ]);
        });
    }
};
