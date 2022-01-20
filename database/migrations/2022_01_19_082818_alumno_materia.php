<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlumnoMateria extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_materia', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('cod_materia');
            $table->boolean('aprobado');
            $table->string('curso_academico');
            $table->foreign('dni')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_materia')->references('cod_materia')->on('materia')->onDelete('cascade')->onUpdate('cascade');
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
        //
    }
}
