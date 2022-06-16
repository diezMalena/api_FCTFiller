<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCuestionarioRespondidos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuestionario_respondidos', function (Blueprint $table) {
            $table->string('codigo_centro');
            $table->string('ciclo');
            $table->string('curso_academico');
            $table->string('dni_tutor_empresa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cuestionario_respondidos', function (Blueprint $table) {
            $table->dropColumn('codigo_centro');
            $table->dropColumn('ciclo');
            $table->dropColumn('curso_academico');
            $table->dropColumn('dni_tutor_empresa');
        });
    }
}
