<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla fct
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateFct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fct', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->string('dni_alumno');
            $table->string('dni_tutor_empresa');
            $table->string('curso_academico');
            $table->string('horario');
            $table->integer('num_horas');
            $table->date('fecha_ini');
            $table->date('fecha_fin');
            $table->integer('firmado_director');
            $table->integer('firmado_empresa');
            $table->string('ruta_anexo');
            $table->string('departamento');
            $table->foreign('id_empresa')->references('id')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
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
        //
    }
}
