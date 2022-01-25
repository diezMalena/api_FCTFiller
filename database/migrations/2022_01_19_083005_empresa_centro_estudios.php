<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmpresaCentroEstudios extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author @DaniJCoello (24-01-22)
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_centro_estudios', function (Blueprint $table) {
            $table->string('cod_convenio')->primary();
            $table->string('cod_centro');
            $table->unsignedBigInteger('id_empresa');
            $table->date('fecha');
            $table->integer('firmado_director');
            $table->integer('firmado_empresa');
            $table->string('ruta_anexo');
            $table->foreign('id_empresa')->references('id')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_centro')->references('cod_centro')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
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
