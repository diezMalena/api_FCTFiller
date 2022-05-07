<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Ciudad;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
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
}
