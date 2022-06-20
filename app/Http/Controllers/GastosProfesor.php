<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
use App\Auxiliar\Parametros as AuxiliarParametros;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use App\Models\Fct;
use App\Auxiliar\Parametros;
use App\Models\Anexo;
use App\Models\AuxConvenio;
use App\Models\AuxCursoAcademico;
use App\Models\CentroEstudios;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\Gasto;
use App\Models\Matricula;
use App\Models\EmpresaGrupo;
use App\Models\FacturaManutencion;
use App\Models\FacturaTransporte;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use Carbon\Carbon;
use App\Models\Grupo;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\Tutoria;
use Faker\Core\Number;
use Illuminate\Database\QueryException;
use Mockery\Undefined;
use PhpParser\Node\Expr\Cast\Array_;
use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Facades\Hash;
use stdClass;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class GastosProfesor extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /***********************************************************************/
    #region Gestión de gastos de alumno en vista profesor

    /**
     * Calcula la suma de KM realizados por el alumno durante el trayecto (I/V)
     * en vehículo privado
     */
    public function calcularSumaKMVehiculoPrivado($gasto)
    {
        if (str_contains($gasto->ubicacion_centro_trabajo, 'Dentro')) {
            return 0;
        } else {
            if ($gasto->distancia_centroTra_residencia < $gasto->distancia_centroEd_residencia) {
                return 0;
            } else {
                if (str_contains($gasto->residencia_alumno, 'distinta')) {
                    return ($gasto->distancia_centroTra_residencia - $gasto->distancia_centroEd_residencia) * 2;
                } else {
                    return $gasto->distancia_centroEd_centroTra * 2;
                }
            }
        }

        return 0;
    }

    public function obtenerGestionGastosPorEmailTutor($email)
    {
        //Array de DNIS de alumnos tutorizados por la persona que ha iniciado sesión
        $dnisAlumnos = Profesor::join('tutoria', 'tutoria.dni_profesor', '=', 'profesor.dni')
            ->join('matricula', 'matricula.cod_grupo', '=', 'tutoria.cod_grupo')
            ->join('alumno', 'alumno.dni', '=', 'matricula.dni_alumno')
            ->join('gasto', 'gasto.dni_alumno', '=', 'alumno.dni')
            ->where([
                ['profesor.email', '=', $email],
                ['gasto.curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
            ])
            ->pluck('alumno.dni');

        $c = new ControladorAlumno();
        $gastos = new stdClass();
        $gastos->gastos = [];
        foreach ($dnisAlumnos as $dni) {
            $gastos->gastos[] = $c->obtenerGastoAlumnoPorDNIAlumno($dni);
        }

        $gastos->grupo = Profesor::join('tutoria', 'tutoria.dni_profesor', '=', 'profesor.dni')
            ->join('matricula', 'matricula.cod_grupo', '=', 'tutoria.cod_grupo')
            ->where([
                ['profesor.email', '=', $email],
                ['matricula.curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
            ])
            ->select('tutoria.cod_grupo')->first()->cod_grupo;

        $alumnosSinGasto = Profesor::join('tutoria', 'tutoria.dni_profesor', '=', 'profesor.dni')
            ->join('matricula', 'matricula.cod_grupo', '=', 'tutoria.cod_grupo')
            ->join('alumno', 'alumno.dni', '=', 'matricula.dni_alumno')
            ->where([
                ['profesor.email', '=', $email],
                ['matricula.curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
            ])
            ->whereNotIn('alumno.dni', $dnisAlumnos)
            ->pluck('alumno.dni')->toArray();

        $gastos->alumnosSinGasto = Alumno::whereIn('dni', $alumnosSinGasto)->get();

        return $gastos;
    }


    public function gestionGastosProfesor(Request $r)
    {
        try {
            $gastos = $this->obtenerGestionGastosPorEmailTutor($r->user()->email);
            return response()->json($gastos, 200);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 400);
        }
    }


    public function eliminarAlumnoDeGastos($dni_alumno)
    {
        Gasto::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()],
        ])->delete();
        FacturaManutencion::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()],
        ])->delete();
        FacturaTransporte::where([
            ['dni_alumno', '=', $dni_alumno],
            ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()],
        ])->delete();
        return response()->json(['mensaje' => 'Alumno eliminado correctamente'], 200);
    }

    public function nuevoAlumnoGestionGastos(Request $r)
    {
        try {
            Gasto::create([
                'dni_alumno' => $r->dni,
                'curso_academico' => Auxiliar::obtenerCursoAcademico(),
                'tipo_desplazamiento' => '',
                'total_gastos' => 0,
                'residencia_alumno' => '',
                'ubicacion_centro_trabajo' => '',
                'distancia_centroEd_centroTra' => 0,
                'distancia_centroEd_residencia' => 0,
                'distancia_centroTra_residencia' => 0,
                'dias_transporte_privado' => 0
            ]);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error'], 500);
        }

        return response()->json(['mensaje' => 'Creado correctamente'], 201);
    }

    public function descargarAnexoVI(Request $r)
    {
        $rutaFichero = $this->generarAnexoVI($r->user()->email);
        if ($rutaFichero) {
            return response()->download($rutaFichero);
        } else {
            return response()->json(['mensaje' => 'No se ha podido descargar el fichero'], 400);
        }
    }

    public function generarAnexoVI($email)
    {
        $dniTutor = Profesor::where('email', '=', $email)->get()->first()->dni;
        $pathAnexoVI = public_path() . DIRECTORY_SEPARATOR . $dniTutor . DIRECTORY_SEPARATOR . 'Anexo6' . DIRECTORY_SEPARATOR;
        $pathPlantillaAnexo = public_path() . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . 'Anexo6.xlsx';

        $alumnosTutor = $this->obtenerGestionGastosPorEmailTutor($email);
        for ($i = 0; $i < ceil(count($alumnosTutor->gastos) / 17); $i++) {
            //Cogemos el array de alummos de 17 en 17, para ir creando
            //tantos libros de Excel como necesitemos
            $gastoAlumnos = array_slice($alumnosTutor->gastos, $i, 17);
            $reader = new ReaderXlsx();
            $libro = $reader->load($pathPlantillaAnexo);
            $tabla = $libro->getActiveSheet();

            //Cabecera de la tabla Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
            $cabecera = Profesor::join('centro_estudios', 'profesor.cod_centro_estudios', '=', 'centro_estudios.cod')
                ->join('tutoria', 'tutoria.dni_profesor', '=', 'profesor.dni')
                ->join('grupo', 'tutoria.cod_grupo', '=', 'grupo.cod')
                ->where('profesor.dni', '=', '20a')
                ->select('centro_estudios.nombre as nombreCentro', 'profesor.nombre', 'profesor.apellidos', 'grupo.nombre_ciclo', 'centro_estudios.localidad', 'centro_estudios.cod', 'centro_estudios.email')
                ->get()->first();

            $periodo = Auxiliar::obtenerCursoAcademico();
            $fecha = date("d/m/Y");
            $horas = '400';

            $tabla->setCellValue('A7', 'CENTRO DOCENTE: ' . $cabecera->nombreCentro);
            $tabla->setCellValue('A8', 'TUTOR O TUTORA: ' . $cabecera->nombre . ' ' . $cabecera->apellidos);
            $tabla->setCellValue('B9', $cabecera->nombre_ciclo);
            $tabla->setCellValue('F7', $cabecera->localidad);
            $tabla->setCellValue('J7', $cabecera->cod);
            $tabla->setCellValue('I8', $periodo);
            $tabla->setCellValue('K8', $fecha);
            $tabla->setCellValue('F9', $cabecera->email);
            $tabla->setCellValue('J9', $horas);



            //Cuerpo de la tabla
            $fila = 14;
            foreach ($gastoAlumnos as $gasto) {
                $tabla->setCellValue('A' . $fila, $gasto->nombre_alumno);
                $tabla->setCellValue(($gasto->tipo_desplazamiento == 'Domicilio' ? 'D' : 'C') . $fila, '   x   ');
                $tabla->setCellValue('E' . $fila, $gasto->sumatorio_gasto_transporte_publico / count($gasto->facturasTransporte));
                $tabla->setCellValue('F' . $fila, count($gasto->facturasTransporte));
                $tabla->setCellValue('G' . $fila, $this->calcularSumaKMVehiculoPrivado($gasto));
                $tabla->setCellValue('H' . $fila, $gasto->dias_transporte_privado);
                $tabla->setCellValue('I' . $fila, $gasto->sumatorio_gasto_vehiculo_privado);
                $tabla->setCellValue('J' . $fila, $gasto->sumatorio_gasto_vehiculo_privado + $gasto->sumatorio_gasto_transporte_publico);
                $tabla->setCellValue('K' . $fila, $gasto->sumatorio_gasto_manutencion);
                $tabla->setCellValue('L' . $fila, $gasto->total_gastos);
                $fila++;
            }

            Auxiliar::existeCarpeta($pathAnexoVI);
            $writer = new WriterXlsx($libro);
            $writer->save($pathAnexoVI . 'Anexo6_' . $i . '.xlsx');
        }

        $rutaDevolver = $pathAnexoVI . 'Anexo6_0.xlsx';

        //Si se ha generado más de un fichero, los comprimimos
        if (count(glob($pathAnexoVI . '{*.xlsx}', GLOB_BRACE)) > 1) {
            $rutaRelativaAnexoVI = $dniTutor . DIRECTORY_SEPARATOR . 'Anexo6';
            $rutaZIP = $dniTutor . DIRECTORY_SEPARATOR . 'Anexo6' . DIRECTORY_SEPARATOR . 'Anexo6.zip';
            $this->montarZip($rutaRelativaAnexoVI, new ZipArchive(), $rutaZIP);

            foreach (glob($pathAnexoVI . '{*.xlsx/*.zip}', GLOB_BRACE) as $a) {
                if (is_file($a)) {
                    unlink($a);
                }
            }

            $rutaDevolver = public_path($rutaZIP);
        }

        return $rutaDevolver;
    }

    #endregion
    /***********************************************************************/

}
