<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->json('diagnosticos_alta')->nullable()->after('diagnosticos_ingreso');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['diagnosticos_alta']);
        });
    }
};
