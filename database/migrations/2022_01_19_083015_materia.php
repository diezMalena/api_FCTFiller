<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Materia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function down()
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->string('cod_materia')->primary();
            $table->string('descripcion');
            $table->string('abreviatura');
            $table->timestamps();
        });
    }
}
