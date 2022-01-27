<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Curso extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('curso', function (Blueprint $table) {
            $table->string('cod_curso')->primary();
            $table->string('anio');
            $table->string('estudio');
            $table->string('dni_tutor');
            $table->string('cod_ciclo');
            $table->foreign('dni_tutor')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
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
