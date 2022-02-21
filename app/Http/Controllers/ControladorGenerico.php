<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Usuario_view;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ControladorGenerico extends Controller
{

    public function login(Request $request)
    {
        $email = $request->get('email');
        $pass = $request->get('pass');
        $quer = 'select * from usuarios_view'
            . ' where email = ?';
        $usuario_view = DB::select($quer, [$email]);
        error_log(print_r($usuario_view, true));
        if (count($usuario_view) > 0) {
            $usuario_view = $usuario_view[0];
            $ckPass = Hash::check($pass, $usuario_view->password);
            if ($ckPass) {
                $usuario = Auxiliar::getDatosUsuario($usuario_view);
                return response()->json($usuario, 200);
            } else {
                return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
            }
        } else {
            return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
        }


    }



}
