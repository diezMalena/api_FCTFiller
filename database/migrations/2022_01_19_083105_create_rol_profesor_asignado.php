<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla rol_profesor_asignado
 *
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateRolProfesorAsignado extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('rol_profesor_asignado', function (Blueprint $table) {
            $table->string('dni');
            $table->unsignedBigInteger('id_rol');
            $table->primary(['dni', 'id_rol']);
            $table->foreign('dni')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_rol')->references('id')->on('rol_profesor')->onDelete('cascade')->onUpdate('cascade');
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
