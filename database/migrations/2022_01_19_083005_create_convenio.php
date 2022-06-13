<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla convenio
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateConvenio extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author @DaniJCoello (24-01-22)
     * @author David Sánchez Barragán (1-2-22)
     * @return void
     */
    public function up()
    {
        Schema::create('convenio', function (Blueprint $table) {
            $table->string('cod_convenio')->primary();
            $table->string('cod_centro');
            $table->unsignedBigInteger('id_empresa');
            $table->date('fecha_ini')->nullable(true);
            $table->date('fecha_fin')->nullable(true);
            $table->string('ruta_anexo')->nullable(true);
            $table->foreign('id_empresa')->references('id')->on('empresa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cod_centro')->references('cod')->on('centro_estudios')->onDelete('cascade')->onUpdate('cascade');
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
