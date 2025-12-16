<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            // Quién creó el formulario (médico)
            $table->foreignId('created_by')
                ->nullable()
                ->after('paciente_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
