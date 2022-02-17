<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControladorGenerico extends Controller
{

    public function login(Request $request)
    {
        $email = $request->get('email');
        $pass = $request->get('pass');

        error_log(print_r($email, true));
        error_log(print_r($pass, true));
    }
}
