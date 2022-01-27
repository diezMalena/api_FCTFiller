<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmpresaAlumno extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_alumno', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empresa');
            $table->string('dni_alumno');
            $table->string('curso_academico');
            $table->integer('num_horas');
            $table->string('horario');
            $table->date('fecha_ini');
            $table->date('fecha_fin');
            $table->primary(['id_empresa', 'dni_alumno']);
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
