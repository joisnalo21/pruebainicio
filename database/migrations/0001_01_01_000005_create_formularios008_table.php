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

            // Médico que crea el formulario
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Control del wizard
            $table->string('estado')->default('borrador'); // borrador | completo
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
