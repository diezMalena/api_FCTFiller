<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Anexo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anexos', function (Blueprint $table) {
            $table->id('id_anexo');
            $table->string('tipo_anexo');
            $table->integer('firmado_director')->default('0');
            $table->integer('firmado_empresa')->default('0');
            $table->integer('firmado_alumno')->default('0');
            $table->string('ruta_anexo')->default('0')->index();
            $table->integer('habilitado')->default('1');
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
