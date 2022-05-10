<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaTransporte extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factura_transporte', function (Blueprint $table) {
            $table->id();
            $table->string('dni_alumno');
            $table->string('curso_academico');
            $table->date('fecha');
            $table->float('importe');
            $table->string('origen')->default('');
            $table->string('destino')->default('');
            $table->string('imagen_ticket')->default('');
            $table->foreign('dni_alumno')->references('dni')->on('alumno')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('factura_transporte');
    }
}
