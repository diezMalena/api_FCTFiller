<?php

namespace App\Http\Controllers;

use App\Models\Usuario_view;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ControladorGenerico extends Controller
{

    public function login(Request $request)
    {
        $email = $request->get('email');
        error_log(print_r($email, true));
        $pass = $request->get('pass');
        error_log(print_r($pass, true));
        $hash_pass = Hash::make($pass);
        error_log(print_r($hash_pass, true));
        $usuario = Usuario_view::where(['email', '=', $email], ['password', '=', $pass])
        ->select()
        ->get();
        error_log($usuario);
        return response()->json($usuario, 200);

    }
}
