<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlumnoCurso extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_curso', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('cod_curso');
            $table->string('matricula');
            $table->foreign('dni')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_curso')->references('cod_curso')->on('curso')->onDelete('cascade')->onUpdate('cascade');
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
