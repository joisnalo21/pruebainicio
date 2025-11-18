<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('pacientes', function (Blueprint $table) {
        $table->id();

        $table->string('cedula')->unique();
        $table->string('primer_nombre');
        $table->string('segundo_nombre')->nullable();
        $table->string('apellido_paterno');
        $table->string('apellido_materno');

        $table->date('fecha_nacimiento');
        $table->integer('edad')->nullable();
        $table->string('sexo', 15);

        // Ubicación
        $table->string('provincia');
        $table->string('canton');
        $table->string('parroquia');

        // Contacto
        $table->string('telefono', 20);
        $table->string('direccion');
        $table->string('barrio')->nullable();

        // Información adicional
        $table->string('zona')->nullable(); // U/R
        $table->string('lugar_nacimiento')->nullable();
        $table->string('nacionalidad')->default('Ecuador');
        $table->string('grupo_cultural')->nullable();
        $table->string('estado_civil')->nullable();
        $table->string('instruccion')->nullable();
        $table->string('ocupacion');
        $table->string('empresa')->nullable();
        $table->string('seguro_salud')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
