<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\AlumnoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use Carbon\Carbon;
use App\Models\CentroEstudios;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\Curso;
use App\Models\EmpresaCentroEstudios;
use App\Models\EmpresaCurso;
use App\Models\EmpresaAlumno;
use App\Auxiliar\Parametros;

class ControladorTutorFCT extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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

    /**
     * Esta funcion nos permite rellenar el Anexo 1
     *@author LauraM <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function rellenarAnexo1(Request $val){

        $dni_tutor=$val->get('dni_tutor');
        $curso=Curso::select('cod_curso')->where('dni_tutor',$dni_tutor)->get();
        $empresas_id=EmpresaCurso::select('id_empresa')->where('cod_curso',$curso[0]->cod_curso)->get();
             //Recorrido id empresas
            foreach($empresas_id as $id){
                try{
                    $rutaOriginal = 'anexos/plantillas/Anexo1';
                    $rutaDestino = 'anexos/rellenos/anexo1/Anexo1'.$id->id_empresa;
                    $template = new TemplateProcessor($rutaOriginal . '.docx');

                //Fecha
                $fecha= Carbon::now();
                //Codigo del centro
                $cod_centro=Profesor::select('cod_centro_estudios')->where('dni',$dni_tutor)->get();
                //Numero de Convenio
                $num_convenio = EmpresaCentroEstudios::select('cod_convenio')->where('id_empresa', '=', $id->id_empresa)->where('cod_centro', '=', $cod_centro[0]->cod_centro_estudios)->get();
                //Nombre del centro
                $nombre_centro=CentroEstudios::select('nombre')->where('cod_centro',$cod_centro[0]->cod_centro_estudios)->get();
                //Nombre de la empresa
                $nombre_empresa=Empresa::select('nombre')->where('id',$id->id_empresa)->get();
                //Cif empresa
                $cif_empresa=Empresa::select('cif')->where('id',$id->id_empresa)->get();
                //Direccion del centro //REVISAR
                //$dir_centro=CentroTrabajo::select('direccion')->where('cif_empresa',$cif_empresa[0]->cif)->get();
                $dir_centro=CentroEstudios::select('direccion')->where('cod_centro',$cod_centro[0]->cod_centro_estudios)->get();
                //Nombre del ciclo //REVISAR
                $nombre_ciclo = Profesor::join('centro_estudios', 'centro_estudios.cod_centro', '=', 'profesor.cod_centro_estudios')
                ->join('centro_ciclo', 'centro_ciclo.cod_centro', '=', 'centro_estudios.cod_centro')
                ->join('ciclo', 'ciclo.cod_ciclo', '=', 'centro_ciclo.cod_ciclo')
                ->select('ciclo.nombre')
                ->where('dni','=',$dni_tutor)
                ->get();

                //Año del curso
                $curso_anio=Curso::select('anio')->where('dni_tutor',$dni_tutor)->get();
                //Nombre del tutor
                $nombre_tutor=Profesor::select('nombre')->where('dni',$dni_tutor)->get();
                //Responsable de la empresa
                $responsable_empresa=Empresa::join('trabajador', 'trabajador.id_empresa','=','empresa.id')
                ->join('rol_trabajador_asignado','rol_trabajador_asignado.dni','=','trabajador.dni')
                ->select('trabajador.nombre')
                ->where('trabajador.id_empresa','=',$id->id_empresa)
                ->where('rol_trabajador_asignado.id_rol','=',Parametros::REPRESENTANTE_LEGAL)
                ->get();

                //Ciudad del centro de estudios
                $ciudad_centro_estudios=CentroEstudios::select('localidad')->where('cod_centro',$cod_centro[0]->cod_centro_estudios)->get();
                //Alumnos
                $alumnos=EmpresaAlumno::join('alumno','alumno.dni','=','empresa_alumno.dni_alumno')
                ->select('alumno.nombre','alumno.apellido','alumno.dni','alumno.localidad','empresa_alumno.horario','empresa_alumno.num_horas','empresa_alumno.fecha_ini','empresa_alumno.fecha_fin')
                ->where('id_empresa','=',$id->id_empresa)
                ->get();


                /********************************************************************************* */
                $table = new Table(array('unit' => TblWidth::TWIP));
                $table->addRow();
                $table->addCell(1500)->addText('APELLIDOS Y NOMBRE');
                $table->addCell(1500)->addText('D.N.I');
                $table->addCell(1500)->addText('LOCALIDAD DE RESIDENCIA DEL ALUMNO/A (**)');
                $table->addCell(1500)->addText('HORARIO DIARIO');
                $table->addCell(1500)->addText('NUMERO HORAS');
                $table->addCell(1500)->addText('FECHA DE COMIENZO');
                $table->addCell(1500)->addText('FECHA DE FINALIZACION');
                foreach ($alumnos as $a) {
                    $table->addRow();
                    $table->addCell(1500)->addText($a->apellido.' '.$a->nombre);
                    $table->addCell(1500)->addText($a->dni);
                    $table->addCell(1500)->addText($a->localidad);
                    $table->addCell(1500)->addText($a->horario);
                    $table->addCell(1500)->addText($a->num_horas);
                    $table->addCell(1500)->addText($a->fecha_ini);
                    $table->addCell(1500)->addText($a->fecha_fin);
                }


              $datos = [
                  'num_convenio'=>$num_convenio[0]->cod_convenio,
                  'dia' => $fecha->day,
                  'mes' => Parametros::MESES[$fecha->month],
                  'year' => $fecha->year,
                  'nombre_centro'=>$nombre_centro[0]->nombre,
                  'nombre_empresa'=>$nombre_empresa[0]->nombre,
                  'dir_centro'=>$dir_centro[0]->direccion,
                  'nombre_tutor'=>$nombre_tutor[0]->nombre,
                  'ciudad_centro'=>$ciudad_centro_estudios[0]->localidad,
                  'anio_curso'=>$curso_anio[0]->anio,
                  'ciclo_nombre'=>$nombre_ciclo[0]->nombre,
                  'responsable_empresa'=>$responsable_empresa[0]->nombre,
              ];


              $template->setValues($datos);
              $template->setComplexBlock('{table}', $table);
              $template->saveAs($rutaDestino . '.docx');
             // $this->convertirWordPDF($rutaDestino);


             //$file = public_path(). $rutaDestino.".docx";
             //$headers = ['Content-Type: application/vnd.ms-word.document.macroEnabled.12'];
             //return \Response::download($file, 'plugin.jpg', $headers);

             //return response()->download(public_path($rutaDestino . '.docx'));
            }catch (Exception $e) {
                dd($e);
            }
            }
    }

    /**
     * Esta funcion nos permite convertir un word en un pdf
     *
     * @param String $rutaArchivo
     * @return void
     */
    public function convertirWordPDF(String $rutaArchivo){

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');


        $Content = \PhpOffice\PhpWord\IOFactory::load($rutaArchivo . '.docx');

        $savePdfPath = public_path($rutaArchivo. '.pdf');

        $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content,'PDF');
        $PDFWriter->save($savePdfPath);

        if ( file_exists($rutaArchivo . '.docx') ) {
            unlink($rutaArchivo . '.docx');
        }
    }
}
