<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla alumno
 *
 * @author David Sánchez Barragán (7 de junio de 2022)
 */
class CreateAlumnoNuevosCampos extends Migration
{
    /**
     * Run the migrations.
     * @author David Sánchez Barragán (7 de junio de 2022)
     * @return void
     */
    public function up()
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable(true);;
            $table->string('domicilio')->nullable(true);
            $table->string('telefono')->nullable(true);
            $table->string('movil')->nullable(true);
            // $table->string('foto')->default('./assets/images/defaultProfilePicture.png')->nullable(true);
            // $table->string('curriculum')->nullable(true);
            // $table->string('cuenta_bancaria')->nullable(true);
            // $table->string('matricula_coche')->nullable(true);
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
