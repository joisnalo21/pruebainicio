<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('pacientes', function (Blueprint $table) {
        if (!Schema::hasColumn('pacientes', 'primer_nombre')) {
            $table->string('primer_nombre')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'segundo_nombre')) {
            $table->string('segundo_nombre')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'apellido_paterno')) {
            $table->string('apellido_paterno')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'apellido_materno')) {
            $table->string('apellido_materno')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'fecha_nacimiento')) {
            $table->date('fecha_nacimiento')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'sexo')) {
            $table->string('sexo', 10)->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'provincia')) {
            $table->string('provincia')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'canton')) {
            $table->string('canton')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'parroquia')) {
            $table->string('parroquia')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'telefono')) {
            $table->string('telefono')->nullable();
        }
        if (!Schema::hasColumn('pacientes', 'ocupacion')) {
            $table->string('ocupacion')->nullable();
        }
    });
}


    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn([
                'primer_nombre',
                'segundo_nombre',
                'apellido_paterno',
                'apellido_materno',
                'fecha_nacimiento',
                'sexo',
                'provincia',
                'canton',
                'parroquia',
                'telefono',
                'ocupacion',
            ]);
        });
    }
};
