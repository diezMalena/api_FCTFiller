<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RolTrabajadorAsignado extends Migration
{
    /**
     * Run the migrations.
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('rol_trabajador_asignado', function (Blueprint $table) {
            $table->string('dni');
            $table->unsignedBigInteger('id_rol');
            $table->primary(['dni', 'id_rol']);
            $table->foreign('dni')->references('dni')->on('trabajador')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_rol')->references('id')->on('roles_empresa')->onDelete('cascade')->onUpdate('cascade');
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
