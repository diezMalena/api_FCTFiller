<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGastoTransportePrivado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gasto_transporte_privado', function (Blueprint $table) {
            $table->string('dni_alumno');
            $table->string('curso_academico');
            $table->integer('n_dias');
            $table->primary(['dni_alumno', 'curso_academico']);
            $table->foreign('dni_alumno')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gasto_transporte_privado');
    }
}
