<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CentroEstudios extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('centro_estudios', function (Blueprint $table) {
            $table->string('cod_centro')->primary();
            $table->string('nombre');
            $table->string('ciudad');
            $table->string('direccion');
            $table->string('provincia');
            $table->string('cod_postal');
            $table->string('cif');
            $table->string('telefono');
            $table->string('email');
            $table->string('dni_director');
            $table->foreign('dni_director')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
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
