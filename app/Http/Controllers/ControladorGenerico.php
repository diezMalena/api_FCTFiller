<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControladorGenerico extends Controller
{

    public function login(Request $request)
    {

        $email = $request->get('email');
        $pass = $request->get('pass');

        $quer = 'select alumno.nombre, alumno.dni from alumno, curso, alumno_curso where alumno.dni not in'
            . ' (select alumno.dni from alumno, curso, alumno_curso, empresa_alumno'
            . ' WHERE curso.cod_curso = alumno_curso.cod_curso'
            . ' AND alumno.dni = alumno_curso.dni'
            . ' AND curso.dni_tutor = ?'
            . ' AND alumno_curso.dni = empresa_alumno.dni_alumno)'
            . ' AND alumno.dni = alumno_curso.dni'
            . ' AND alumno_curso.cod_curso = curso.cod_curso'
            . ' AND alumno.va_a_fct != 0'
            . ' AND curso.dni_tutor = ?';
        $alumnos = DB::select($quer, [$dni, $dni]);
        return response()->json($alumnos, 200);
        error_log(print_r($email, true));
        error_log(print_r($pass, true));
    }
}
