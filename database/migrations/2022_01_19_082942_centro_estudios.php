<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CentroEstudios extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author @DaniJCoello (24-01-22)
     * @return void
     */
    public function up()
    {
        Schema::create('centro_estudios', function (Blueprint $table) {
            $table->string('cod_centro')->primary();
            $table->string('cif')->unique();
            $table->string('cod_centro_convenio');
            $table->string('nombre');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('direccion');
            $table->string('cp');
            $table->string('telefono');
            $table->string('email');
            // $table->string('dni_director');
            // $table->foreign('dni_director')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
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
