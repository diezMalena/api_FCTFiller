<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración de la tabla Seguimiento.
 * @author Malena
 */

class CreateSeguimiento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seguimiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_fct');
            $table->unsignedBigInteger('orden_jornada');
            $table->date('fecha_jornada');
            $table->string('actividades');
            $table->string('observaciones')->nullable();
            $table->float('tiempo_empleado');
            $table->foreign('id_fct')->references('id')->on('fct')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('seguimiento');
    }
}
