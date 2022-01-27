<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\AlumnoCurso;
use App\Models\Curso;
use App\Models\Empresa;
use App\Models\EmpresaAlumno;
use App\Models\EmpresaCurso;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\DB;

class ControladorTutorFCT extends Controller
{
    //
    /**
     *  Esta función se encarga de coger los datos dni y nombre de los
     *  alumnos que no están asociados a ninguna empresa
     *  asignados al dni del tutor que recibimos como parámetro.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarAlumnosSinEmpresa(string $dni)
    {
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
    }
    /**
     *  Esta función se encarga de coger el nombre del ciclo a partir del dni del tutor.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarNombreCiclo(string $dni)
    {
        $quer = 'select ciclo.nombre from curso, ciclo where'
            . ' curso.dni_tutor = ?'
            . ' AND curso.cod_ciclo = ciclo.cod_ciclo';
        $nombre = DB::select($quer, [$dni]);
        return response()->json($nombre, 200);
    }
    /*
    public function solicitarAlumnosSinEmpresa(string $dni)
    {
        $codCurso = Curso::select('cod_curso')->where([
            ['dni_tutor', $dni],
        ])->get();
        $dniAlumno = AlumnoCurso::select('dni')->whereIn(
            'cod_curso',
            $codCurso
        )->get();
        $dniAlumnoEmpresa = EmpresaAlumno::select('dni_alumno')->whereIn(
            'dni_alumno',
            $dniAlumno
        )->get();
        $dniAlumnoNoEmpresa = array();
        foreach ($dniAlumno as $key => $value) {
            $encontrado = false;
            foreach ($dniAlumnoEmpresa as $key2 => $value2) {
                if ($value['dni'] == $value2['dni_alumno']) {
                    $encontrado = true;
                }
            }
            if (!$encontrado) {
                array_push($dniAlumnoNoEmpresa, $value['dni']);
            }
        }
        $alumnos = Alumno::select('dni', 'nombre')->whereIn(
            'dni',
            $dniAlumnoNoEmpresa
        )->get();
        return response()->json($alumnos, 200);
    }
    */
    /**
     *  Esta función se encarga de coger las empresas que solicitan el curso que está tutorizando
     *  el profesor del que recibimos el dni, y dentro de esas empresas hay un array de alumnos que están
     *  ya asociados a una empresa.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarEmpresasConAlumnos(string $dni)
    {
        $quer = 'select empresa.id, empresa.nombre from empresa, curso, empresa_curso'
            . ' where curso.cod_curso = empresa_curso.cod_curso'
            . ' AND empresa_curso.id_empresa = empresa.id'
            . ' AND curso.dni_tutor = ?';
        $empresas = DB::select($quer, [$dni]);
        foreach ($empresas as $empresa) {
            $quer = 'select DISTINCT alumno.nombre, alumno.dni from alumno, alumno_curso, empresa_alumno, empresa, curso'
                . ' where curso.cod_curso = alumno_curso.cod_curso'
                . ' AND alumno_curso.dni = alumno.dni'
                . ' AND alumno.dni = empresa_alumno.dni_alumno'
                . ' AND curso.dni_tutor = ?'
                . ' AND empresa_alumno.id_empresa = ?';
                $empresa->alumnos = DB::select($quer, [$dni, $empresa->id]);
        }

        return response()->json($empresas, 200);
    }
    /**
     *  Esta función se encarga de actualizar la empresa a la que están asignados
     *  los alumnos.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com>
     *  @param $empresas son las empresas
     */
    public function actualizarEmpresaAsignadaAlumno(Request $request)
    {
        dd($request);
        return response()->json($request, 200);
    }
}
