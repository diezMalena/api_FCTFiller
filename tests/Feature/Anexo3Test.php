<?php

namespace Tests\Feature;
use App\Models\Seguimiento;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class Anexo3Test extends TestCase
{
    /**
     * Hace una prueba de insertar una jornada nueva.
     * @author Malena
     */
    public function test_createJornada(){
        $ultimoOrden = Seguimiento::select(DB::raw('MAX(orden_jornada) AS orden_jornada'));
        $jornada = Seguimiento::create([
            'id_fct' => 65,
            'orden_jornada' => $ultimoOrden + 1,
            'fecha_jornada' => Carbon::now(),
            'actividades' => 'muchas',
            'observaciones' => 'ninguna',
            'tiempo_empleado' => 5
        ]);
        $this->json('ANY', '/api/addJornada', $jornada)->assertStatus(200);
    }


    /**
     * Metodo en el que se actualiza una jornada.
     * @author Malena
     */
    public function test_updateJornada(){
        $jornada = Seguimiento::select('*')
            ->where('actividades','=','muchas')
            ->first();


        Seguimiento::where('actividades', $jornada->actividades)->update([
            'actividades' => 'muchiiiisimas',
        ]);

        $this->json('PUT', '/api/update_jornada', $jornada)->assertStatus(200);
    }
}
