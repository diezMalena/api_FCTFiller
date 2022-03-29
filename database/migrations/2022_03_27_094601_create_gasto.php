<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGasto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gasto', function (Blueprint $table) {
            $table->string('dni_alumno');
            $table->string('curso_academico');
            $table->string('tipo_desplazamiento')->default('');
            $table->integer('total_gastos')->default(0);
            $table->string('residencia_alumno')->default('');
            $table->string('ubicacion_centro_trabajo')->default('');
            $table->integer('distancia_centroEd_centroTra')->default(0);
            $table->integer('distancia_centroEd_residencia')->default(0);
            $table->integer('distancia_centroTra_residencia')->default(0);
            $table->primary(['dni_alumno', 'curso_academico']);
            $table->foreign('dni_alumno')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gasto');
    }
}
