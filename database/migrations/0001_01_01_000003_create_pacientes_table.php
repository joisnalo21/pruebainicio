<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('pacientes', function (Blueprint $table) {
        $table->id();
        $table->string('cedula')->unique();
        $table->string('nombre');
        $table->integer('edad');
        $table->string('direccion')->nullable();
        $table->timestamps();
    });
}


    public function down(): void
    {
        // contenido
    }
};









