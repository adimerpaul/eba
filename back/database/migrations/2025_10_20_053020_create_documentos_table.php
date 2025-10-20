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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acopio_cosecha_id');
            $table->unsignedBigInteger('user_id');
            $table->string('nombre')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->longText('html')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('acopio_cosecha_id')->references('id')->on('acopio_cosechas');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
