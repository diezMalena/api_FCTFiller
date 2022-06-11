<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tipo')->after('remember_token');
        });
        if (User::count() == 0) {
            DB::statement(
                'INSERT INTO users (email, password, name, tipo)
                        SELECT alumno.email, alumno.password, CONCAT(alumno.nombre, " ", alumno.apellidos), "alumno" AS "perfil"
                        FROM alumno UNION ALL
                        SELECT trabajador.email, trabajador.password, CONCAT(trabajador.nombre, " ", trabajador.apellidos), "trabajador" AS "perfil"
                        FROM trabajador UNION ALL
                        SELECT profesor.email, profesor.password, CONCAT(profesor.nombre, " ", profesor.apellidos), "profesor" AS "perfil"
                        FROM profesor'
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
