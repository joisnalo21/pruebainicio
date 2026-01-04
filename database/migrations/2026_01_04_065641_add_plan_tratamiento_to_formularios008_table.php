<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->json('plan_tratamiento')->nullable()->after('diagnosticos_alta');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['plan_tratamiento']);
        });
    }
};
