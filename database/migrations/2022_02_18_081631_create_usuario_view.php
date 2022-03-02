<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE VIEW usuarios_view AS
        (select alumno.email, alumno.password, "alumno" as "perfil"
        from alumno UNION all
        select trabajador.email, trabajador.password, "trabajador" as "perfil"
        from trabajador UNION all
        select profesor.email, profesor.password, "profesor" as "perfil"
        from profesor)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario_view');
    }
}
