<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->unsignedBigInteger('control_proceso_id')->nullable()->after('cosecha_id');
            $table->foreign('control_proceso_id')->references('id')->on('control_procesos')->onDelete('set null');
            $table->index('control_proceso_id');
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['control_proceso_id']);
            $table->dropIndex(['control_proceso_id']);
            $table->dropColumn('control_proceso_id');
        });
    }
};
