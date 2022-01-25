<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Empresa;
use App\Models\EmpresaCurso;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;

class ControladorTutorFCT extends Controller
{
    //
    /**
     *  Esta función se encarga de coger los datos dni y nombre de los
     *  alumnos asignados al dni del tutor que recibimos como parámetro.
     *
     *  @author
     *  @param $dni es el dni del tutor
     */
    public function solicitarAlumnosSinEmpresa($dni){
        $cod_curso = Curso::select('cod_curso')->where([
            ['dni_tutor', $dni],
        ])->get();
        $alumnos = Alumno::select('dni','nombre')->where([
            ['cod_curso', $cod_curso],
        ])->get();
        return response()->json($alumnos, 200);
    }
    /**
     *  Esta función se encarga de coger los datos dni, nombre y cif de los
     *  alumnos asignados al dni del tutor que recibimos como parámetro.
     *
     *  @author
     *  @param $dni es el dni del tutor
     */
    public function solicitarEmpresas($dni){
        $cod_curso = Curso::select('cod_curso')->where([
            ['dni_tutor', $dni],
        ])->get();
        $cif = EmpresaCurso::select('cif_empresa')->where([
            ['cod_curso', $cod_curso],
        ])->get();
        $empresas = Empresa::select('nombre','cif')->where([
            ['cif', $cif],
        ])->get();
        return response()->json($empresas, 200);
    }
    /**
     *  Esta función se encarga de actualizar la empresa a la que están asignados
     *  los alumnos.
     *
     *  @author
     *  @param $empresas son las empresas
     */
    public function actualizarEmpresaAsignadaAlumno($empresas){

        foreach ($empresas as $empresa => $value) {
            # code...
        }

        return response()->json($empresas, 200);
    }
}
