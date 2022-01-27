<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

class ControladorTutorFCT extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
                ->where('rol_trabajador_asignado.id_rol','=',1)
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
                $table->addCell(700)->addText('APELLIDOS Y NOMBRE');
                $table->addCell(700)->addText('D.N.I');
                $table->addCell(700)->addText('LOCALIDAD DE RESIDENCIA DEL ALUMNO/A (**)');
                $table->addCell(700)->addText('HORARIO DIARIO');
                $table->addCell(700)->addText('NUMERO HORAS');
                $table->addCell(700)->addText('FECHA DE COMIENZO');
                $table->addCell(700)->addText('FECHA DE FINALIZACION');
                foreach ($alumnos as $a) {
                    $table->addRow();
                    $table->addCell(700)->addText($a->apellido.' '.$a->nombre);
                    $table->addCell(700)->addText($a->dni);
                    $table->addCell(700)->addText($a->localidad);
                    $table->addCell(700)->addText($a->horario);
                    $table->addCell(700)->addText($a->num_horas);
                    $table->addCell(700)->addText($a->fecha_ini);
                    $table->addCell(700)->addText($a->fecha_fin);
                }


              $datos = [
                  'num_convenio'=>$num_convenio[0]->cod_convenio,
                  'dia' => $fecha->day,
                  'mes' => $fecha->month,
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
              $this->convertirWordPDF($rutaDestino);
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
