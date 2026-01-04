<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->timestamp('archivado_en')->nullable()->after('estado');
            $table->index(['estado', 'archivado_en']);
        });
    }

    public function down(): void
    {
        Schema::table('formularios008', function (Blueprint $table) {
            $table->dropIndex(['estado', 'archivado_en']);
            $table->dropColumn('archivado_en');
        });
    }
};
