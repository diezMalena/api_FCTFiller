<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para la tabla GrupoFamilia
 *
 * @return void
 * @author David Sánchez Barragán
 */
class CreateGrupoFamilia extends Migration
{

    public function up()
    {
        Schema::create('grupo_familia', function (Blueprint $table) {
            $table->string('cod_grupo');
            $table->unsignedBigInteger('id_familia');
            $table->primary(['cod_grupo', 'id_familia']);
            $table->foreign('cod_grupo')->references('cod')->on('grupo')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_familia')->references('id')->on('familia_profesional')->onDelete('cascade')->onUpdate('cascade');
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
        //Schema::dropIfExists('grupo_familias');
    }
}
