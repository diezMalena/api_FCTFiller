<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MateriaCurso extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('materia_curso', function (Blueprint $table) {
            $table->string('cod_curso');
            $table->string('cod_materia');
            $table->primary(['cod_curso', 'cod_materia']);
            $table->foreign('cod_curso')->references('cod_curso')->on('curso')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_materia')->references('cod_materia')->on('materia')->onDelete('cascade')->onUpdate('cascade');
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
