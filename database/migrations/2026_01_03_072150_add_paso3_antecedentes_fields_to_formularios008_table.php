<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            // Paso 3 / Sección 4: Antecedentes
            $table->boolean('antecedentes_no_aplica')->default(false)->after('valor_alcochek');
            $table->json('antecedentes_tipos')->nullable()->after('antecedentes_no_aplica'); // array
            $table->string('antecedentes_otro_texto')->nullable()->after('antecedentes_tipos');
            $table->text('antecedentes_detalle')->nullable()->after('antecedentes_otro_texto');
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropColumn([
                'antecedentes_no_aplica',
                'antecedentes_tipos',
                'antecedentes_otro_texto',
                'antecedentes_detalle',
            ]);
        });
    }
};