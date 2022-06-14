<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreguntasCuestionarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preguntas_cuestionario', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_cuestionario')->unsigned();
            $table->string('tipo');
            $table->text('pregunta');
            $table->foreign('id_cuestionario')->references('id')->on('cuestionarios')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('preguntas_cuestionario');
    }
}
