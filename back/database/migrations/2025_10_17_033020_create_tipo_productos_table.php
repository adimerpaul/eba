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
        Schema::create('tipo_productos', function (Blueprint $table) {
            $table->id();
//            id serial NOT NULL PRIMARY KEY,
//	codigo_tipo varchar(10) NOT NULL,
//	nombre_tipo varchar(100) NOT NULL,
//	detalles varchar(100) null
            $table->string('codigo_tipo', 10)->unique();
            $table->string('nombre_tipo', 100);
            $table->string('detalles', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_productos');
    }
};
