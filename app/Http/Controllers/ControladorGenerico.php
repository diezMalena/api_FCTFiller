<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Matricula;
use App\Models\Ciudad;
use App\Models\Notificacion;
use App\Models\Semana;
use App\Models\Empresa;
use App\Models\FamiliaProfesional;
use App\Models\Grupo;
use App\Models\GrupoFamilia;
use App\Models\Profesor;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ControladorGenerico extends Controller
{

    /***********************************************************************/
    #region Autenticación

    /**
     * Coteja los datos de email y contraseña del login con los de la base de datos,
     * devolviendo el modelo del usuario con sus roles construidos (si los tuviera) si es correcto
     * y un código estándar http según el resultado
     *
     * @param Request $req Los datos del login (email y password)
     * @return Response Respuesta JSON que contiene un mensaje, un código http y, si el login es correcto, un modelo usuario
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function login(Request $req)
    {
        $loginData = $req->all();
        if (!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Login incorrecto'], 400);
        } else {
            $user = auth()->user();
            $token = $user->createToken('authToken')->accessToken;
            $usuario = Auxiliar::getDatosUsuario($user);
            // Cambio añadido para rescatar el código del centro del alumno existente en tabla matrícula. Hasta ahora solo se rescataba el del profesor.
            if ($user->tipo == 'alumno') {
                $matricula = Matricula::where('dni_alumno', '=', $usuario->dni)
                ->select(['*'])
                ->first();
                if ($matricula){
                    $usuario->cod_centro=$matricula->cod_centro;
                    $usuario->cod_grupo=$matricula->cod_grupo;
                    $usuario->curso_academico=$matricula->curso_academico;
                }
            }
            return response()->json([
                'usuario' => $usuario,
                'access_token' => $token,
                'message' => 'Login correcto'
            ], 200);
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Selects genéricas

    /***********************************************************************/
    #region Provincias y localidades

    /**
     * Obtiene un listado de provincias
     * @return Response objeto JSON con el listado de provincias
     * @author David Sánchez Barragán
     */
    public function listarProvincias()
    {
        $listado = Ciudad::distinct()->orderBy('provincia', 'asc')->get('provincia')->pluck('provincia');
        return response()->json($listado, 200);
    }

    /**
     * Obtiene un listado de ciudades
     * @param string $provincia provincia con la que se filtra la búsqueda
     * @return Response objeto JSON con el listado de ciudades
     * @author David Sánchez Barragán
     */
    public function listarCiudades($provincia)
    {
        $listado = Ciudad::where('provincia', $provincia)->distinct()->orderBy('ciudad', 'asc')->get(['ciudad'])->pluck('ciudad');
        return response()->json($listado, 200);
    }

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Gestión de Notificaciones

    /**
     * Genera la notificación del Anexo III
     * @author Malena
     */
    public function generarNotificaciones(Request $req)
    {
        $email = $req->get('email');
        $dni = $req->get('dni');
        $esProfesor = Profesor::where('email', '=', $email)
            ->select('email')
            ->first();
        //Si el correo recibido lo encontramos entre los profesores
        if ($esProfesor != null) {
            $datos = Semana::join('fct', 'semana.id_fct', '=', 'fct.id')
                ->join('matricula', 'fct.dni_alumno', '=', 'matricula.dni_alumno')
                ->join('alumno', 'alumno.dni', '=', 'matricula.dni_alumno')
                ->join('tutoria', 'matricula.cod_grupo', '=', 'tutoria.cod_grupo')
                ->where('semana.firmado_alumno', '=', 1)
                ->where('semana.firmado_tutor_estudios', '=', 0)
                ->select('alumno.nombre AS nombre_alumno', 'alumno.apellidos AS apellidos_alumno', 'tutoria.dni_profesor AS dni_profesor', 'semana.id AS id_semana')
                ->get();
            for ($i = 0; $i < count($datos); $i++) {
                if ($datos[$i]->dni_profesor == $dni) {
                    //Vamos a comprobar que la notificación para esa semana aún no se ha generado:
                    $estaNotifSemana = Notificacion::where('semana', '=', $datos[$i]->id_semana)
                        ->select('*')
                        ->first();
                    //Si la semana aún no tiene notificacion, se inserta:
                    if ($estaNotifSemana == null) {
                        $introducirNotificacion = Notificacion::create([
                            'email' => $email,
                            'mensaje' => 'Ya puedes firmar la hoja de seguimiento de ' . $datos[$i]->nombre_alumno . ' ' . $datos[$i]->apellidos_alumno . '.',
                            'leido' => 0,
                            'semana' => $datos[$i]->id_semana
                        ]);
                        /*En el caso de que el alumno genere un nuevo documento y las firmas se vuelvan a colocar en 0, queremos que también vuelva a aparecer
                        una nueva notificación porque hay que volver a firmar el documento que ha generado nuevo el alumno. */
                    } else {
                        //Contando con que esa semana ya tiene notificación, vamos a ponerla en leido a 0 para que se vuelva a mostrar:
                        $mostrarNotificacion = Notificacion::where('semana', '=', $datos[$i]->id_semana)
                            ->update([
                                'leido' => 0,
                            ]);
                    }
                }
            }
        } else {
        }
        return response()->json(['message' => 'Notificaciones generadas.'], 200);
    }

    /**
     * Función que recoge las notificaciones del user que no esten leídas
     * @author Malena
     * @return $notificaciones
     */
    public function getNotificacionesHeader(Request $req)
    {
        $email = $req->get('email');
        $dni = $req->get('dni');
        //Recogemos las notificaciones de esta persona que no estén leidas:
        $notificaciones = Notificacion::where('email', '=', $email)
            ->where('leido','=', 0)
            ->select('*')
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($notificaciones, 200);
    }

    /**
     * Función que realiza un count del total de las notificaciones
     * no leídas por el user.
     * @author Malena
     * @return $count de notificaciones
     */
    public function countNotificaciones(Request $req)
    {
        $email = $req->get('email');
        $count = Notificacion::where('email', '=', $email)
            ->where('leido', '=', 0)
            ->select('*')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->count();
        return response()->json($count, 200);
    }

    #endregion
    /***********************************************************************/



    /***********************************************************************/
    #region Ciclos formativos y familias profesionales

    /**
     * Devuelve todas las familias profesionales registradas en la base de datos
     *
     * @return Response JSON con un array de familias profesionales
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function getFamiliasProfesionales()
    {
        try {
            if ($familias = FamiliaProfesional::all()) {
                return response()->json($familias, 200);
            } else {
                return response()->json(['message' => 'Sin contenido'], 204);
            }
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    /**
     * Devuelve en una response los ciclos con la información de sus familias profesionales,
     * filtrados por las mismas si se les pasa como argumento su ID
     *
     * @param BigInteger|null $familia ID de la familia profesional por la que se filtra
     * @return Response JSON con array de ciclos con sus familias integradas
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function getCiclos($familia = null)
    {
        try {
            if ($familia) {
                $ciclos = Grupo::whereIn('cod', GrupoFamilia::select('cod_grupo')->where('id_familia', $familia)->get())->get();
            } else {
                $ciclos = Grupo::all();
            }
            foreach ($ciclos as $ciclo) {
                $ciclo->familias = FamiliaProfesional::whereIn('id', GrupoFamilia::select('id_familia')->where('cod_grupo', $ciclo->cod)->get())->get();
            }
            return response()->json($ciclos, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Auxiliares

    /**
     * Comprueba que un registro está duplicado en la base de datos
     *
     * @param string $elemento nombre de la tabla de la que se hace comprobación
     * @param string $campo nombre del campo con el que se hace la comprobación
     * @param string $valor valor del campo que se comprueba
     * @return boolean true si el registro está duplicado, false si es único
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function checkDuplicate(string $elemento, string $campo, string $valor)
    {
        $duplicado = false;
        try {
            switch ($elemento) {
                case 'empresa':
                    $duplicado = Empresa::where($campo, $valor)->count() != 0;
                    break;
                case 'trabajador':
                    $duplicado = Trabajador::where($campo, $valor)->count() != 0;
                    break;
            }
            return response()->json($duplicado, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Error en la comprobación'], 500);
        }
    }

    /**
     * Descarga cualquier anexo a partir de la ruta que se le envía desde el cliente
     *
     * @param Request $req contiene la ruta del anexo
     * @return Response señal de descarga o 404, si no se encuentra el archivo
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function descargarAnexoRuta(Request $req)
    {
        $ruta_anexo = $req->get('ruta');
        if (file_exists($ruta_anexo)) {
            return response()->download($ruta_anexo);
        } else {
            return response()->json(['message' => 'Not Found'], 404);
        }
    }

    #endregion
    /***********************************************************************/
}
