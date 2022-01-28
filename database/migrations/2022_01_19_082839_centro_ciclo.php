<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CentroCiclo extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('centro_ciclo', function (Blueprint $table) {
            $table->string('cod_ciclo');
            $table->string('cod_centro');
            $table->primary(['cod_centro', 'cod_ciclo']);
            $table->foreign('cod_centro')->references('cod_centro')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_ciclo')->references('cod_ciclo')->on('ciclo')->onDelete('cascade')->onUpdate('cascade');
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
