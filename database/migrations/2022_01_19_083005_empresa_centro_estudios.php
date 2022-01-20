<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmpresaCentroEstudios extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_centro_estudios', function (Blueprint $table) {
            $table->string('convenio');
            $table->date('fecha');
            $table->string('cod_centro');
            $table->string('cif_empresa');
            $table->primary(['cod_centro', 'cif_empresa']);
            $table->foreign('cif_empresa')->references('cif')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
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
