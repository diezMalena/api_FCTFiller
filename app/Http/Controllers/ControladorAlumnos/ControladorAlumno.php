<?php

namespace App\Http\Controllers\ControladorAlumnos;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\CentroEstudios;
use App\Models\Empresa;
use App\Models\FamiliaProfesional;
use App\Models\Fct;
use App\Models\NivelEstudios;
use App\Models\Profesor;
use App\Models\Seguimiento;
use App\Models\Trabajador;
use App\Auxiliar\Auxiliar;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Auxiliar\Parametros as AuxiliarParametros;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;

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

    /**
     * Mètodo que genera el Anexo III con los datos necesarios extraídos de la BBDD.
     * @author Malena.
     */
    public function generarAnexo3(Request $req){
        $dni_alumno = $req->get('dni');
        //error_log($dni_alumno);

        //Primero, vamos a sacar el centro donde está el alumno:
        $centro = $this->centroDelAlumno($dni_alumno);
        //Sacamos el nombre del alumno:
        $alumno = $this->getNombreAlumno($dni_alumno);
        //Sacamos el nombre del tutor del alumno:
        $tutor = $this->getNombreTutor($dni_alumno);
        //Sacamos la familia profesional que le corresponde al alumno:
        $familia_profesional = $this->getFamiliaProfesional($dni_alumno);
        //Sacamos el nombre del ciclo en el que esta matriculado el alumno:
        $ciclo = $this->getCicloFormativo($dni_alumno);
        //Sacamos el nombre de la empresa en la que esta el alumno haciendo las practicas:
        $empresa = $this->getNombreEmpresa($dni_alumno);
        //Sacamos el nombre del tutor de la empresa al que esta asignado el alumno:
        $tutor_empresa = $this->getNombreTutorEmpresa($dni_alumno);
        //Sacamos los registros que necesitamos de la tabla FCT:
        $fct = $this->getDatosFct($dni_alumno);
        //Cogemos las ultimas 5 jornadas, para ponerlas en el documento:
        $jornadas = $this->las5UltimasJornadas($dni_alumno);

        //Construyo el array con todos los datos y ss correspondientes prefijos.
        $auxPrefijos = ['centro', 'alumno', 'tutor', 'familia_profesional','ciclo','empresa','tutor_empresa','fct'];
        $auxDatos = [$centro, $alumno, $tutor, $familia_profesional,$ciclo,$empresa,$tutor_empresa,$fct];
        $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

        //Recorro las 5 jornadas, y les establezco su valor correspondiente en el documento.
        for($i = 0; $i < count($jornadas); $i++){
            $datos['jornada'.$i.'.actividades'] = $jornadas[$i]->actividades;
            $datos['jornada'.$i.'.tiempo_empleado'] = $jornadas[$i]->tiempo_empleado;
            $datos['jornada'.$i.'.observaciones'] = $jornadas[$i]->observaciones;
        }
        //Nombre de la plantilla:
        $nombrePlantilla = 'anexo3';

        //La ruta donde se va a almacenar el documento:
        $rutaOrigen = 'anexos/plantillas/' . $nombrePlantilla . '.docx';

        //Establezco la fecha para ponerlo en el nombre del documento:
        $fecha = Carbon::now();
        $fecha_doc = $fecha->day . '_' . AuxiliarParametros::MESES[$fecha->month] . '_' . $fecha->year % 100;
        //De momento, formare el nombre del documento con el dni del alumno + fecha.
        $nombre = $nombrePlantilla . '-' . $dni_alumno . '-' . $fecha_doc .'.docx';
        $rutaDestino = 'anexos'.DIRECTORY_SEPARATOR.'rellenos'.DIRECTORY_SEPARATOR.'anexo3'.DIRECTORY_SEPARATOR . $nombre;

        //Creo la plantilla y la relleno con los valores establecidos anteriormente.
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);

        return response()->download(public_path($rutaDestino));
    }


    /**
     * Método que recoge los campos necesarios del centro de estudios de la BBDD.
     * @return $centro.
     * @author Malena.
     */
    public function centroDelAlumno(string $dni_alumno){
        $centro = CentroEstudios::join('matricula', 'centro_estudios.cod','=','matricula.cod_centro')
        ->select('centro_estudios.cif AS cif', 'centro_estudios.nombre AS nombre')
        ->where('matricula.dni_alumno','=',$dni_alumno)
        ->first();

        return $centro;
    }


    /**
     * Método que recoge el nombre del alumno.
     * Para futuro cambio, concatenar el nombre + apellidos.
     * @return $nombre
     * @author Malena.
     */
    public function getNombreAlumno(string $dni_alumno){
        $nombre = Alumno::select('nombre')
        ->where('dni','=',$dni_alumno)
        ->first();

        return $nombre;
    }


    /**
     * Método que recoge el nombre del tutor del centro estudios que le corresponde al alumno.
     * Para futuro cambio, concatenar el nombre + apellidos.
     * @return @tutor
     * @author Malena
     */
    public function getNombreTutor(string $dni_alumno){
        $tutor = Profesor::join('tutoria','profesor.dni','=','tutoria.dni_profesor')
        ->join('grupo','tutoria.cod_grupo','=','grupo.cod')
        ->join('matricula','matricula.cod_grupo','=','grupo.cod')
        ->where('matricula.dni_alumno','=',$dni_alumno)
        ->select('profesor.nombre AS nombre')
        ->first();

        return $tutor;
    }


    /**
     * Método que recoge la familia profesional del ciclo en el que está matriculado el alumno.
     * @return $familia_profesional
     * @author Malena
     */
    public function getFamiliaProfesional(string $dni_alumno){
        $familia_profesional = FamiliaProfesional::join('grupo_familia','familia_profesional.id','=','grupo_familia.id_familia')
        ->join('grupo','grupo_familia.cod_grupo','=','grupo.cod')
        ->join('matricula','matricula.cod_grupo','=','grupo.cod')
        ->where('matricula.dni_alumno','=',$dni_alumno)
        ->select('familia_profesional.descripcion AS descripcion')
        ->first();

        return $familia_profesional;
    }


    /**
     * Método que recoge el ciclo formativo en el que está matriculado el alumno.
     * @return $ciclo_formativo
     * @author Malena
     */
    public function getCicloFormativo(string $dni_alumno){
        $ciclo_formativo = NivelEstudios::join('grupo','nivel_estudios.cod','=','grupo.cod_nivel')
        ->join('matricula','matricula.cod_grupo','=','grupo.cod')
        ->where('matricula.dni_alumno','=',$dni_alumno)
        ->select('nivel_estudios.cod AS cod_nivel','grupo.nombre_largo AS nombre')
        ->first();

        return $ciclo_formativo;
    }


    /**
     * Método que recoge el nombre de la empresa en la que está asociado el alumno.
     * @return $nombre_empresa
     * @author Malena
     */
    public function getNombreEmpresa(string $dni_alumno){
        $nombre_empresa = Empresa::join('empresa_grupo','empresa.id','=','empresa_grupo.id_empresa')
        ->join('grupo','grupo.cod','=','empresa_grupo.cod_grupo')
        ->join('matricula','matricula.cod_grupo','=','grupo.cod')
        ->where('matricula.dni_alumno','=',$dni_alumno)
        ->select('empresa.nombre AS nombre')
        ->first();

        return $nombre_empresa;
    }


    /**
     * Método que recoge de la BBDD el nombre del tutor que tiene asignado el alumno en la empresa.
     * @return $tutor_empresa
     * @author Malena
     */
    public function getNombreTutorEmpresa(string $dni_alumno){
        $tutor_empresa = Trabajador::join('fct','trabajador.dni','=','fct.dni_tutor_empresa')
        ->where('fct.dni_alumno','=',$dni_alumno)
        ->select('trabajador.nombre AS nombre')
        ->first();

        return $tutor_empresa;
    }


    /**
     * Método que recoge los datos necesarios correspondientes a la tabla FCT.
     * @return $fct
     * @author Malena
     */
    public function getDatosFct(string $dni_alumno){
        $fct = FCT::where('fct.dni_alumno','=',$dni_alumno)
        ->select('fecha_ini AS fecha_ini','fecha_fin AS fecha_fin','departamento AS departamento','num_horas AS horas')
        ->first();

        return $fct;
    }


    /**
     * Método que recoge las últimas 5 jornadas para insertarlas en la tabla del Anexo III.
     * @return array $jornadas
     * @author Malena
     */
    public function las5UltimasJornadas(string $dni_alumno){
        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;

        $jornadas = Seguimiento::join('fct', 'fct.id','=','seguimiento.id_fct')
        ->select('seguimiento.actividades AS actividades','seguimiento.observaciones AS observaciones','seguimiento.tiempo_empleado AS tiempo_empleado')
        ->where('fct.dni_alumno','=',$dni_alumno)
        ->where('fct.id_empresa','=',$id_empresa)
        ->orderBy('seguimiento.orden_jornada','DESC')
        ->take(5)
        ->get();

        return $jornadas;
    }
}
