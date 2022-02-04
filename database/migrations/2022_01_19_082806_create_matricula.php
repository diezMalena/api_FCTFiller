<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla matricula
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateMatricula extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('matricula', function (Blueprint $table) {
            $table->string('cod')->primary();
            $table->string('cod_centro');
            $table->string('dni_alumno');
            $table->string('cod_grupo');
            $table->string('curso_academico');
            $table->foreign('cod_centro')->references('cod')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dni_alumno')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_grupo')->references('cod')->on('grupo')->onDelete('cascade')->onUpdate('cascade');
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
