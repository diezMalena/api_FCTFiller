<?php

namespace App\Http\Controllers\ControladorAlumnos;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\CentroEstudios;
use App\Models\Empresa;
use App\Models\FamiliaProfesional;
use App\Models\Fct;
use App\Models\NivelEstudios;
use App\Auxiliar\Parametros;
use App\Models\Profesor;
use App\Models\Seguimiento;
use App\Models\Trabajador;
use App\Models\Matricula;
use App\Models\Anexo;
use App\Models\Semana;
use App\Models\Notificacion;
use App\Auxiliar\Auxiliar;
use Illuminate\Http\Request;
use Exception;
use ZipArchive;
use Carbon\Carbon;
use App\Auxiliar\Parametros as AuxiliarParametros;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Models\AuxCursoAcademico;
use App\Models\FacturaManutencion;
use App\Models\FacturaTransporte;
use App\Models\Gasto;
use App\Models\GrupoFamilia;
use App\Models\Tutoria;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;



class ControladorAlumno extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /***********************************************************************/
    #region Hojas de seguimiento - Anexo III

    /***********************************************************************/
    #region Funciones auxiliares generales

    /**
     * Método que recoge el id y el id_empresa de la tabla FCT correspondiente al dni_alumno
     * que recibe como parametro.
     * @param $dni_alumno.
     * @author Malena.
     */
    public function buscarId_fct(string $dni_alumno)
    {
        $datosFct = FCT::select('id', 'id_empresa')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->get();
        return $datosFct;
    }

    /**
     * Este método me devuelve el valor más alto del campo orden, para
     * ordenar los resultados por id_fct, y mostrarlos en la tabla de las
     * jornadas rellenadas por el alumno en orden descendente.
     * @author Malena.
     */
    public function encontrarUltimoOrden(int $id_fct)
    {
        $ultimoOrden = Seguimiento::select(DB::raw('MAX(orden_jornada) AS orden_jornada'))
            ->where('id_fct', '=', $id_fct)
            ->get();
        return $ultimoOrden;
    }

    /**
     * Método que devuelve el id_empresa a la que el alumno está asociado.
     * @return id_empresa
     * @author Malena
     */
    public function empresaAsignadaAlumno(string $dni_alumno)
    {
        /*Necesitamos también el id_empresa para luego poder mostrar en el desplegable todos
        los tutores y responsables de dicha empresa.*/
        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;
        return $id_empresa;
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Gestión de jornadas

    /**
     * Método que recibe un objeto Jornada y el dni_alumno del alumno que ha iniciado sesión en la aplicación,
     * y con ello añade la jornada en la tabla Seguimiento de la BBDD. Le devuelve a la parte de cliente un
     * array de Jornadas correspondientes al alumno.
     * @author Malena.
     */
    public function addJornada(Request $req)
    {
        $jornada = $req->get('jornada');
        $dni_alumno = $req->get('dni_alumno');
        $fct = $this->buscarId_fct($dni_alumno);
        $id_fct = $fct[0]->id;
        $jornada['id_fct'] = $id_fct;

        $ultimoOrden = $this->encontrarUltimoOrden($id_fct)[0]->orden_jornada;
        if ($ultimoOrden == null) {
            $jornada['orden_jornada'] = 1;
        } else {
            $jornada['orden_jornada'] = $ultimoOrden + 1;
        }
        $seguimiento = Seguimiento::create($jornada);

        if (($ultimoOrden + 1) % 5 == 0) {
            //Es el último día de la semana:
            $jornadas = $this->ultimaJornada($dni_alumno);
            $semana = Semana::create([
                'id_fct' => $id_fct,
                'id_quinto_dia' => $jornadas[0]->id,
            ]);
        }
    }

    /**
     * Metodo que se encarga de seleccionar las jornadas que le corresponden al alumno
     * con su empresa asignada.
     * @param $dni_alumno del alumno que inicia sesion, $id_empresa de la que tiene asignada dicho alumno.
     * @author Malena.
     * @return $semanas, array de semanas que tiene el alumno.
     */
    public function devolverJornadas(Request $req)
    {
        //este array tendrá dentro las jornadas divididas de 5 en 5 para organizarlas en semanas.
        $semanas = []; //Array de semanas
        $semana = []; //Array de jornadas
        $dni_alumno = $req->get('dni');

        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;

        $jornadas = Seguimiento::join('fct', 'fct.id', '=', 'seguimiento.id_fct')
            ->select('seguimiento.id AS id_jornada', 'seguimiento.orden_jornada AS orden_jornada', 'seguimiento.fecha_jornada AS fecha_jornada', 'seguimiento.actividades AS actividades', 'seguimiento.observaciones AS observaciones', 'seguimiento.tiempo_empleado AS tiempo_empleado')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->where('fct.id_empresa', '=', $id_empresa)
            ->orderBy('seguimiento.orden_jornada', 'ASC')
            ->get();


        for ($i = 0; $i < count($jornadas); $i++) {
            $semana[] = $jornadas[$i];
            if (count($semana) == 5 || $i == count($jornadas) - 1) {
                $semanas[] = $semana;
                $semana = [];
            }
        }
        return response()->json($semanas, 200);
    }



    public function devolverSemanas(Request $req)
    {
        $dni_alumno = $req->get('dni');
        $fct = $this->buscarId_fct($dni_alumno);
        $id_fct = $fct[0]->id;

        $semanas = Semana::where('id_fct', '=', $id_fct)
            ->orderBy('id_quinto_dia', 'DESC')
            ->get();

        return response()->json($semanas, 200);
    }


    /**
     * Método que recibe una jornada editada y la actualiza en la BBDD.
     * @author Malena
     */
    public function updateJornada(Request $req)
    {
        $dni_alumno = $req->get('dni_alumno');
        $jornada = $req->get('jornada');

        try {
            $jornadaUpdate = Seguimiento::where('id', '=', $jornada['id_jornada'])
                ->update([
                    'orden_jornada' => $jornada['orden_jornada'],
                    'fecha_jornada' => $jornada['fecha_jornada'],
                    'actividades' => $jornada['actividades'],
                    'observaciones' => $jornada['observaciones'],
                    'tiempo_empleado' => $jornada['tiempo_empleado']
                ]);

            return response()->json($jornadaUpdate, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error, la jornada no se ha actualizado.'], 450);
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Cabeceras: departamento, alumno, horas y tutor

    /**
     * Método que recoge el departamento del alumno que inicia sesión, y se encarga
     * de mandarlo a la parte de cliente, donde se gestiona qué hacer dependiendo de si el Departamento
     * tiene o no tiene valor.
     * @author Malena.
     */
    public function gestionarDepartamento(Request $req)
    {
        $dni_alumno = $req->get('dni');
        try {
            $departamento = FCT::select('departamento')
                ->where('fct.dni_alumno', '=', $dni_alumno)
                ->get();
            return response()->json($departamento, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error, el departamento no se ha enviado.'], 450);
        }
    }

    /**
     * Método que se encarga de recoger el valor del Departamento para añadirlo
     * a su campo correspondiente en la tabla FCT.
     * @author Malena.
     */
    public function addDepartamento(Request $req)
    {
        $dni_alumno = $req->get('dni');
        $departamento = $req->get('departamento');
        try {
            $departamento = FCT::where('dni_alumno', $dni_alumno)
                ->update(['departamento' => $departamento]);
            return response()->json(['message' => 'El departamento se ha insertado correctamente.'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error, el departamento no se ha insertado en la BBDD.'], 450);
        }
    }

    /**
     * Método que selecciona de la BBDD el nombre, los apellidos y la empresa asignada del alumno
     * que inicia sesión, para mostrarlo en la correspondiente interfaz.
     * @author Malena
     */
    public function devolverDatosAlumno(Request $req)
    {
        $dni_alumno = $req->get('dni');
        try {
            $datosAlumno = FCT::join('alumno', 'alumno.dni', '=', 'fct.dni_alumno')
                ->join('empresa', 'empresa.id', '=', 'fct.id_empresa')
                ->select('alumno.nombre AS nombre_alumno', 'alumno.apellidos AS apellidos_alumno', 'empresa.nombre AS nombre_empresa')
                ->where('alumno.dni', '=', $dni_alumno)
                ->get();

            return response()->json($datosAlumno, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error, los datos no se han enviado.'], 450);
        }
    }

    /**
     * Método que se encarga de sumar todas las horas del campo "tiempo_empleado" de la tabla Seguimiento,
     * del alumno que inicia sesión para mostrarlas en la interfaz.
     * @author Malena.
     */
    public function sumatorioHorasTotales(Request $req)
    {
        $dni_alumno = $req->get('dni');
        $horas = 0;

        $fct = $this->buscarId_fct($dni_alumno);
        $id_fct = $fct[0]->id;

        try {
            $horasTotales = Seguimiento::join('fct', 'seguimiento.id_fct', '=', 'fct.id')
                ->select(DB::raw('SUM( seguimiento.tiempo_empleado) AS horasSumadas'))
                ->where('fct.dni_alumno', '=', $dni_alumno)
                ->where('seguimiento.id_fct', '=', $id_fct)
                ->groupBy('fct.dni_alumno')
                ->get();

            /*Me saltaba un error al no encontrar jornadas en un alumno, y horasSumadas ser null,
            con este control de errores lo soluciono.*/
            if (count($horasTotales) != 0) {
                $horas = $horasTotales[0]->horasSumadas;
            }
            return response()->json($horas, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error, las hotas se han ido a la verga.'], 450);
        }
    }

    /**
     * Método que envia al cliente los datos del tutor al que está asociado el alumno
     * para poder mostrarlo en la interfaz, ademas de mandarle también el id_empresa
     * al que el alumno está asociado.
     * @author Malena
     */
    public function recogerTutorEmpresa(Request $req)
    {
        $dni_alumno = $req->get('dni');
        $dni_tutor = $this->sacarDniTutor($dni_alumno)->dni_tutor_empresa;
        try {
            $mail_tutor = Trabajador::join('rol_trabajador_asignado', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                ->whereIn('rol_trabajador_asignado.id_rol', array(2, 3))
                ->where('trabajador.dni', '=', $dni_tutor)
                ->select('trabajador.email AS email')
                ->first();

            $email_tutor = $mail_tutor->email;
            //Recojo el id_empresa:
            $id_empresa = $this->empresaAsignadaAlumno($dni_alumno);
            return response()->json([$email_tutor, $id_empresa], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error, los datos no se han enviado.'], 450);
        }
    }

    /**
     * Método que actualiza en la BBDD el tutor que el alumno ha elegido.
     * @author Malena
     */
    public function actualizarTutorEmpresa(Request $req)
    {
        try {
            $mail_tutor_nuevo = $req->get('mail_tutor_nuevo');
            $dni_alumno = $req->get('dni_alumno');
            $id_empresa = $this->empresaAsignadaAlumno($dni_alumno);

            //Tenemos el mail del nuevo tutor, tenemos que coger su dni para poder actualizarlo en la tabla FCT:
            $dni_tutor_nuevo = Trabajador::join('rol_trabajador_asignado', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                ->whereIn('rol_trabajador_asignado.id_rol', array(2, 3))
                ->where('trabajador.id_empresa', '=', $id_empresa)
                ->where('trabajador.email', '=', $mail_tutor_nuevo)
                ->select('trabajador.dni AS dni')
                ->first();

            Fct::where('dni_alumno', $dni_alumno)->update([
                'dni_tutor_empresa' => $dni_tutor_nuevo->dni,
            ]);
            return response()->json(['message' => 'Tutor actualizado correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error, el tutor no se ha actualizado.'], 450);
        }
    }

    #endregion
    /***********************************************************************/



    /***********************************************************************/
    #region Recoger alumnos asociados a un tutor
    /**
     * Función que recoge de la BBDD los alumnos que tienen un determinado
     * tutor.
     * @author Malena
     * @return $alumnosAsociados a x tutor
     */
    public function getAlumnosAsociados(Request $req)
    {
        //dni_tutor puede ser tanto de instituto como de empresa
        $dni = $req->dni_tutor;
        $esTutorEstudios = Profesor::where('dni', '=', $dni)
            ->select('dni')
            ->first();

        $alumnosAsociados = [];
        //Si encontramos un tutor_estudios con ese dni, sacamos sus alumnos asociados,
        //Sino, querrá decir que el dni pertenecerá a un tutor de la empresa, y se le mostrarán sus correspondientes alumnos.
        if ($esTutorEstudios != null) {
            $alumnosAsociados = Alumno::join('matricula', 'alumno.dni', '=', 'matricula.dni_alumno')
                ->join('grupo', 'grupo.cod', '=', 'matricula.cod_grupo')
                ->join('tutoria', 'tutoria.cod_grupo', '=', 'grupo.cod')
                ->where('tutoria.dni_profesor', '=', $dni)
                ->select('alumno.dni AS dni', 'alumno.nombre AS nombre', 'alumno.apellidos AS apellidos')
                ->get();
        } else {
            $alumnosAsociados = Alumno::join('fct', 'alumno.dni', '=', 'fct.dni_alumno')
                ->where('fct.dni_tutor_empresa', '=', $dni)
                ->select('alumno.dni AS dni', 'alumno.nombre AS nombre', 'alumno.apellidos AS apellidos')
                ->get();
        }

        return response()->json($alumnosAsociados, 200);
    }
    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Generación y descarga del Anexo III

    /**
     * Mètodo que genera el Anexo III con los datos necesarios extraídos de la BBDD.
     * @author Malena.
     */
    public function generarAnexo3(Request $req)
    {
        $dni_alumno = $req->get('dni');
        $id_quinto_dia = $req->get('id_quinto_dia');

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
        $jornadas = $this->las5UltimasJornadas($dni_alumno, $id_quinto_dia);

        //Construyo el array con todos los datos y ss correspondientes prefijos.
        $auxPrefijos = ['centro', 'alumno', 'tutor', 'familia_profesional', 'ciclo', 'empresa', 'tutor_empresa', 'fct'];
        $auxDatos = [$centro, $alumno, $tutor, $familia_profesional, $ciclo, $empresa, $tutor_empresa, $fct];
        $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);
        //Recorro las 5 jornadas, y les establezco su valor correspondiente en el documento.
        for ($i = 0; $i < count($jornadas); $i++) {
            $datos['jornada' . $i . '.actividades'] = $jornadas[$i]->actividades;
            $datos['jornada' . $i . '.tiempo_empleado'] = $jornadas[$i]->tiempo_empleado;
            $datos['jornada' . $i . '.observaciones'] = $jornadas[$i]->observaciones;
        }
        //Nombre de la plantilla:
        $nombrePlantilla = 'Anexo3';
        //La ruta donde se va a almacenar el documento:
        $rutaOrigen = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . $nombrePlantilla . '.docx';
        //Establezco la fecha para ponerlo en el nombre del documento:
        $fecha = Carbon::now();
        $fecha_doc = $fecha->day . '-' . AuxiliarParametros::MESES[$fecha->month] . '-' . $fecha->year % 100;
        //De momento, formare el nombre del documento con el dni del alumno + fecha.
        $nombre = $nombrePlantilla . '_' . $dni_alumno . '_' . $fecha_doc . '.docx';
        Auxiliar::existeCarpeta(public_path($dni_alumno . DIRECTORY_SEPARATOR . 'Anexo3'));
        $rutaDestino = $dni_alumno . DIRECTORY_SEPARATOR . 'Anexo3' . DIRECTORY_SEPARATOR . $nombre;
        //Anexo::create(['tipo_anexo' => 'Anexo3', 'ruta_anexo' => $rutaDestino]);
        //Creo la plantilla y la relleno con los valores establecidos anteriormente.
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);
        /*Cuando se genere un nuevo Word, las firmas de los 3 implicados se pondrán a 0 dado que se entiende que al generar un Word nuevo, es porque el
        alumno ha cambiado algún campo y hay que firmarlo y subirlo nuevo.*/
        $fct = $this->buscarId_fct($dni_alumno);
        $id_fct = $fct[0]->id;
        $cambiarFirmas = Semana::where('id_fct', '=', $id_fct)
            ->where('id_quinto_dia', '=', $id_quinto_dia)
            ->update([
                'firmado_alumno' => 0,
                'firmado_tutor_estudios' => 0,
                'firmado_tutor_empresa' => 0,
            ]);
        return response()->download(public_path($rutaDestino));
    }

    /**
     * Función que comprueba en la BBDD si existe una hoja de seguimiento
     * de una determinada semana
     * @author Malena
     * @return $ruta_hoja
     */
    public function hayDocumento(Request $req)
    {
        $ruta_hoja = Semana::where('id_fct', '=', $req->id_fct)
            ->where('id_quinto_dia', '=', $req->id_quinto_dia)
            ->select('ruta_hoja')
            ->first();
        return response()->json($ruta_hoja, 200);
    }

    /**
     * Función que envía al cliente la ruta de la hoja de seguimiento
     * de una determinada semana para descargarla.
     * @author Malena
     * @return Ruta $req->ruta_hoja
     */
    public function descargarAnexo3(Request $req)
    {
        return response()->download(public_path($req->ruta_hoja));
    }

    /**
     * Función que recoge todos los datos necesarios para poder subir una determinaa
     * hoja de seguimiento al servidor, guardándola en su correspondiente carpeta y
     * añadiendo la ruta en la BBDD.
     * @author Malena
     */
    public function subirAnexo3(Request $req)
    {
        try {
            $dni = $req->dni;
            $rutaDestino = public_path() . DIRECTORY_SEPARATOR . $dni . DIRECTORY_SEPARATOR . 'Anexo3' . DIRECTORY_SEPARATOR . 'Uploaded';
            if (strpos($req->file_name, ".pdf")) {
                $replaced = Str::replace('.pdf', '', $req->file_name);
            }
            $subida = Auxiliar::guardarFichero($rutaDestino, $replaced, $req->file);
            $ruta_hoja = $dni . DIRECTORY_SEPARATOR . 'Anexo3' . DIRECTORY_SEPARATOR . 'Uploaded' . DIRECTORY_SEPARATOR . $req->file_name;

            $rol_dni_alumno = Alumno::select('dni')
                ->where('dni', '=', $dni)
                ->first();

            if ($rol_dni_alumno == null) {
                $actualizarFirma = Semana::where('id_fct', '=', $req->id_fct)
                    ->where('id_quinto_dia', '=', $req->id_quinto_dia)
                    ->update([
                        'ruta_hoja' => $ruta_hoja,
                        'firmado_tutor_estudios' => 1
                    ]);
                //Vamos a poner en leido la notificacion de una semana en concreto porque el tutor del instituto ya lo ha firmado
                $sacarId_semana = Semana::where('id_fct', '=', $req->id_fct)
                    ->where('id_quinto_dia', '=', $req->id_quinto_dia)
                    ->select('id')
                    ->first();

                $notificacion_id_semana = Notificacion::where('semana', '=', $sacarId_semana->id)
                    ->update([
                        'leido' => 1,
                    ]);
            } else {
                //en este caso, firma el alumno y si ha marcado el check, firmará tambien el tutor de la empresa
                $actualizarFirma = Semana::where('id_fct', '=', $req->id_fct)
                    ->where('id_quinto_dia', '=', $req->id_quinto_dia)
                    ->update([
                        'ruta_hoja' => $ruta_hoja,
                        'firmado_alumno' => 1,
                        'firmado_tutor_empresa' => $req->firmado_tutor_empresa
                    ]);
            }
            return response()->json(['message' => 'Documento subido correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error, el documento no se ha subido.'], 450);
        }
    }

    /***********************************************************************/
    #region Funciones auxiliares para la generación del Anexo III
    /**
     * Método que recoge los campos necesarios del centro de estudios de la BBDD.
     * @return $centro.
     * @author Malena.
     */
    public function centroDelAlumno(string $dni_alumno)
    {
        $centro = CentroEstudios::join('matricula', 'centro_estudios.cod', '=', 'matricula.cod_centro')
            ->select('centro_estudios.cif AS cif', 'centro_estudios.nombre AS nombre')
            ->where('matricula.dni_alumno', '=', $dni_alumno)
            ->first();

        return $centro;
    }

    /**
     * Método que recoge el nombre del alumno.
     * Para futuro cambio, concatenar el nombre + apellidos.
     * @return $nombre
     * @author Malena.
     */
    public function getNombreAlumno(string $dni_alumno)
    {
        $nombre = Alumno::select('nombre', 'apellidos')
            ->where('dni', '=', $dni_alumno)
            ->first();

        return $nombre;
    }

    /**
     * Método que recoge el nombre del tutor del centro estudios que le corresponde al alumno.
     * Para futuro cambio, concatenar el nombre + apellidos.
     * @return @tutor
     * @author Malena
     */
    public function getNombreTutor(string $dni_alumno)
    {
        $tutor = Profesor::join('tutoria', 'profesor.dni', '=', 'tutoria.dni_profesor')
            ->join('grupo', 'tutoria.cod_grupo', '=', 'grupo.cod')
            ->join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
            ->where('matricula.dni_alumno', '=', $dni_alumno)
            ->select('profesor.nombre AS nombre', 'profesor.apellidos AS apellidos')
            ->first();

        return $tutor;
    }

    /**
     * Método que recoge la familia profesional del ciclo en el que está matriculado el alumno.
     * @return $familia_profesional
     * @author Malena
     */
    public function getFamiliaProfesional(string $dni_alumno)
    {
        $familia_profesional = FamiliaProfesional::join('grupo_familia', 'familia_profesional.id', '=', 'grupo_familia.id_familia')
            ->join('grupo', 'grupo_familia.cod_grupo', '=', 'grupo.cod')
            ->join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
            ->where('matricula.dni_alumno', '=', $dni_alumno)
            ->select('familia_profesional.descripcion AS descripcion')
            ->first();

        return $familia_profesional;
    }

    /**
     * Método que recoge el ciclo formativo en el que está matriculado el alumno.
     * @return $ciclo_formativo
     * @author Malena
     */
    public function getCicloFormativo(string $dni_alumno)
    {
        $ciclo_formativo = NivelEstudios::join('grupo', 'nivel_estudios.cod', '=', 'grupo.cod_nivel')
            ->join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
            ->where('matricula.dni_alumno', '=', $dni_alumno)
            ->select('nivel_estudios.cod AS cod_nivel', 'grupo.nombre_largo AS nombre')
            ->first();

        return $ciclo_formativo;
    }

    /**
     * Método que recoge el nombre de la empresa en la que está asociado el alumno.
     * @return $nombre_empresa
     * @author Malena
     */
    public function getNombreEmpresa(string $dni_alumno)
    {
        //Primero saco el curso academico:
        $curso = Auxiliar::obtenerCursoAcademico();

        //En la select incluyo al curso academico como otra select:
        $nombre_empresa = Empresa::join('fct', 'empresa.id', '=', 'fct.id_empresa')
            ->where('fct.curso_academico', '=', $curso)
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->select('empresa.nombre AS nombre')
            ->first();
        return $nombre_empresa;
    }

    /**
     * Método que recoge de la BBDD el nombre del tutor que tiene asignado el alumno en la empresa.
     * @return $tutor_empresa
     * @author Malena
     */
    public function getNombreTutorEmpresa(string $dni_alumno)
    {
        $tutor_empresa = Trabajador::join('fct', 'trabajador.dni', '=', 'fct.dni_tutor_empresa')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->select('trabajador.nombre AS nombre')
            ->first();

        return $tutor_empresa;
    }

    /**
     * Método que recoge los datos necesarios correspondientes a la tabla FCT.
     * @return $fct
     * @author Malena
     */
    public function getDatosFct(string $dni_alumno)
    {
        $fct = FCT::where('fct.dni_alumno', '=', $dni_alumno)
            ->select('fecha_ini AS fecha_ini', 'fecha_fin AS fecha_fin', 'departamento AS departamento', 'num_horas AS horas')
            ->first();

        return $fct;
    }

    /**
     * Método que recoge las últimas 5 jornadas para insertarlas en la tabla del Anexo III.
     * @return array $jornadas
     * @author Malena
     */
    public function las5UltimasJornadas(string $dni_alumno, $id_quinto_dia)
    {
        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;

        $quinto_dia = Seguimiento::where('id', '=', $id_quinto_dia)
            ->first();

        $jornadas = Seguimiento::join('fct', 'fct.id', '=', 'seguimiento.id_fct')
            ->select('seguimiento.id AS id', 'seguimiento.actividades AS actividades', 'seguimiento.observaciones AS observaciones', 'seguimiento.tiempo_empleado AS tiempo_empleado')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->where('fct.id_empresa', '=', $id_empresa)
            ->where('seguimiento.orden_jornada', '<=', $quinto_dia->orden_jornada)
            ->orderBy('seguimiento.orden_jornada', 'DESC')
            ->take(5)
            ->get();


        return $jornadas;
    }

    /**
     * Método que recoge la última jornada añadida.
     * @author Malena
     * @return $jornada
     */
    public function ultimaJornada($dni_alumno)
    {
        $fct = $this->buscarId_fct($dni_alumno);
        $id_empresa = $fct[0]->id_empresa;

        $jornada = Seguimiento::join('fct', 'fct.id', '=', 'seguimiento.id_fct')
            ->select('seguimiento.id AS id', 'seguimiento.actividades AS actividades', 'seguimiento.observaciones AS observaciones', 'seguimiento.tiempo_empleado AS tiempo_empleado')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->where('fct.id_empresa', '=', $id_empresa)
            ->orderBy('seguimiento.orden_jornada', 'DESC')
            ->take(1)
            ->get();


        return $jornada;
    }

    /**
     * Método para sacar el dni del tutor que tiene asignado el alumno.
     * @return tutor_empresa
     * @author Malena
     */
    public function sacarDniTutor(string $dni_alumno)
    {
        $tutor_empresa = Fct::where('dni_alumno', '=', $dni_alumno)
            ->select('dni_tutor_empresa')
            ->first();
        return $tutor_empresa;
    }


    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Acuerdo de confidencialidad - Anexo XV

    /**
     * Esta funcion nos permite rellenar el AnexoXV
     * @param Request $req, este req contiene el dni del alumno y el nombre del archivo que esta en base de datos, para
     * que coincida el nombre con el del documento que se va a crear
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public function rellenarAnexoXV(Request $req)
    {

        $fecha = Carbon::now();
        $dni_alumno = $req->get('dni');
        $dni_tutor = $this->getDniTutorDelAlumno($dni_alumno);
        $nombre_archivo = $req->get('cod_anexo');
        $nombre_alumno = $this->getNombreAlumno($dni_alumno);
        $nombre_ciclo = $this->getNombreCicloAlumno($dni_alumno);
        $centro_estudios = $this->getCentroEstudiosYLocalidad($dni_alumno);
        $familia_profesional = $this->getDescripcionFamiliaProfesional($nombre_ciclo[0]->nombre_ciclo);

        try {
            //ARCHIVO

            //Alumno
            $rutaOriginal = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . 'AnexoXV.docx';
            $rutaCarpeta = public_path($dni_alumno . DIRECTORY_SEPARATOR . 'AnexoXV');
            $rutaDestino = $dni_alumno  . DIRECTORY_SEPARATOR . 'AnexoXV' . DIRECTORY_SEPARATOR . $nombre_archivo;
            Auxiliar::existeCarpeta($rutaCarpeta);

            //Tutor
            $rutaCarpetaTutor = public_path($dni_tutor[0]->dni_profesor . DIRECTORY_SEPARATOR . 'AnexoXV');
            $rutaDestinoTutor = $dni_tutor[0]->dni_profesor . DIRECTORY_SEPARATOR . 'AnexoXV' . DIRECTORY_SEPARATOR . $nombre_archivo;
            Auxiliar::existeCarpeta($rutaCarpetaTutor);

            //Al haber llegado aeste punto, asumimos que el anexo se ha completado y por lo tanto, lo habilitamos
            Anexo::where('ruta_anexo', 'like', "%$nombre_archivo")->update([
                'habilitado' => 1,
            ]);

            //Añadimos el anexo a la base de datos con la ruta del profesor
            $existeAnexo = Anexo::where('tipo_anexo', '=', 'AnexoXV')->where('ruta_anexo', 'like', "$rutaDestinoTutor")->get();

            if (count($existeAnexo) == 0) {
                Anexo::create(['tipo_anexo' => 'AnexoXV', 'ruta_anexo' => $rutaDestinoTutor, 'habilitado' => 1]);
            }

            #region Relleno de datos en Word
            $auxPrefijos = ['alumno', 'ciclo', 'centro', 'familia_profesional'];
            $auxDatos = [$nombre_alumno, $nombre_ciclo[0], $centro_estudios[0], $familia_profesional[0]];
            $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

            $datos = $datos +  [
                'dia' => $fecha->day,
                'mes' => Parametros::MESES[$fecha->month],
                'year' => $fecha->year,
                'dni' => $dni_alumno,
                'curso' => '2º'

            ];

            $template = new TemplateProcessor($rutaOriginal);
            $template->setValues($datos);
            $template->saveAs($rutaDestino);
            $template = new TemplateProcessor($rutaOriginal);
            $template->setValues($datos);
            $template->saveAs($rutaDestinoTutor);
            #endregion

            return response()->download(public_path($rutaDestino));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error de ficheros: ' . $e
            ], 500);
        }
    }

    /**
     * Esta funcion es llamada por la función original subirAnexoEspecifico, como el Anexo15 tiene muchas mas cosas
     * teniendo en cuenta que necesitamos guardar todo en la base de datos, referenciando al tutor
     * y guardarlo tambien en la carpeta del tutor, esta función añade todo lo necesario para ello.
     * Para una explicación extensa de que hace subirAnexoXV acudir a subirAnexoEspecifico.
     *
     * @param [type] $dni el dni del alumno
     * @param [type] $tipoAnexo el tipo de anexo
     * @param [type] $nombreArchivo el nombre del archivo
     * @param [type] $fichero el fichero en cuestión que se va a subir
     * @param [type] $rutaCarpetaAlumno la ruta de la carpeta asociada al alumno $dniAlumno/AnexoXV en este ccaso
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public function subirAnexoXV($dni, $tipoAnexo, $nombreArchivo, $fichero, $rutaCarpetaAlumno)
    {
        $tutor = $this->getDniTutorDelAlumno($dni);
        $rutaCarpeta = $tutor[0]->dni_profesor . DIRECTORY_SEPARATOR . $tipoAnexo;

        try {

            $flujo = fopen($rutaCarpeta . DIRECTORY_SEPARATOR .  $nombreArchivo, 'wb');
            $flujoAux = fopen($rutaCarpetaAlumno . DIRECTORY_SEPARATOR .  $nombreArchivo, 'wb');

            //Dividimos el string en comas
            // $datos[ 0 ] == "data:type/extension;base64"
            // $datos[ 1 ] == <actual base64 file>

            $datos = explode(',', $fichero);

            if (count($datos) > 1) {
                fwrite($flujo, base64_decode($datos[1]));
                fwrite($flujoAux, base64_decode($datos[1]));
            } else {
                return false;
            }
            fclose($flujo);
            fclose($flujoAux);


            //Extension y archivo sin extensión
            $archivoNombreSinExtension = explode('.', $nombreArchivo);
            $extension = explode('/', mime_content_type($fichero))[1];

            //Alumno
            $rutaParaBBDAlumno = $rutaCarpetaAlumno . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaParaBBDDSinExtensionAlumno = $rutaCarpetaAlumno . DIRECTORY_SEPARATOR . $archivoNombreSinExtension[0];

            //Profesor
            $rutaParaBBDDProfesor = $rutaCarpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaParaBBDDSinExtension = $rutaCarpeta . DIRECTORY_SEPARATOR . $archivoNombreSinExtension[0];


            //Base de datos
            //Solo busco por una ruta, por que si este anexo existe para el profesor, es por que el alumno ya lo ha creado
            $existeAnexo = Anexo::where('tipo_anexo', '=', $tipoAnexo)->where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->get();


            if (count($existeAnexo) == 0) {
                Anexo::create(['tipo_anexo' => $tipoAnexo, 'ruta_anexo' => $rutaParaBBDDProfesor]); //Profesor
                $this->firmarAnexo($rutaParaBBDDProfesor, $extension);
            } else {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDDSinExtensionAlumno%")->update([ //Alumno
                    'ruta_anexo' => $rutaParaBBDAlumno,
                ]);
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->update([ //Profesor
                    'ruta_anexo' => $rutaParaBBDDProfesor,
                ]);

                $this->firmarAnexo($rutaParaBBDAlumno, $extension);
                $this->firmarAnexo($rutaParaBBDDProfesor, $extension);
            }

            //Lo ponemos con su nombre original, en un directorio que queramos
            //El anexoXV es especial, hay que almacenarlo en dos sitios
            $fichero->move(public_path($rutaCarpetaAlumno), $nombreArchivo);
            $fichero->move(public_path($rutaCarpeta), $nombreArchivo);
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function firmarAnexo($rutaParaBBDD, $extension)
    {
        if ($extension == 'pdf') {
            Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                'firmado_alumno' => 1,
            ]);
        } else {
            Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                'firmado_alumno' => 0,
            ]);
        }
    }


    /***********************************************************************/
    #region Funciones auxiliares para el Anexo XV

    /**
     * Esta funcion nos permite obtener la descripcion de la tabla familia profesional a través del
     * nombre del ciclo
     * @param [type] $nombreCiclo, es el nombre del ciclo
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function getDescripcionFamiliaProfesional($nombreCiclo)
    {

        $familia_profesional = GrupoFamilia::join('grupo', 'grupo.cod', '=', 'grupo_familia.cod_grupo')
            ->join('familia_profesional', 'familia_profesional.id', '=', 'grupo_familia.id_familia')
            ->select('familia_profesional.descripcion')
            ->where('grupo.nombre_ciclo', '=', $nombreCiclo)
            ->get();

        return $familia_profesional;
    }

    /**
     * Esta funcion nos permite obtener el nombre del ciclo al que pertenece el alumno
     * @param [type] $dni_alumno, es el dni del alumno
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function getNombreCicloAlumno($dni_alumno)
    {
        $nombre_ciclo = Grupo::join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
            ->select('grupo.nombre_ciclo')
            ->where('matricula.dni_alumno', '=', $dni_alumno)->get();

        return $nombre_ciclo;
    }

    public static function getDniTutorDelAlumno($dni_alumno)
    {

        $tutor = Grupo::join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
            ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
            ->select('tutoria.dni_profesor')
            ->where('matricula.dni_alumno', '=', $dni_alumno)
            ->get();

        return $tutor;
    }

    /**
     *Nos permite obtener el centro de estudios y la localidad al que este
     *pertenece a través del dni de un alumno
     * @param [type] $dni_alumno
     * @return void
     *@author Laura <lauramorenoramos97@gmail.com>
     */
    public static function getCentroEstudiosYLocalidad($dni_alumno)
    {

        $centro_estudios = Matricula::join('centro_estudios', 'centro_estudios.cod', '=', 'matricula.cod_centro')
            ->select('centro_estudios.nombre', 'centro_estudios.localidad')
            ->where('matricula.dni_alumno', '=', $dni_alumno)->get();

        return $centro_estudios;
    }

    #endregion
    /***********************************************************************/
    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Anexos Alumnos

    /**
     * Esta funcion nos permite ver los anexos de un alumno
     * @param Request $req, este req contiene el dni del alumno
     * @return void
     * @author Laura <lauramorenoramos97@gmail.com>
     */
    public function listaAnexosAlumno($dni_alumno)
    {
        $datos = array();

        // De momento solo es necesario para el anexo XV
        $this->elAlumnoTieneSusAnexosObligatorios($dni_alumno);

        $Anexos = Anexo::where('ruta_anexo', 'like', "$dni_alumno%")->get();

        foreach ($Anexos as $a) {
            //return response($Anexos);
            //Un anexo es habilitado si este esta relleno por completo
            if ($a->tipo_anexo == 'Anexo5' || $a->tipo_anexo == 'AnexoXV') {

                $anexoAux = explode('/', $a->ruta_anexo);

                if ($a->tipo_anexo == 'Anexo5') {
                    if (file_exists(public_path($a->ruta_anexo))) {
                        $datos[] = [
                            'nombre' => $a->tipo_anexo,
                            'relleno' => $a->habilitado,
                            'codigo' => $anexoAux[2],
                            'fecha' => $a->created_at
                        ];
                    }
                } else {
                    $datos[] = [
                        'nombre' => $a->tipo_anexo,
                        'relleno' => $a->habilitado,
                        'codigo' => $anexoAux[2],
                        'fecha' => $a->created_at
                    ];
                }
            }
        }
        return response()->json($datos, 200);
    }

    /**
     * Si el alumno no tiene los anexos obligatorios en base de datos, estos se crearan en ella como deshabilitados
     * , de manera que si el alumno acaba de entrar por primera vez a la aplicacion, le apareceran los anexos
     * que debería tener en su crud de anexos y le aparecera si estan o no rellenos (habilitados o deshabilitados)
     * una vez completados, o sea, rellenos por el usuario, se habilitaran.
     *
     * @param [type] $dni_alumno
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com
     */
    public function elAlumnoTieneSusAnexosObligatorios($dni_alumno)
    {

        $fecha = Carbon::now();

        //AnexoXV
        //comprobamos que el anexo no exista para añadirlo a la tabla, sino se duplicaran
        //los registros
        $existeAnexo = Anexo::where('tipo_anexo', '=', 'AnexoXV')->where('ruta_anexo', 'like', "%$dni_alumno%")->get();
        if (count($existeAnexo) == 0) {
            $AuxNombre = $dni_alumno . '_' . $fecha->year . '_.docx';
            $rutaDestino = $dni_alumno  . DIRECTORY_SEPARATOR . 'AnexoXV' . DIRECTORY_SEPARATOR . 'AnexoXV_' . $AuxNombre;
            Anexo::create(['tipo_anexo' => 'AnexoXV', 'ruta_anexo' => $rutaDestino, 'habilitado' => 0]);
        }
    }

    /**
     * Esta funcion permite descargar todos los anexos del crud de anexos del alumno
     * @param Request $val
     * @return void
     *@author LauraM <lauramorenoramos97@gmail.com>
     */
    public function descargarTodoAlumnos(Request $req)
    {
        $c = new ControladorTutorFCT();
        $AuxNombre = Str::random(7);
        $dni = $req->get('dni_alumno');
        $habilitado = 1;

        $nombreZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';
        $nombreZip = $c->montarZipCrud($dni, $nombreZip, $habilitado);

        return response()->download(public_path($nombreZip))->deleteFileAfterSend(true);
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Resumen de gastos del alumno - Anexo VI

    /***********************************************************************/
    #region CRUD Tickets transporte

    /**
     * Obtiene todos los datos para la pantalla de gestión de gastos, en el perfil de alumno:
     * - Objeto clase Gasto del alumno
     * - Lista de facturas de tranporte
     * - Lista de facturas de manutención
     * @param string $dni_alumno DNI del alumno
     * @return Response Respuesta con la información del alumno, según su DNI y el curso académico actual
     * @author David Sánchez Barragán
     */
    public function gestionGastosAlumno($dni_alumno)
    {
        $gasto = $this->obtenerGastoAlumnoPorDNIAlumno($dni_alumno);
        if ($gasto) {
            return response()->json($gasto, 200);
        } else {
            return response()->json([], 204);
        }
    }

    /**
     * Obtiene el registro correspondiente de la tabla Gasto según el DNI del alumno
     * @param string $dni_alumno DNI del alumno
     * @return Gasto Registro de la tabla correspondiente al alumno en el curso académico actual
     * @author David Sánchez Barragán
     */
    public function obtenerGastoAlumnoPorDNIAlumno($dni_alumno)
    {
        $gasto = Gasto::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
        ])->get()->first();
        if ($gasto) {
            if ($gasto->tipo_desplazamiento == "No aplica") {
                $gasto->facturasTransporte = [];
                $gasto->facturasManutencion = [];

                $gasto->sumatorio_gasto_vehiculo_privado = 0;
                $gasto->sumatorio_gasto_transporte_publico = 0;
                $gasto->sumatorio_gasto_manutencion = 0;
                $gasto->total_gastos = 0;
            } else {
                $gasto->facturasTransporte = FacturaTransporte::where([
                    ['dni_alumno', '=', $dni_alumno],
                    ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                ])->get();
                //Incluimos la URL de la foto del ticket de transporte
                foreach ($gasto->facturasTransporte as $factura) {
                    $factura->imagen_ticket = Auxiliar::obtenerURLServidor() . '/api/descargarImagenTicketTransporte/' . $factura->id . '/' . uniqid();
                }

                $gasto->facturasManutencion = FacturaManutencion::where([
                    ['dni_alumno', '=', $dni_alumno],
                    ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                ])->get();
                //Incluimos la URL de la foto del ticket de transporte
                foreach ($gasto->facturasManutencion as $factura) {
                    $factura->imagen_ticket = Auxiliar::obtenerURLServidor() . '/api/descargarImagenTicketManutencion/' . $factura->id . '/' . uniqid();
                }

                $gasto->sumatorio_gasto_vehiculo_privado = $this->calcularGastoVehiculoPrivado($gasto);
                $gasto->sumatorio_gasto_transporte_publico = $this->calcularGastoTransportePublico($dni_alumno);
                $gasto->sumatorio_gasto_manutencion = $this->calcularGastoManutencion($dni_alumno);
                $gasto->total_gastos = $gasto->sumatorio_gasto_vehiculo_privado + $gasto->sumatorio_gasto_transporte_publico + $gasto->sumatorio_gasto_manutencion;
            }
            //Incluido para controlar la gestión de los gastos del profesor
            $alumno = Alumno::where('dni', '=', $dni_alumno)->get()->first();
            $gasto->nombre_alumno = $alumno->nombre . ' ' . $alumno->apellidos;

            return $gasto;
        }

        return null;
    }




    /**
     * Actualiza la información en la tabla Gasto según el objeto recibido con la información
     * del cliente.
     * @param Request $r Request con forma de objeto Gasto
     * @return Response Respuesta HTTP estándar
     * @author David Sánchez Barragán
     */
    public function actualizarDatosGastoAlumno(Request $r)
    {
        Gasto::where([
            ['dni_alumno', '=', $r->dni_alumno],
            ['curso_academico', '=', $r->curso_academico]
        ])->update([
            'tipo_desplazamiento' => $this->obtenerTipoDesplazamiento($r->residencia_alumno, $r->ubicacion_centro_trabajo),
            'residencia_alumno' => $r->residencia_alumno,
            'ubicacion_centro_trabajo' => $r->ubicacion_centro_trabajo == null ? ' ' : $r->ubicacion_centro_trabajo,
            'distancia_centroEd_centroTra' => $r->distancia_centroEd_centroTra,
            'distancia_centroEd_residencia' => $r->distancia_centroEd_residencia,
            'distancia_centroTra_residencia' => $r->distancia_centroTra_residencia
        ]);

        return response()->json(['mensaje' => 'Gasto actualizado correctamente']);
    }

    /**
     * Actualiza el campo dias_transporte_privado del modelo Gasto
     * @param Request $r Request que incluye el dni del alumno a actualizar y el número de días
     * @return Response Código HTTP estándar
     * @author David Sánchez Barragán
     */
    public function actualizarDiasVehiculoPrivado(Request $r)
    {
        Gasto::where([
            ['dni_alumno', '=', $r->dni_alumno],
            ['curso_academico', '=', $r->curso_academico]
        ])->update([
            'dias_transporte_privado' => $r->dias_transporte_privado
        ]);

        return response()->json(['mensaje' => 'Gasto actualizado correctamente']);
    }

    /**
     * Introduce una factura de transporte en la tabla FacturaTransporte
     * @param Request $r Petición con la información de la factura de transporte
     * @return Response Respuesta HTTP estándar
     * @author David Sánchez Barragán
     */
    public function nuevaFacturaTransporte(Request $r)
    {
        $factura = FacturaTransporte::create([
            'dni_alumno' => $r->dni_alumno,
            'curso_academico' => Auxiliar::obtenerCursoAcademico(),
            'fecha' => $r->fecha,
            'importe' => $r->importe,
            'origen' => $r->origen,
            'destino' => $r->destino,
            'imagen_ticket' => ''
        ]);

        $imagen_ticket = '';
        $pathFoto = public_path() . DIRECTORY_SEPARATOR .  $r->dni_alumno;
        $nombreFichero = 'ticketTransporte' . $factura->id;
        if ($r->imagen_ticket != null) {
            $imagen_ticket = Auxiliar::guardarFichero($pathFoto, $nombreFichero, $r->imagen_ticket);
        }

        $factura->imagen_ticket = $imagen_ticket;

        $factura->save();

        return response()->json(['mensaje' => 'Factura actualizada correctamente']);
    }

    /**
     * Introduce una factura de manutención en la tabla FacturaManutencion
     * @param Request $r Petición con la información de la factura de manutención
     * @return Response Respuesta HTTP estándar
     * @author David Sánchez Barragán
     */
    public function nuevaFacturaManutencion(Request $r)
    {
        $factura = FacturaManutencion::create([
            'dni_alumno' => $r->dni_alumno,
            'curso_academico' => Auxiliar::obtenerCursoAcademico(),
            'fecha' => $r->fecha,
            'importe' => $r->importe,
            'imagen_ticket' => '',
        ]);

        $imagen_ticket = '';
        $pathFoto = public_path() . DIRECTORY_SEPARATOR .  $r->dni_alumno;
        $nombreFichero = 'ticketManutencion' . $factura->id;
        if ($r->imagen_ticket != null) {
            $imagen_ticket = Auxiliar::guardarFichero($pathFoto, $nombreFichero, $r->imagen_ticket);
        }

        $factura->imagen_ticket = $imagen_ticket;

        $factura->save();

        return response()->json(['mensaje' => 'Factura actualizada correctamente']);
    }

    /**
     * Actualización de los datos de la factura de transporte recibida por la Request
     * @param Request $r Petición con la información de la factura de transporte
     * @author David Sánchez Barragán
     */
    public function actualizarFacturaTransporte(Request $r)
    {
        $imagen_ticket = '';

        //Si la foto o el curriculum contienen su parte de URL, no se guardan en la base de datos;
        //se recoge entonces el path original que tuvieran
        if (!str_contains($r->imagen_ticket, "descargarImagenTicketTransporte")) {
            $imagen_ticket_anterior = FacturaTransporte::where('id', '=', $r->id)->get()->first()->imagen_ticket;
            if (strlen($imagen_ticket_anterior) != 0) {
                Auxiliar::borrarFichero($imagen_ticket_anterior);
            }
            $imagen_ticket = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni_alumno, 'ticketTransporte' . $r->id, $r->imagen_ticket);
        } else {
            $imagen_ticket = FacturaTransporte::where('id', '=', $r->id)->get()->first()->imagen_ticket;
        }

        FacturaTransporte::where([
            ['id', '=', $r->id]
        ])->update([
            'fecha' => $r->fecha,
            'importe' => $r->importe,
            'origen' => $r->origen,
            'destino' => $r->destino,
            'imagen_ticket' => $imagen_ticket == null ? ' ' : $imagen_ticket,
        ]);

        return response()->json(['mensaje' => 'Factura actualizada correctamente']);
    }

    /**
     * Actualización de los datos de la factura de manutención recibida por la Request
     * @param Request $r Petición con la información de la factura de manutención
     * @author David Sánchez Barragán
     */
    public function actualizarFacturaManutencion(Request $r)
    {
        $imagen_ticket = '';
        //Si la foto o el curriculum contienen su parte de URL, no se guardan en la base de datos;
        //se recoge entonces el path original que tuvieran
        if (!str_contains($r->imagen_ticket, "descargarImagenTicketManutencion")) {
            $imagen_ticket_anterior = FacturaManutencion::where('id', '=', $r->id)->get()->first()->imagen_ticket;
            if (strlen($imagen_ticket_anterior) != 0) {
                Auxiliar::borrarFichero($imagen_ticket_anterior);
            }
            $imagen_ticket = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni_alumno, 'ticketManutencion' . $r->id, $r->imagen_ticket);
        } else {
            $imagen_ticket = FacturaManutencion::where('id', '=', $r->id)->get()->first()->imagen_ticket;
        }

        FacturaManutencion::where([
            ['id', '=', $r->id]
        ])->update([
            'fecha' => $r->fecha,
            'importe' => $r->importe,
            'imagen_ticket' => $imagen_ticket == null ? ' ' : $imagen_ticket,
        ]);

        return response()->json(['mensaje' => 'Factura actualizada correctamente']);
    }

    /**
     * Elimina una factura de manutención según su ID en la tabla FacturaManutencion
     * @param $id ID de la factura a eliminar
     * @return Response Respuesta HTTP estándar
     * @author David Sánchez Barragán
     */
    public function eliminarFacturaManutencion($id)
    {
        try {
            FacturaManutencion::where('id', '=', $id)->delete();
            return response()->json(['mensaje' => 'Factura eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['mensaje' => 'Se ha producido un error'], 500);
        }
    }

    /**
     * Elimina una factura de transporte según su ID en la tabla FacturaTransporte
     * @param $id ID de la factura a eliminar
     * @return Response Respuesta HTTP estándar
     * @author David Sánchez Barragán
     */
    public function eliminarFacturaTransporte($id)
    {
        try {
            FacturaTransporte::where('id', '=', $id)->delete();
            return response()->json(['mensaje' => 'Factura eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['mensaje' => 'Se ha producido un error'], 500);
        }
    }
    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Funciones auxiliares CRUD Anexo VI:

    /**
     * Descarga la imagen del ticket de transporte
     * @param string $dni DNI del alumno del que se quiere obtener la la imagen del ticket de transporte
     * @param string $guid Universally Unique Identifier, utilizado para que en el cliente se detecte
     * el cambio de foto si se actualiza.
     * @return File Objeto File para que la foto sea accesible desde el atributo src en etiquetas img en lado cliente
     * @author David Sánchez Barragán
     */
    public function descargarImagenTicketTransporte($id, $guid)
    {
        $pathFoto = FacturaTransporte::where('id', '=', $id)->select('imagen_ticket')->get()->first()->imagen_ticket;
        if ($pathFoto) {
            return response()->file($pathFoto);
        } else {
            return response()->json(['mensaje' => 'Error, fichero no encontrado'], 404);
        }
    }

    /**
     * Descarga la imagen del ticket de manutención
     * @param string $dni DNI del alumno del que se quiere obtener la la imagen del ticket de manutención
     * @param string $guid Universally Unique Identifier, utilizado para que en el cliente se detecte
     * el cambio de foto si se actualiza.
     * @return File Objeto File para que la foto sea accesible desde el atributo src en etiquetas img en lado cliente
     * @author David Sánchez Barragán
     */
    public function descargarImagenTicketManutencion($id, $guid)
    {
        $pathFoto = FacturaManutencion::where('id', '=', $id)->select('imagen_ticket')->get()->first()->imagen_ticket;
        if ($pathFoto) {
            return response()->file($pathFoto);
        } else {
            return response()->json(['mensaje' => 'Error, fichero no encontrado'], 404);
        }
    }

    /**
     * Calcula el total del importe correspondiente al gasto de viajar en vehículo privado
     * @param Gasto $gasto Objeto de la tabla Gasto del que queramos calcular el importe
     * @return float Cálculo del importe correspondiente.
     * @author David Sánchez Barragán
     */
    public function calcularGastoVehiculoPrivado($gasto)
    {
        if (str_contains($gasto->ubicacion_centro_trabajo, 'Dentro')) {
            return 0;
        } else {
            if ($gasto->distancia_centroTra_residencia < $gasto->distancia_centroEd_residencia) {
                return 0;
            } else {
                if (str_contains($gasto->residencia_alumno, 'distinta')) {
                    return ($gasto->distancia_centroTra_residencia - $gasto->distancia_centroEd_residencia) * 2 * Parametros::COEFICIENTE_KM_VEHICULO_PRIVADO  * $gasto->dias_transporte_privado;
                } else {
                    return $gasto->distancia_centroEd_centroTra * 2 * Parametros::COEFICIENTE_KM_VEHICULO_PRIVADO * $gasto->dias_transporte_privado;
                }
            }
        }

        return false;
    }

    /**
     * Devuelve el total del importe de los tickets de transporte público
     * @param string $dni_alumno DNI del alumno
     * @return float Cálculo del importe correspondiente.
     * @author David Sánchez Barragán
     */
    public function calcularGastoTransportePublico($dni_alumno)
    {
        return FacturaTransporte::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
        ])->sum('importe');
    }

    /**
     * Devuelve el total del importe de los tickets de manutención
     * @param string $dni_alumno DNI del alumno.
     * @return float Cálculo del importe correspondiente.
     * @author David Sánchez Barragán
     */
    public function calcularGastoManutencion($dni_alumno)
    {
        return FacturaManutencion::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
        ])->sum('importe');
    }

    /**
     * Obtiene el tipo de desplazamiento (necesario para el Anexo VI)
     * según los parámetros recibidos en la función.
     * @param string $residencia_alumno Residencia del alumno (Localidad del centro educativo/Localidad distinta a la del centro educativo)
     * @param string $ubicacion_centro_trabajo Ubicación del centro de trabajo (Dentro del núcleo urbano/Fuera del núcleo urbano/En otra localidad)
     * @return string El tipo de desplazamiento-> Centro educativo: el centro de trabajo está a las afueras
     * o en otra localidad, Domicilio: el alumno no reside en la localidad del centro educativo,
     * No aplica-> no se tiene derecho a gastos de manutencion.
     * @author David Sánchez Barragán
     */
    public function obtenerTipoDesplazamiento($residencia_alumno, $ubicacion_centro_trabajo)
    {
        if (str_contains($residencia_alumno, 'Localidad del centro educativo')) {
            if (str_contains($ubicacion_centro_trabajo, 'Dentro')) {
                return "No aplica";
            } else {
                return "Centro educativo";
            }
        } else {
            return "Domicilio";
        }
    }
    #endregion
    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Confirmación de gastos - Anexo V

    /**
     * Actualiza los gastos en la base de datos y genera un Anexo V
     *
     * @param Request $req contiene los datos de los gastos de un alumno
     * @return Response JSON con la ruta del anexo generado (si todo ha ido bien), un mensaje y el código HTTP
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function confirmarGastos(Request $req)
    {
        try {
            $curso = $req->curso_academico;
            #region Actualización de gastos (confirmación)
            Gasto::where('dni_alumno', $req->dni_alumno)->where('curso_academico', $curso)
                ->update(['total_gastos' => $req->total_gastos]);
            #endregion
            #region Recolección de datos para rellenar el Anexo V
            // Extraigo todos los objetos que se necesitan en el Anexo V
            $controller = new ControladorTutorFCT();
            $alumno = Alumno::where('dni', $req->dni_alumno)->first();
            $fct = Fct::where('dni_alumno', $req->dni_alumno)->where('curso_academico', $curso)->first();
            $matricula = Matricula::where('dni_alumno', $alumno->dni)->where('curso_academico', $curso)->first();
            $centro = CentroEstudios::where('cod', $matricula->cod_centro)->first();
            $director = $controller->getDirectorCentroEstudios($centro->cod);
            $grupo = Grupo::where('cod', $matricula->cod_grupo)->first();
            $familia = FamiliaProfesional::find(GrupoFamilia::where('cod_grupo', $grupo->cod)->first()->id_familia);
            $tutor = Profesor::where('dni', Tutoria::where('cod_grupo', $grupo->cod)->where('curso_academico', $curso)->first()->dni_profesor)->first();
            $empresa = Empresa::find($fct->id_empresa);

            // Fabrico el vector con los datos y añado lo que falta
            $datos = Auxiliar::modelsToArray(
                [$alumno, $fct, $centro, $director, $grupo, $familia, $tutor, $empresa],
                ['alumno', 'fct', 'centro', 'director', 'grupo', 'familia', 'tutor', 'empresa']
            );
            $fecha = Carbon::now();
            $datos['dia'] = $fecha->day;
            $datos['mes'] = AuxiliarParametros::MESES[$fecha->month];
            $datos['anio'] = $fecha->year % 100;
            $datos['gasto.total_gastos'] = $req->total_gastos;
            #endregion
            #region Generación del Anexo V
            // Creo las rutas (y creo las carpetas) para el alumno y el tutor (que debe tener una copia)
            $nombrePlantilla = 'Anexo5';
            $rutaOrigen = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . $nombrePlantilla . '.docx';
            Auxiliar::existeCarpeta(public_path($alumno->dni . DIRECTORY_SEPARATOR . $nombrePlantilla));
            $rutaDestinoAlu = $alumno->dni . DIRECTORY_SEPARATOR . $nombrePlantilla . DIRECTORY_SEPARATOR . $nombrePlantilla . '_' . str_replace('/', '-', $curso) . '.docx';
            Auxiliar::existeCarpeta(public_path($tutor->dni . DIRECTORY_SEPARATOR . $nombrePlantilla));
            $rutaDestinoTutor = $tutor->dni . DIRECTORY_SEPARATOR . $nombrePlantilla . DIRECTORY_SEPARATOR . $nombrePlantilla . '_' . $alumno->dni . '_' . str_replace('/', '-', $curso) . '.docx';

            // Genero el Anexo V
            $template = new TemplateProcessor($rutaOrigen);
            $template->setValues($datos);
            $template->saveAs($rutaDestinoAlu);
            // Y lo copio al tutor
            copy($rutaDestinoAlu, $rutaDestinoTutor);
            // Por último, introduzco los registros en la tabla de anexos
            Anexo::where('ruta_anexo', 'like', explode('.', $rutaDestinoAlu)[0] . '%')->orWhere('ruta_anexo', 'like', explode('.', $rutaDestinoTutor)[0] . '%')->delete();
            Anexo::create([
                'tipo_anexo' => 'Anexo5',
                'ruta_anexo' => $rutaDestinoAlu
            ]);
            Anexo::create([
                'tipo_anexo' => 'Anexo5',
                'ruta_anexo' => $rutaDestinoTutor
            ]);
            #endregion
            return response()->json(['message' => 'Gastos confirmados', 'ruta_anexo' => $rutaDestinoAlu], 200);
        } catch (QueryException $ex) {
            return response()->json($ex->getMessage(), 400);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * Guarda el archivo que recibe en base64 en la ruta del alumno y de su tutor,
     * y marca los archivos como firmados
     *
     * @param Request $req contiene el archivo en base 64, el curso académico y el DNI del alumno
     * @return Response JSON con la respuesta del servidor, según haya resultado el proceso
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function subirAnexoV(Request $req)
    {
        try {
            // Variables básicas
            $dniAlu = $req->get('dni');
            $curso = $req->get('curso_academico');
            $dniTutor = $this->getTutorFromAlumnoCurso($dniAlu, $curso)->dni;

            // Guardo los archivos
            $carpetaAlu = $dniAlu . DIRECTORY_SEPARATOR . 'Anexo5';
            $carpetaTutor = $dniTutor . DIRECTORY_SEPARATOR . 'Anexo5';
            $archivoAlu = 'Anexo5_' . str_replace('/', '-', $curso);
            $archivoTutor = 'Anexo5_' . $dniAlu . '_' . str_replace('/', '-', $curso);
            $rutaAlu = Auxiliar::guardarFichero($carpetaAlu, $archivoAlu, $req->get('file'));
            $rutaTutor = Auxiliar::guardarFichero($carpetaTutor, $archivoTutor, $req->get('file'));

            // Hago el update en la base de datos
            Anexo::where('ruta_anexo', 'like', explode('.', $rutaAlu)[0] . '%')->update([
                'ruta_anexo' => $rutaAlu,
                'firmado_alumno' => 1
            ]);
            Anexo::where('ruta_anexo', 'like', explode('.', $rutaTutor)[0] . '%')->update([
                'ruta_anexo' => $rutaTutor,
                'firmado_alumno' => 1
            ]);

            return response()->json(['message' => 'Anexo firmado'], 200);
        } catch (QueryException $ex) {
            return response()->json($ex->errorInfo[2], 400);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * Obtiene el modelo del tutor a partir del DNI del alumno y un curso académico
     *
     * @param string $dni DNI de un alumno
     * @param string $curso Curso académico
     * @return Profesor Modelo con los datos del profesor
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    private function getTutorFromAlumnoCurso(string $dni, string $curso)
    {
        $matricula = Matricula::where('dni_alumno', $dni)->where('curso_academico', $curso)->first();
        $grupo = Grupo::where('cod', $matricula->cod_grupo)->first();
        $tutoria = Tutoria::where('curso_academico', $curso)->where('cod_grupo', $grupo->cod)->first();
        return Profesor::where('dni', $tutoria->dni_profesor)->first();
    }

    #endregion
    /***********************************************************************/
}
