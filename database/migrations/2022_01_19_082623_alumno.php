<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alumno extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('localidad');
            $table->string('provincia');
            $table->boolean('va_a_fct');
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
