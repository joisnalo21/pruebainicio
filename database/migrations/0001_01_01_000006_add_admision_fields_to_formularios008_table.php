<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            // Encabezado (fijo para el hospital, pero lo guardamos en el formulario)
            $table->string('institucion_sistema')->nullable()->after('paso_actual');
            $table->string('unidad_operativa')->nullable()->after('institucion_sistema');
            $table->string('cod_uo')->nullable()->after('unidad_operativa');

            // Códigos/localización (los dejamos null por ahora si no los tienes)
            $table->string('cod_provincia')->nullable()->after('cod_uo');
            $table->string('cod_canton')->nullable()->after('cod_provincia');
            $table->string('cod_parroquia')->nullable()->after('cod_canton');

            // Historia clínica (aún no existe -> se deja null)
            $table->string('numero_historia_clinica')->nullable()->after('cod_parroquia');

            // Admisión
            $table->dateTime('fecha_admision')->nullable()->after('numero_historia_clinica');
            $table->string('referido_de')->nullable()->after('fecha_admision');

            // En caso necesario avisar a:
            $table->string('avisar_nombre')->nullable()->after('referido_de');
            $table->string('avisar_parentesco')->nullable()->after('avisar_nombre');
            $table->string('avisar_direccion')->nullable()->after('avisar_parentesco');
            $table->string('avisar_telefono')->nullable()->after('avisar_direccion');

            // Llegada / fuente info
            $table->string('forma_llegada')->nullable()->after('avisar_telefono'); // ambulatorio|ambulancia|otro
            $table->string('fuente_informacion')->nullable()->after('forma_llegada');
            $table->string('entrega_institucion_persona')->nullable()->after('fuente_informacion');
            $table->string('entrega_telefono')->nullable()->after('entrega_institucion_persona');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'institucion_sistema',
                'unidad_operativa',
                'cod_uo',
                'cod_provincia',
                'cod_canton',
                'cod_parroquia',
                'numero_historia_clinica',
                'fecha_admision',
                'referido_de',
                'avisar_nombre',
                'avisar_parentesco',
                'avisar_direccion',
                'avisar_telefono',
                'forma_llegada',
                'fuente_informacion',
                'entrega_institucion_persona',
                'entrega_telefono',
            ]);
        });
    }
};
