<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreguntasRespondidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preguntas_respondidas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_cuestionario_respondido')->unsigned();
            $table->string('tipo');
            $table->text('pregunta');
            $table->text('respuesta');
            $table->foreign('id_cuestionario_respondido')->references('id')->on('cuestionario_respondidos')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('preguntas_respondidas');
    }
}
