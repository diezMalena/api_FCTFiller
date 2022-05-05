<?php

namespace App\Http\Controllers\ControladorEmpresas;
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
use App\Models\Matricula;
use App\Models\EmpresaGrupo;
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
use Illuminate\Support\Str;
use App\Models\Tutoria;
use Faker\Core\Number;
use Mockery\Undefined;
use PhpParser\Node\Expr\Cast\Array_;
use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Facades\Hash;

class ControladorResponCentro extends Controller
{
  /***********************************************************************/
    #region Anexo IV - Informe valorativo y de evaluación
    /**
     * Esta funcion nos permite rellenar el Anexo 4
     * @author LauraM <lauramorenoramos97@gmail.com>
     * @param Request $val->get(dni_tutor) es el dni del tutor
     * @return void
     */
    public function rellenarAnexoIV(Request $val)
    {
        $fecha = Carbon::now();
        $dni_responsable = $val->get('dni_responsable');
        $empresa_responsable= Trabajador::select('id_empresa')->where('dni','=',$dni_responsable)->get();
        



       /* $alumnos_del_tutor = Tutoria::join('matricula', 'tutoria.cod_centro', '=', 'matricula.cod_centro')
            ->where('matricula.cod_centro', '=', $centro_estudos_tutor[0]->cod_centro)
            ->select('matricula.dni_alumno')
            ->get();

        //Como voy a obtener el nombre del documento??
        Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2'. DIRECTORY_SEPARATOR .$fecha->year));
        $rutaOrigen = $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2' . DIRECTORY_SEPARATOR .$fecha->year . DIRECTORY_SEPARATOR .'plantilla' . '.docx';

        foreach ($alumnos_del_tutor as $a) {
            $alumno_nombre = Alumno::select('nombre')->where('dni','=',$a->dni)->get();
            $alumno_apellidos = Alumno::select('apellidos')->where('dni','=',$a->dni)->get();
            $ciclo_nombre = Auxiliar::getNombreCicloAlumno($a->dni);
            $familia_profesional_descripcion = Auxiliar::getDescripcionFamiliaProfesional($ciclo_nombre[0]->nombre_ciclo);
            $empresa_nombre = ControladorAlumno::getNombreEmpresa($a->dni);
            $tutor_empresa_nombre = ControladorAlumno::getNombreTutorEmpresa($a->dni);
            $fct=ControladorAlumno::getDatosFct($a->dni);
            $rutaDestino = $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2' . DIRECTORY_SEPARATOR .$fecha->year. DIRECTORY_SEPARATOR . 'Anexo2_'.$a->dni.'docx';

            $datos = [
                'centro_nombre' =>  $centro_nombre->nombre,
                'centro_cif' => $centro_cif[0]->cif,
                'tutor_nombre' => $tutor_nombre[0]->nombre,
                'tutor_apellidos' => $tutor_apellidos[0]->apellidos,
                'grupo_tutoriza' => $grupo_tutoriza[0]->apellidos,
                'alumno_nombre' => $tutor_apellidos[0]->nombre,
                'alumno_apellidos' => $tutor_apellidos[0]->apellidos,
                'familia_profesional_descripcion' => $familia_profesional_descripcion[0]->nombre,
                'empresa_nombre' => $empresa_nombre[0]->nombre,
                'tutor_empresa_nombre' => $tutor_empresa_nombre[0]->nombre,
            ];
    
            $template = new TemplateProcessor($rutaOrigen);
            $template->setValues($datos);
            $template->saveAs($rutaDestino);
            unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2' . DIRECTORY_SEPARATOR .$fecha->year. DIRECTORY_SEPARATOR .'plantilla' . '.docx');
            Anexo::create(['tipo_anexo' => 'Anexo2', 'ruta_anexo' => $rutaDestino]);
        }

        //return response()->download(public_path($nombreZip))->deleteFileAfterSend(true);*/
    }
    #endregion
    /***********************************************************************/
}
