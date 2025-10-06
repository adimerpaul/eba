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
        Schema::create('colmenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apiario_id')->constrained('apiarios')->cascadeOnDelete();
            $table->foreignId('tipo_miel_id')->nullable()->constrained('tipo_miel');

            $table->string('codigo_colmena', 50)->nullable()->unique();
            $table->string('tipo_colmena', 50)->nullable();
            $table->date('fecha_instalacion')->nullable();
            $table->date('reina_fecha_nacimiento')->nullable();
            $table->string('reina_procedencia', 100)->nullable();
            $table->string('estado', 20)->default('ACTIVA'); // ACTIVA/INACTIVA

            $table->timestamps();
            $table->softDeletes();
            $table->index('apiario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colmenas');
    }
};
