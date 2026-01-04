<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->boolean('no_aplica_lesiones')->default(false)->after('valor_alcochek');
            $table->json('lesiones')->nullable()->after('no_aplica_lesiones'); 
            // lesiones = [{view:'front'|'back', x:0..1, y:0..1, tipo:1..14}]
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn(['no_aplica_lesiones', 'lesiones']);
        });
    }
};