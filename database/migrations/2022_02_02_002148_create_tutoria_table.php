<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla tutoria
 *
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateTutoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutoria', function (Blueprint $table) {
            $table->string('dni_profesor');
            $table->string('cod_grupo');
            $table->string('curso_academico');
            $table->string('cod_centro');
            $table->primary(['dni_profesor', 'cod_grupo','cod_centro']);
            $table->foreign('dni_profesor')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_grupo')->references('cod')->on('grupo')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_centro')->references('cod')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
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
        //Schema::dropIfExists('tutorias');
    }
}
