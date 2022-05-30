<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Ciudad;
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

    #endregion
    /***********************************************************************/
}
