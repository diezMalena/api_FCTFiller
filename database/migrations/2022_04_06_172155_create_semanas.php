<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemanas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('semana', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_fct');
            $table->unsignedBigInteger('id_quinto_dia');
            $table->integer('firmado_alumno')->default(0);
            $table->integer('firmado_tutor_estudios')->default(0);
            $table->integer('firmado_tutor_empresa')->default(0);
            $table->string('ruta_hoja')->default('');
            $table->foreign('id_fct')->references('id_fct')->on('seguimiento')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_quinto_dia')->references('id')->on('seguimiento')->cascadeOnDelete()->cascadeOnUpdate();

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
        Schema::dropIfExists('semana');
    }
}
