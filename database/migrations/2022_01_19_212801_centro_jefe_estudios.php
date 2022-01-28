<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CentroJefeEstudios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centro_jefe_estudios', function (Blueprint $table) {
            $table->string('dni');
            $table->string('cod_centro');
            $table->primary(['dni', 'cod_centro']);
            $table->foreign('dni')->references('dni')->on('profesor')->onDelete('cascade')->onUpdate('cascade');
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
