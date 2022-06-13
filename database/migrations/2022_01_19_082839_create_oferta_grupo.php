<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla oferta_grupo
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateOfertaGrupo extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('oferta_grupo', function (Blueprint $table) {
            $table->string('cod_centro');
            $table->string('cod_grupo');
            $table->primary(['cod_centro', 'cod_grupo']);
            $table->foreign('cod_centro')->references('cod')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_grupo')->references('cod')->on('grupo')->onDelete('cascade')->onUpdate('cascade');
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
