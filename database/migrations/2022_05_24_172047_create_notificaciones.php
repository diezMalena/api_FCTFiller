<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('mensaje');
            $table->integer('leido');
            $table->unsignedBigInteger('semana')->nullable();
            $table->timestamps();

            $table->foreign('email')->references('email')->on('users');
            $table->foreign('semana')->references('id')->on('semana');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}
