<?php

namespace App\Http\Controllers\ControladorAlumnos;

use App\Http\Controllers\Controller;
use App\Models\Fct;
use App\Models\Seguimiento;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;

class ControladorAlumno extends Controller
{
    //
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Método que recoge el id y el id_empresa de la tabla FCT correspondiente al dni_alumno
     * que recibe como parametro.
     * @param $dni_alumno.
     * @author Malena.
     */
    public function buscarId_fct(string $dni_alumno){
        $datosFct = FCT::select('id','id_empresa')
        ->where('fct.dni_alumno','=',$dni_alumno)
        ->get();
        return $datosFct;
    }

    /**
     * Método que recibe un objeto Jornada y el dni_alumno del alumno que ha iniciado sesión en la aplicación,
     * y con ello añade la jornada en la tabla Seguimiento de la BBDD. Le devuelve a la parte de cliente un
     * array de Jornadas correspondientes al alumno.
     * @author Malena.
     */
    public function addJornada(Request $req){
        $jornada = $req->get('jornada');

        $fct = $this->buscarId_fct($req->get('dni_alumno'));
        $id_fct = $fct[0]->id;
        $jornada['id_fct'] = $id_fct;

        $ultimoOrden = $this->encontrarUltimoOrden($id_fct);
        if($ultimoOrden[0]->orden_jornada == null){
            $jornada['orden_jornada'] = 1;
        }else{
            $jornada['orden_jornada'] =$ultimoOrden[0]->orden_jornada + 1;
        }
        $seguimiento = Seguimiento::create($jornada);
    }

    /**
     * Este método me devuelve el valor más alto del campo orden, para
     * ordenar los resultados por id_fct, y mostrarlos en la tabla de las
     * jornadas rellenadas por el alumno en orden descendente.
     * @author Malena.
     */
    public function encontrarUltimoOrden(int $id_fct){
        $ultimoOrden = Seguimiento::select(DB::raw( 'MAX(orden_jornada) AS orden_jornada'))
                        ->where('id_fct','=',$id_fct)
                        ->get();
        //error_log($ultimoOrden);
        return $ultimoOrden;
    }

    /**
     * Metodo que se encarga de seleccionar las jornadas que le corresponden al alumno
     * con su empresa asignada.
     * @param $dni_alumno del alumno que inicia sesion, $id_empresa de la que tiene asignada dicho alumno.
     * @author Malena.
     * @return $jornadas, array de jornadas que tiene el alumno añadidas en la BBDD.
     */
    public function devolverJornadas(Request $req){
        $dni_alumno = $dni_alumno = $req->get('dni');

        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;

        $jornadas = Seguimiento::join('fct', 'fct.id','=','seguimiento.id_fct')
        ->select('seguimiento.id AS id_jornada','seguimiento.orden_jornada AS orden_jornada','seguimiento.fecha_jornada AS fecha_jornada','seguimiento.actividades AS actividades','seguimiento.observaciones AS observaciones','seguimiento.tiempo_empleado AS tiempo_empleado')
        ->where('fct.dni_alumno','=',$dni_alumno)
        ->where('fct.id_empresa','=',$id_empresa)
        ->orderBy('seguimiento.orden_jornada','DESC')
        ->get();

        //error_log($jornadas);

        return response()->json($jornadas,200);
    }


    /**
     * Método que selecciona de la BBDD el nombre, los apellidos y la empresa asignada del alumno
     * que inicia sesión, para mostrarlo en la correspondiente interfaz.
     * @author Malena
     */
    public function devolverDatosAlumno(Request $req){
        $dni_alumno = $req->get('dni');
        try{
            $datosAlumno=FCT::join('alumno', 'alumno.dni','=','fct.dni_alumno')
            ->join('empresa','empresa.id','=','fct.id_empresa')
            ->select('alumno.nombre AS nombre_alumno', 'alumno.apellidos AS apellidos_alumno','empresa.nombre AS nombre_empresa')
            ->where('alumno.dni','=',$dni_alumno)
            ->get();

            return response()->json($datosAlumno,200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Error, los datos no se han enviado.'],450);
        }
    }

    /**
     * Método que recoge el departamento del alumno que inicia sesión, y se encarga
     * de mandarlo a la parte de cliente, donde se gestiona qué hacer dependiendo de si el Departamento
     * tiene o no tiene valor.
     * @author Malena.
     */
    public function gestionarDepartamento(Request $req){
        $dni_alumno = $req->get('dni');
        //error_log($dni_alumno);
        try{
            $departamento=FCT::select('departamento')
            ->where('fct.dni_alumno','=',$dni_alumno)
            ->get();
            //error_log($departamento[0]); //Resultado = {"departamento":""}
            return response()->json($departamento,200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Error, el departamento no se ha enviado.'],450);
        }
    }

    /**
     * Método que se encarga de recoger el valor del Departamento para añadirlo
     * a su campo correspondiente en la tabla FCT.
     * @author Malena.
     */
    public function addDepartamento(Request $req){
        $dni_alumno = $req->get('dni');
        $departamento = $req->get('departamento');
        //error_log($dni_alumno);
        //error_log($departamento);
        try{
            $departamento = FCT::where('dni_alumno', $dni_alumno)
            ->update(['departamento' => $departamento]);
            return response()->json(['message'=>'El departamento se ha insertado correctamente.'],200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Error, el departamento no se ha insertado en la BBDD.'],450);
        }
    }

    /**
     * Método que se encarga de sumar todas las horas del campo "tiempo_empleado" de la tabla Seguimiento,
     * del alumno que inicia sesión para mostrarlas en la interfaz.
     * @author Malena.
     */
    public function sumatorioHorasTotales(Request $req){
        $dni_alumno = $req->get('dni');
        $horas = 0;

        $fct = $this->buscarId_fct($dni_alumno);
        $id_fct = $fct[0]->id;
        //error_log($id_fct);

        try{
            $horasTotales = Seguimiento::join('fct', 'seguimiento.id_fct','=','fct.id')
            ->select( DB::raw( 'SUM( seguimiento.tiempo_empleado) AS horasSumadas'))
            ->where('fct.dni_alumno','=',$dni_alumno)
            ->where('seguimiento.id_fct','=',$id_fct)
            ->groupBy('fct.dni_alumno')
            ->get();

            //error_log($horasTotales[0]->horasSumadas);

            /*Me saltaba un error al no encontrar jornadas en un alumno, y horasSumadas ser null,
            con este control de errores lo soluciono.*/
            if(count($horasTotales) != 0){
                $horas = $horasTotales[0]->horasSumadas;
            }
            return response()->json($horas,200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Error, las hotas se han ido a la verga.'],450);
        }
    }


    /**
     * Método que recibe una jornada editada y la actualiza en la BBDD.
     * @author Malena
     */
    public function updateJornada(Request $req){
        $dni_alumno = $req->get('dni_alumno');
        $jornada = $req->get('jornada');

        try{
            $jornadaUpdate=Seguimiento::where('id','=',$jornada['id_jornada'])
            ->update([
                'orden_jornada' => $jornada['orden_jornada'],
                'fecha_jornada' => $jornada['fecha_jornada'],
                'actividades' => $jornada['actividades'],
                'observaciones' => $jornada['observaciones'],
                'tiempo_empleado' => $jornada['tiempo_empleado']
            ]);

            return response()->json($jornadaUpdate,200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Error, la jornada no se ha actualizado.'],450);
        }
    }
}
