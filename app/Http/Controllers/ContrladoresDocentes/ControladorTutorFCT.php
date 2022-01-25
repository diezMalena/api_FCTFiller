<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use App\Models\CentroEstudios;
use App\Models\Empresa;
use App\Models\CentroTrabajo;
use App\Models\Profesor;
use App\Models\Ciclo;
use App\Models\CentroCiclo;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\EmpresaCentroEstudios;

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
        try{
        $rutaOriginal = 'anexos/plantillas/Anexo1';
        $rutaDestino = 'anexos/plantillas/pruebaAnexo1';
        $template = new TemplateProcessor($rutaOriginal . '.docx');

        $fecha= Carbon::now();

        $dni_tutor=$val->get('dni_tutor');
        $cod_centro=Profesor::select('cod_centro_estudios')->where('dni',$dni_tutor)->get();
        $id_empresa=$val->get('id');


         $num_convenio = EmpresaCentroEstudios::where('id_empresa', '=', $id_empresa)->where('cod_centro', '=', $cod_centro)->first();
         $nombre_centro=CentroEstudios::select('nombre')->where('cod_centro',$cod_centro)->get();
         $nombre_empresa=Empresa::select('nombre')->where('id',$id_empresa)->get();
         $dir_centro=CentroTrabajo::select('direccion')->where('id_empresa',$id_empresa)->get();
         $nombre_tutor=Profesor::select('nombre')->where('dni',$dni_tutor)->get();
         $ciudad_centro_estudios=CentroEstudios::select('ciudad')->where('cod_centro',$cod_centro)->get();
         $curso_anio=Curso::select('anio')->where('dni',$dni_tutor)->get();

        /* $ciclo = Alumno::table('alumno')
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('ciclo.nombre')
            ->get();*/

        $datos = [
            'num_convenio'=>$num_convenio,
            'dia' => $fecha->day,
            'mes' => $fecha->month,
            'year' => $fecha->year,
            'nombre_centro'=>$nombre_centro,
            'nombre_empresa'=>$nombre_empresa,
            'dir_centro'=>$dir_centro,
            'nombre_tutor'=>$nombre_tutor,
            'ciudad_centro'=>$ciudad_centro_estudios,
            'anio_curso'=>$curso_anio,
            //'responsable_empresa'=>$responsable_empresa, representante legal
        ];

        $template->setValues($datos);
        $template->saveAs($rutaDestino . '.docx');
        $this->convertirWordPDF($rutaDestino);
    }catch (Exception $e) {
        dd($e);
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
