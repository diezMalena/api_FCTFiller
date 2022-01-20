<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CentroTrabajo extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('centro_trabajo', function (Blueprint $table) {
            $table->string('direccion');
            $table->string('provincia');
            $table->string('telefono');
            $table->string('email');
            $table->string('localidad');
            $table->string('cif_empresa');
            $table->string('nombre');
            $table->primary(['cif_empresa', 'nombre']);
            $table->foreign('cif_empresa')->references('cif')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
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
