<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alumno extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('horario');
            $table->integer('num_horas');
            $table->date('fecha_ini');
            $table->date('fecha_fin');
            $table->string('cif');
            $table->string('cod_curso');
            $table->foreign('cif')->references('cif')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_curso')->references('cod_curso')->on('curso')->onDelete('cascade')->onUpdate('cascade');
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
