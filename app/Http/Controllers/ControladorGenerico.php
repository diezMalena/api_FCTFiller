<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ControladorGenerico extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Autenticación
    |--------------------------------------------------------------------------
    */

    /**
     * Extrae de una vista los datos del usuario que ha introducido el correo y contraseña,
     * comprueba que ese email existe en la base de datos y después compara que la contraseña
     * que ha introducido el usuario coincida con la contraseña asociada a ese email.
     * Si todo eso es correcto llama a la función de obtener sus datos y creal el token,
     * si no, devuelve un error.
     *
     * @param int $usuario array con los datos del usuario
     * @author alvaro <alvarosantosmartin6@gmail.com>
     */
    public function login(Request $request)
    {
        //Extraigo los campos
        $email = $request->get('email');
        $pass = $request->get('pass');
        //Hago la query
        $quer = 'select * from usuarios_view'
            . ' where email = ?';
        $usuario_view = DB::select($quer, [$email]);
        // error_log(print_r($usuario_view, true));
        if (count($usuario_view) > 0) {
            $usuario_view = $usuario_view[0];
            $ckPass = Hash::check($pass, $usuario_view->password);
            if ($ckPass) {
                $usuario = Auxiliar::getDatosUsuario($usuario_view);
                //DSB Cambio 10-03-2022: Añadido codigo de centro de estudios
                $usuario->cod_centro = Auxiliar::obtenerCentroPorDNIProfesor($usuario->dni);
                // $usuario->token = auth()->user()->createToken('authToken')->accessToken;
                return response()->json($usuario, 200);
            } else {
                return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
            }
        } else {
            return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Selects genéricas
    |--------------------------------------------------------------------------
    */

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
}
