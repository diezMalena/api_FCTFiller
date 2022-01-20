<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Trabajador extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('trabajador', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('nombre');
            $table->string('cif_empresa');
            $table->string('nombre_centro');
            $table->foreign('cif_empresa')->references('cif')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('nombre_centro')->references('nombre')->on('centro_trabajo')->onDelete('cascade')->onUpdate('cascade');
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
