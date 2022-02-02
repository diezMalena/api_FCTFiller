<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clase para la migración de la tabla rol_profesor.
 *
 * @author David Sánchez Barragán (1-1-22)
 */
class CreateRolProfesor extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @author David Sánchez Barragán (1-1-22)
     * @return void
     */
    public function up()
    {
        Schema::create('rol_profesor', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
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
