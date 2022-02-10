<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla grupo
 *
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateGrupo extends Migration
{
    /**
     * Run the migrations.
     * @author David Sánchez Barragán
     * @return void
     */
    public function up()
    {
        Schema::create('grupo', function (Blueprint $table) {
            //2DAM
            $table->string('cod')->primary();
            //2º CFGS Desarrollo de Aplicaciones Multiplataforma (LOE)
            $table->string('nombre_largo');
            //Desarrollo de Aplicaciones Multiplataforma
            $table->string('nombre_ciclo');
            /**
            * DSB Eliminación del campo cod_familia_profesional, que se traspasa a la tabla GrupoFamilia,
            * puesto que un grupo puede tener más de una familia profesional
            */
            // //Informática y telecomunicaciones
            // $table->unsignedBigInteger('cod_familia_profesional');
            //CFGS
            $table->string('cod_nivel');
            //$table->foreign('cod_familia_profesional')->references('id')->on('familia_profesional')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_nivel')->references('cod')->on('nivel_estudios')->onDelete('cascade')->onUpdate('cascade');
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
