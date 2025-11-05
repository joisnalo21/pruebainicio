<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
public function up()
{
    Schema::create('formularios008', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
        $table->string('motivo');
        $table->text('diagnostico')->nullable();
        $table->text('tratamiento')->nullable();
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        // contenido
    }
};












