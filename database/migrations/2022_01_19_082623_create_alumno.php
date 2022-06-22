<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla alumno
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CreateAlumno extends Migration
{
    /**
     * Run the migrations.
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function up()
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->string('dni')->primary();
            $table->integer('cod_alumno')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('provincia');
            $table->string('localidad');
            $table->boolean('va_a_fct');
            $table->string('foto')->default('./assets/images/defaultProfilePicture.png')->nullable(true);
            $table->string('curriculum')->nullable(true);
            $table->string('cuenta_bancaria')->nullable(true);
            $table->string('matricula_coche')->nullable(true);
            $table->date('fecha_nacimiento')->nullable(true);
            $table->string('domicilio')->nullable(true);
            $table->string('telefono')->nullable(true);
            $table->string('movil')->nullable(true);
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
        Schema::dropIfExists('alumno');
    }
}
