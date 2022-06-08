<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla cuestionariosRespondidos
 *
 * @author pablo
 */

class CreateCuestionarioRespondidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuestionario_respondidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_usuario');
            $table->string('titulo');
            $table->string('destinatario');
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
        Schema::dropIfExists('cuestionario_respondidos');
    }
}
