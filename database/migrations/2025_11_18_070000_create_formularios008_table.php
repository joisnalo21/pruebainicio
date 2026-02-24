<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formularios008', function (Blueprint $table) {
            $table->id();

            // Relación con paciente
            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->cascadeOnDelete();

            // NOTA: created_by se agrega en la migración 2025_12_16_000001_add_created_by_to_formularios008_table.php
            // (esto evita duplicar columna al hacer migrate:fresh o al correr tests con sqlite)

            // Control del wizard
            $table->string('estado')->default('borrador'); // borrador | completo | archivado
            $table->unsignedTinyInteger('paso_actual')->default(1);

            // Auditoría básica
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formularios008');
    }
};
