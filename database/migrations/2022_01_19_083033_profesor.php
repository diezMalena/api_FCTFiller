<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Profesor extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author @DaniJCoello (24-01-22)
     * @return void
     */
    public function up()
    {
        Schema::create('profesor', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('cod_centro_estudios');
            $table->foreign('cod_centro_estudios')->references('cod_centro')->on('centro_estudios')->cascadeOnDelete()->cascadeOnUpdate();
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
