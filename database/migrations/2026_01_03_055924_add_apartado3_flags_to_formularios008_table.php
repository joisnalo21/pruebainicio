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
    Schema::table('formularios008', function (Blueprint $table) {
        $table->boolean('no_aplica_apartado_3')->default(false)->after('grupo_sanguineo');
        $table->boolean('custodia_policial')->default(false)->after('evento_tipos');
    });
}

public function down(): void
{
    Schema::table('formularios008', function (Blueprint $table) {
        $table->dropColumn(['no_aplica_apartado_3', 'custodia_policial']);
    });
}

};
