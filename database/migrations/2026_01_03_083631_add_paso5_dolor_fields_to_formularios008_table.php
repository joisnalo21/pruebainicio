<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->boolean('no_aplica_dolor')->default(false)->after('no_aplica_apartado_3');
            $table->json('dolor_items')->nullable()->after('no_aplica_dolor');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['no_aplica_dolor', 'dolor_items']);
        });
    }
};
