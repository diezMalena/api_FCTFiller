<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla trabajador
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateTrabajador extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author @DaniJCoello (24-01-22)
     * @return void
     */
    public function up()
    {
        Schema::create('trabajador', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellidos');
            $table->unsignedBigInteger('id_empresa');
            $table->foreign('id_empresa')->references('id')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
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
