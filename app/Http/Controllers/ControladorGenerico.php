<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Usuario_view;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class ControladorGenerico extends Controller
{

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
                // $usuario->token = auth()->user()->createToken('authToken')->accessToken;
                return response()->json($usuario, 200);
            } else {
                return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
            }
        } else {
            return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
        }

    }



}
