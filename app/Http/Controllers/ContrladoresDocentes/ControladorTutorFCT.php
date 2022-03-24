<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Auxiliar;
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
use Database\Factories\RolProfesorAsignadoFactory;
use Faker\Core\Number;
use Illuminate\Support\Facades\Hash;
use Mockery\Undefined;
use PhpParser\Node\Expr\Cast\Array_;
use Ramsey\Uuid\Type\Integer;

class ControladorTutorFCT extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //
    /**
     *  Esta función se encarga de coger los datos dni y nombre de los
     *  alumnos que no están asociados a ninguna empresa
     *  asignados al dni del tutor que recibimos como parámetro.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarAlumnosSinEmpresa(string $dni)
    {
        $cursoAcademico = Auxiliar::obtenerCursoAcademico();
        $alumnosEnEmpresa = Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
            ->join('fct', 'fct.dni_alumno', '=', 'matricula.dni_alumno')
            ->join('grupo', 'grupo.cod', '=', 'matricula.cod_grupo')
            ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
            ->where([['tutoria.dni_profesor', '=', $dni], ['tutoria.curso_academico', '=', $cursoAcademico]])
            ->pluck('alumno.dni')
            ->toArray();

        $alumnosSinEmpresa = Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
            ->join('grupo', 'grupo.cod', '=', 'matricula.cod_grupo')
            ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
            ->where([['tutoria.dni_profesor', '=', $dni], ['tutoria.curso_academico', '=', $cursoAcademico]])
            ->whereNotIn('alumno.dni', $alumnosEnEmpresa)
            ->select(['alumno.dni', 'alumno.nombre', 'alumno.va_a_fct'])
            ->get();
        return response()->json($alumnosSinEmpresa, 200);
    }
    /**
     *  Esta función se encarga de coger el nombre del ciclo a partir del dni del tutor.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarNombreCiclo(string $dni)
    {
        $nombre = Tutoria::where('dni_profesor', '=', $dni)->get()[0]->cod_grupo;
        return response()->json($nombre, 200);
    }
    /**
     *  Esta función se encarga de coger las empresas que solicitan el curso que está tutorizando
     *  el profesor del que recibimos el dni, y dentro de esas empresas hay un array de alumnos que están
     *  ya asociados a una empresa.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public function solicitarEmpresasConAlumnos(string $dni)
    {
        $empresas = Grupo::join('empresa_grupo', 'empresa_grupo.cod_grupo', '=', 'grupo.cod')
            ->join('empresa', 'empresa.id', '=', 'empresa_grupo.id_empresa')
            ->join('tutoria', 'tutoria.cod_grupo', '=', 'grupo.cod')
            ->where('tutoria.dni_profesor', $dni)
            ->get();

        foreach ($empresas as  $empresa) {
            //Aquí rocojo el nombre del responsable de esa empresa
            $responsable = RolTrabajadorAsignado::join('trabajador', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                ->join('empresa', 'empresa.id', '=', 'trabajador.id_empresa')
                ->where([['rol_trabajador_asignado.id_rol', 2], ['empresa.id', $empresa->id]])
                ->select('trabajador.nombre')
                ->get()[0]->nombre;
            $empresa->nombre_responsable = $responsable;
            //Aquí rocojo el dni del responsable de esa empresa
            $dni_responsable = RolTrabajadorAsignado::join('trabajador', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                ->join('empresa', 'empresa.id', '=', 'trabajador.id_empresa')
                ->where([['rol_trabajador_asignado.id_rol', 2], ['empresa.id', $empresa->id]])
                ->select('trabajador.dni')
                ->get()[0]->dni;
            $empresa->dni_responsable = $dni_responsable;
            //Aquí rocojo los alumnos asociados a esa empresa
            $alumnos = Grupo::join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
                ->join('alumno', 'alumno.dni', '=', 'matricula.dni_alumno')
                ->join('fct', 'fct.dni_alumno', '=', 'alumno.dni')
                ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
                ->where([['tutoria.dni_profesor', $dni], ['fct.id_empresa', $empresa->id]])
                ->select(['alumno.nombre', 'alumno.dni', 'alumno.va_a_fct', 'fct.horario', 'fct.fecha_ini', 'fct.fecha_fin'])
                ->get();
            $empresa->alumnos = $alumnos;
        }

        return response()->json($empresas, 200);
    }

    /**
     *  Esta función se encarga de actualizar la empresa a la que están asignados
     *  los alumnos.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com>
     *  @param $request tiene las empresas con los datos del id, el responsable, y un array con sus alumnos asiganados
     *  que estos tienen dentro si van a fct, su dni, fecha de inicio de las prácticas y de finalización, el horario.
     *  También tiene el array de alumnos sin empresa.
     */
    public function actualizarEmpresaAsignadaAlumno(Request $request)
    {
        try {
            $cursoAcademico = Auxiliar::obtenerCursoAcademico();
            $alumnos_solos = $request->get('alumnos_solos');
            $empresas = $request->get('empresas');
            $dni_tutor = $request->get('dni_tutor');
            $this->borrarAnexosTablaFCT($dni_tutor);
            // error_log(print_r($alumnos_solos, true));
            //elimita de la tabla fct los registros de los alumnos que ya no están en una empresa
            foreach ($alumnos_solos as $alumno) {
                Fct::where([['dni_alumno', $alumno['dni']], ['curso_academico', $cursoAcademico]])->delete();
            }

            //este for mete el nuevo nombre del responsable, se haya cambiado o no.
            //elimina el registro de la tabla fct de los alumnos que están en una empresa y
            //los inserta de nuevo con los cambios que se han hecho.
            foreach ($empresas as $empresa) {
                Trabajador::find($empresa['dni_responsable'])->update(['nombre' => $empresa['nombre_responsable']]);
                $alumnos = $empresa['alumnos'];
                foreach ($alumnos as $alumno) {
                    Fct::where([['dni_alumno', $alumno['dni']], ['curso_academico', $cursoAcademico]])->delete();

                    Fct::create([
                        'id_empresa' => $empresa['id'],
                        'dni_alumno' => $alumno['dni'],
                        'dni_tutor_empresa' => $empresa['dni_responsable'],
                        'curso_academico' => $cursoAcademico,
                        'horario' => $alumno['horario'],
                        'num_horas' => '400',
                        'fecha_ini' => $alumno['fecha_ini'],
                        'fecha_fin' => $alumno['fecha_fin'],
                        'firmado_director' => '0',
                        'firmado_empresa' => '0',
                        'ruta_anexo' => '',
                        'departamento' => ''
                    ]);
                }
            }
            return response()->json(['message' => 'Actualizacion completada'], 200);
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * @author LauraM <lauramorenoramos97@gmail.com>
     * A esta funcion le pasas el dni del tutor, con esa dni, busca las rutas de sus anexos en la tabla FCT
     * y borra esos anexos
     */
    public function borrarAnexosTablaFCT($dni_tutor)
    {
        $anexosArr = array();

        //buscar los anexos del tutor filtrando
        $anexosCreados = FCT::select('ruta_anexo')->where('ruta_anexo', 'like', "$dni_tutor%")->get();

        foreach ($anexosCreados as $a) {
            $anexosArr[] = $a->ruta_anexo . '.docx';
        }

        $anexosArr = array_unique($anexosArr);

        foreach ($anexosArr as $a) {
            unlink(public_path($a));
        }
    }

    public function borrarAnexosTablaAnexos($tipoAnexo, $dni_tutor)
    {

        //borrar los anexos del tutor filtrando y tipo de Anexo
        Anexo::where('ruta_anexo', 'like', "$dni_tutor%")->where('tipo_anexo', '=', $tipoAnexo)->where('habilitado', '=', 1)->delete();
    }

    /**
     * Esta funcion nos permite rellenar el Anexo 1
     *@author LauraM <lauramorenoramos97@gmail.com>
     * @param Request $val->get(dni_tutor) es el dni del tutor
     * @return void
     */
    public function rellenarAnexo1(Request $val)
    {
        $dni_tutor = $val->get('dni_tutor');
        //Borro todos los anexos de la tabla Anexos que sean inservibles
        $this->borrarAnexosTablaAnexos('Anexo1', $dni_tutor);
        $grupo = Tutoria::select('cod_grupo')->where('dni_profesor', $dni_tutor)->get();
        $empresas_id = EmpresaGrupo::select('id_empresa')->where('cod_grupo', $grupo[0]->cod_grupo)->get();
        $fecha = Carbon::now();
        $AuxNombre = $dni_tutor . '_' . $fecha->day . '_' . Parametros::MESES[$fecha->month] . '_' . $fecha->year . $fecha->format('_h_i_s_A');


        //***************************************ZIP********************************************** */
        $zip = new ZipArchive;
        $nombreZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';
        //******************************************************************************************** */

        try {
            foreach ($empresas_id as $id) {
                //Alumnos
                $alumnos = Fct::join('alumno', 'alumno.dni', '=', 'fct.dni_alumno')
                    ->join('matricula', 'matricula.dni_alumno', '=', 'fct.dni_alumno')
                    ->select('alumno.nombre', 'alumno.apellidos', 'alumno.dni', 'alumno.localidad', 'fct.horario', 'fct.num_horas', 'fct.fecha_ini', 'fct.fecha_fin')
                    ->where('fct.id_empresa', '=', $id->id_empresa)
                    ->where('matricula.cod_grupo', '=', $grupo[0]->cod_grupo)
                    ->get();
                if (count($alumnos) > 0) {
                    //Codigo del centro
                    $cod_centro = Profesor::select('cod_centro_estudios')->where('dni', $dni_tutor)->get();
                    //Numero de Convenio
                    $num_convenio = Convenio::select('cod_convenio')->where('id_empresa', '=', $id->id_empresa)->where('cod_centro', '=', $cod_centro[0]->cod_centro_estudios)->get();
                    //Nombre del ciclo
                    $nombre_ciclo = Grupo::select('nombre_ciclo')->where('cod', $grupo[0]->cod_grupo)->get();
                    //Codigo Ciclo
                    $cod_ciclo = Grupo::select('cod')->where('nombre_ciclo',  $nombre_ciclo[0]->nombre_ciclo)->get();



                    //ARCHIVO
                    $rutaOriginal = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . 'Anexo1';
                    $convenioAux = str_replace('/', '-', $num_convenio[0]->cod_convenio);
                    $AuxNombre = '_' . $id->id_empresa . '_' . $convenioAux . '_' . $cod_ciclo[0]->cod . '_' . $fecha->year . '_';
                    $rutaDestino = $dni_tutor  . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . 'Anexo1' . $AuxNombre;
                    $template = new TemplateProcessor($rutaOriginal . '.docx');


                    //Almacenamos las rutas de los anexos en la bbdd

                    foreach ($alumnos as $a) {
                        Fct::where('id_empresa', '=', $id->id_empresa)->where('dni_alumno', '=', $a->dni)->update(['ruta_anexo' => $rutaDestino . '.docx']);
                        Anexo::create(['tipo_anexo' => 'Anexo1', 'ruta_anexo' => $rutaDestino . '.docx']);
                    }

                    //Nombre de la empresa
                    $nombre_empresa = Empresa::select('nombre')->where('id', $id->id_empresa)->get();
                    //Nombre del centro
                    $nombre_centro = CentroEstudios::select('nombre')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();
                    //Direccion del centro
                    $dir_centro = Empresa::select('direccion')->where('id', $id->id_empresa)->get();
                    //Año del curso
                    $curso_anio = Convenio::select('curso_academico_inicio')->where('cod_convenio', $num_convenio[0]->cod_convenio)->get();
                    //Nombre del tutor
                    $nombre_tutor = Profesor::select('nombre')->where('dni', $dni_tutor)->get();
                    //Responsable de la empresa
                    $responsable_empresa = Empresa::join('trabajador', 'trabajador.id_empresa', '=', 'empresa.id')
                        ->join('rol_trabajador_asignado', 'rol_trabajador_asignado.dni', '=', 'trabajador.dni')
                        ->select('trabajador.nombre')
                        ->where('trabajador.id_empresa', '=', $id->id_empresa)
                        ->where('rol_trabajador_asignado.id_rol', '=', Parametros::REPRESENTANTE_LEGAL)
                        ->get();

                    //representante del centro de trabajo
                    $representante_centro = Empresa::join('trabajador', 'trabajador.id_empresa', '=', 'empresa.id')
                        ->join('rol_trabajador_asignado', 'rol_trabajador_asignado.dni', '=', 'trabajador.dni')
                        ->select('trabajador.nombre')
                        ->where('trabajador.id_empresa', '=', $id->id_empresa)
                        ->where('rol_trabajador_asignado.id_rol', '=', Parametros::RESPONSABLE_CENTRO)
                        ->get();

                    //Directora
                    $directora = Profesor::join('rol_profesor_asignado', 'rol_profesor_asignado.dni', '=', 'profesor.dni')
                        ->select('profesor.nombre')
                        ->where('profesor.cod_centro_estudios', '=', $cod_centro[0]->cod_centro_estudios)
                        ->where('rol_profesor_asignado.id_rol', '=', Parametros::DIRECTOR)
                        ->get();


                    //Ciudad del centro de estudios
                    $ciudad_centro_estudios = CentroEstudios::select('localidad')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();


                    /**************************************Tabla************************************** */
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
                        $table->addCell(1500)->addText($a->apellidos . ' ' . $a->nombre);
                        $table->addCell(1500)->addText($a->dni);
                        $table->addCell(1500)->addText($a->localidad);
                        $table->addCell(1500)->addText($a->horario);
                        $table->addCell(1500)->addText($a->num_horas);
                        $table->addCell(1500)->addText($a->fecha_ini);
                        $table->addCell(1500)->addText($a->fecha_fin);
                    }

                    /**************************************Datos************************************** */
                    $datos = [
                        'num_convenio' => $num_convenio[0]->cod_convenio,
                        'dia' => $fecha->day,
                        'mes' => Parametros::MESES[$fecha->month],
                        'year' => $fecha->year,
                        'nombre_centro' => $nombre_centro[0]->nombre,
                        'nombre_empresa' => $nombre_empresa[0]->nombre,
                        'dir_centro' => $dir_centro[0]->direccion,
                        'nombre_tutor' => $nombre_tutor[0]->nombre,
                        'ciudad_centro' => $ciudad_centro_estudios[0]->localidad,
                        'anio_curso' => $curso_anio[0]->curso_academico_inicio,
                        'ciclo_nombre' =>  $nombre_ciclo[0]->nombre_ciclo,
                        'responsable_empresa' => $responsable_empresa[0]->nombre,
                        'directora' => $directora[0]->nombre,
                        'representante_centro' => $representante_centro[0]->nombre,
                    ];

                    $rutaCarpeta = public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1');
                    $this->existeCarpeta($rutaCarpeta);
                    $rutaCarpeta = public_path('tmp' . DIRECTORY_SEPARATOR . 'anexos');
                    $this->existeCarpeta($rutaCarpeta);

                    $template->setValues($datos);
                    $template->setComplexBlock('{table}', $table);
                    $template->saveAs($rutaDestino . '.docx');

                    // $this->convertirWordPDF($rutaDestino);
                }

                //Convertir en Zip
                $nombreZip = $this->montarZip($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1', $zip, $nombreZip);
            }
            return response()->download(public_path($nombreZip))->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error de ficheros: ' . $e
            ], 500);
        }
    }


    /**
     * Este metodo sirve para comprimir varios archivos del Anexo1 en un zip
     * @author Laura <lauramorenoramos97@gmail.com>
     *
     * @param String $rutaArchivo es la ruta en la que se van a buscar los archivos a comprimir
     * @param ZipArchive $zip es el zip
     * @param String $nombreZip es el nombre del zip
     * @return $nombreZip
     */
    public function montarZip(String $rutaArchivo, ZipArchive $zip, String $nombreZip)
    {
        if ($zip->open(public_path($nombreZip), ZipArchive::CREATE)) {

            $files = File::files(public_path($rutaArchivo));
            foreach ($files as $value) {
                $relativeNameZipFile = basename($value);
                $zip->addFile($value, $relativeNameZipFile);
            }
            $zip->close();
        }
        return $nombreZip;
    }

    /**
     * Esta funcion crea una carpeta si esta no existe
     *@author Laura <lauramorenoramos97@gmail.com>
     * @param [string] $ruta
     * @return void
     */
    public function existeCarpeta($ruta)
    {
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }
    }


    /**
     * Esta funcion nos permite convertir un word en un pdf
     *@author @DaniJCoello
     * @param String $rutaArchivo
     * @return void
     */
    public function convertirWordPDF(String $rutaArchivo)
    {

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');


        $Content = \PhpOffice\PhpWord\IOFactory::load($rutaArchivo . '.docx');

        $savePdfPath = public_path($rutaArchivo . '.pdf');

        $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content, 'PDF');
        $PDFWriter->save($savePdfPath);

        if (file_exists($rutaArchivo . '.docx')) {
            unlink($rutaArchivo . '.docx');
        }
    }


    /**
     * Esta funcion devuelve los anexos de un tutor, sacando lo que va a mostrar de la tabla Anexos
     *
     * @param Request $val
     * @return void
     */
    public function verAnexos($dni_tutor)
    {
        //$dni_tutor = $val->get('dni_tutor');
        $datos = array();
        $habilitado = 1;

        ///////////////////////////////ANEXO 0  Y 0A//////////////////////////////////////
        $Anexos0 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->whereIn('tipo_anexo', ['Anexo0', 'Anexo0A'])->where('ruta_anexo', 'like', "$dni_tutor%")->get();
        //dd($Anexos0);
        foreach ($Anexos0 as $a) {
            //Esto sirve para poner las barras segun el so que se este usando
            $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
            $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

            $convenioAux = $rutaAux[2];
            $convenioAux = explode('_', $convenioAux);
            $convenioAux = explode('.', $convenioAux[1]);
            $convenioAux = str_replace('-', DIRECTORY_SEPARATOR, $convenioAux[0]);

            $id_empresa = Convenio::select('id_empresa')->where('cod_convenio', '=', $convenioAux)->get();
            $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa[0]->id_empresa)->get();

            //FECHA
            $fechaAux = explode(':', $a->created_at);
            $fechaAux = explode(' ', $fechaAux[0]);

            $datos[] = [
                'nombre' => $rutaAux[1],
                'codigo' => $rutaAux[2],
                'empresa' => $empresa_nombre[0]->nombre,
                'firma_empresa' => $a->firmado_empresa,
                'firma_centro' => $a->firmado_director,
                'created_at' => $fechaAux[0]
            ];
        }

        ///////////////////////////////ANEXO 1//////////////////////////////////////
        $Anexos1 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->where('tipo_anexo', '=', 'Anexo1')->where('ruta_anexo', 'like', "$dni_tutor%")->distinct()->get();

        foreach ($Anexos1 as $a) {

            //Esto sirve para poner las barras segun el so que se este usando
            $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
            $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

            $nombreArchivo = $rutaAux[2];

            //Para sacar el id de la empresa
            $id_empresa = explode('_', $rutaAux[2]);
            $id_empresa = $id_empresa[1];

            $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa)->get();

            //FECHA
            $fechaAux = explode(':', $a->created_at);
            $fechaAux = explode(' ', $fechaAux[0]);

            //meter ese nombre en un array asociativo
            $datos[] = [
                'nombre' => 'Anexo1',
                'codigo' => $nombreArchivo,
                'empresa' => $empresa_nombre[0]->nombre,
                'firma_empresa' =>  $a->firmado_empresa,
                'firma_centro' => $a->firmado_director,
                'created_at' => $fechaAux[0]
            ];
        }
        return response()->json($datos, 200);
    }
    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * A esta funcion le llegan un array de rutas y las modifica para que tengan el formato a favor del sistema
     * operativo que se este usando
     *
     * @param [string] $rutas
     * @return array
     */
    public function transformarRutasSO($rutas)
    {

        $rutasAux = array();
        foreach ($rutas as $r) {
            str_replace($r, '/', DIRECTORY_SEPARATOR);
            $rutasAux[] = $r;
        }

        return $rutasAux;
    }

    /**
     * Esta funcion nos permite descargar un anexo en concreto
     *@author Laura <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function descargarAnexo(Request $val)
    {

        $dni_tutor = $val->get('dni_tutor');
        $cod_anexo = $val->get('codigo');
        $codAux = explode("_", $cod_anexo);
        $rutaOriginal = '';

        if ($codAux[0] == 'Anexo1') {
            $rutaOriginal = public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . $cod_anexo);
            $rutaOriginal  = str_replace('/', DIRECTORY_SEPARATOR, $rutaOriginal);
        } else {
            if ($codAux[0] == 'Anexo0' || $codAux[0] == 'Anexo0A') {
                $rutaOriginal = public_path($dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo);
                $rutaOriginal  = str_replace('/', DIRECTORY_SEPARATOR, $rutaOriginal);
            }
        }

        return Response::download($rutaOriginal);
        // return response()->download($rutaOriginal);
    }


    /**
     * Esta funcion te permite eliminar un fichero de una carpeta
     *@author Laura <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function eliminarAnexo($dni_tutor, $cod_anexo)
    {
        $codAux = explode("_", $cod_anexo);
        if ($codAux[0] == 'Anexo1') {
            //Eliminar un fichero
            unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . $cod_anexo);
            FCT::where('ruta_anexo', 'like', "%$cod_anexo")->update([
                'ruta_anexo' => '',
            ]);
        } else {
            if ($codAux[0] == 'Anexo0' || $codAux[0] == 'Anexo0A') {
                unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo);
                Convenio::where('ruta_anexo', 'like', "%$cod_anexo")->update([
                    'ruta_anexo' => '',
                ]);
            }
        }
        return response()->json(['message' => 'Archivo eliminado'], 200);
    }




    /**
     * Esta funcion permite descargar todos los anexos del crud de anexos del tutor, menos el 3
     *
     * @param Request $val
     * @return void
     */
    public function descargarTodo(Request $val)
    {
        $zip = new ZipArchive;
        $AuxNombre = Str::random(7);
        $dni = $val->get('dni_tutor');
        $habilitado =  $val->get('habilitado');

        $nombreZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';

        $nombreZip = $this->montarZipCrud($dni, $zip, $nombreZip,$habilitado);


        return response()->download(public_path($nombreZip));
    }

    /**
     * Esta funcion sirve para generar el zip de todos los anexos del crud de anexos
     * Miramos los anexos de la carpeta de anexos del tutor, buscamos ese anexo habilitado o no habilitado, segun si
     * la consulta se hace desde el crud de anexos o desde el historial  y comprobamos
     * si este existe en el directorio, en tal caso se añade al zip
     *@author Laura <lauramorenoramos97@gmail.com>
     * @param String $dni_tutor, el dni del tutor, sirve para ubicar su directorio
     * @param ZipArchive $zip , el zip donde se almacenaran los archivos
     * @param String $nombreZip, el nombre que tendrá el zip
     * @return void
     */
    public function montarZipCrud(String $dni_tutor, ZipArchive $zip, String $nombreZip, $habilitado)
    {
        $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
        if ($zip->open(public_path($nombreZip), ZipArchive::CREATE)) {
            ///////////////////////////////ANEXO1//////////////////////////////////////////
            foreach ($files as $value) {
                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo1')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();


                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            ///////////////////////////////ANEXO0//////////////////////////////////////////
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0'));
            foreach ($files as $value) {

                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo0')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();


                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            ///////////////////////////////ANEXOA//////////////////////////////////////////
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0A'));
            foreach ($files as $value) {

                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo0A')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();


                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            $zip->close();
        }
        return $nombreZip;
    }


    /**
     * Genera el Anexo 0, convenio entre una empresa y un centro
     * @param string $codConvenio el código del convenio entre la empresa y el centro
     * @param string $dniTutor el DNI del tutor que está loggeado en el sistema
     * @return string la ruta en la que se guarda el anexo
     *
     * @author @DaniJCoello
     */
    public function generarAnexo0(string $codConvenio, string $dniTutor)
    {

        //Primero consigo los datos del centro de estudios asociado al tutor y su director
        $centroEstudios = $this->getCentroEstudiosFromConvenio($codConvenio)->makeHidden('created_at', 'updated_at');
        $director = $this->getDirectorCentroEstudios($centroEstudios->cod)->makeHidden('created_at', 'updated_at', 'password');

        //Ahora hago lo propio con la empresa en cuestión
        $empresa = $this->getEmpresaFromConvenio($codConvenio)->makeHidden('created_at', 'updated_at');
        $representante = $this->getRepresentanteLegal($empresa->id)->makeHidden('created_at', 'updated_at', 'password');

        //Construyo el array con todos los datos
        $auxPrefijos = ['director', 'centro', 'representante', 'empresa'];
        $auxDatos = [$director, $centroEstudios, $representante, $empresa];
        $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

        //Ahora extraigo los datos de fecha
        $fecha = Carbon::now();
        $datos['dia'] = $fecha->day;
        $datos['mes'] = AuxiliarParametros::MESES[$fecha->month];
        $datos['anio'] = $fecha->year % 100;
        $datos['cod_convenio'] = $codConvenio;

        //Esta variable se usa sólo para el nombre del archivo
        $codConvenioAux = str_replace('/', '-', $codConvenio);

        //Ahora genero el Word y el PDF en sí
        //Establezco las variables que necesito
        $nombrePlantilla = $empresa->es_privada == 1 ? 'Anexo0' : 'Anexo0A';
        // $nombreTemporal = $nombrePlantilla . '-' . $codConvenioAux . '-tmp';
        $rutaOrigen = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . $nombrePlantilla . '.docx';
        // $rutaTemporal = 'tmp/anexos/' . $nombreTemporal . '.docx';
        $this->existeCarpeta(public_path($dniTutor . DIRECTORY_SEPARATOR . $nombrePlantilla));
        $rutaDestino =  $dniTutor . DIRECTORY_SEPARATOR . $nombrePlantilla . DIRECTORY_SEPARATOR . $nombrePlantilla . '_' . $codConvenioAux . '.docx';
        //Creo la plantilla y la relleno
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);

        // Guardo la ruta del archivo en la base de datos
        Convenio::where('cod_convenio', $codConvenio)->update(['ruta_anexo' => $rutaDestino]);
        Anexo::create(['tipo_anexo' => 'Anexo0', 'ruta_anexo' => $rutaDestino]);

        // Y la devuelvo
        return $rutaDestino;
    }

    /**
     * Genera el código de un convenio a partir del código del centro, un autoincremental y la fecha
     * @param string $codCentroConvenio el código del centro para generar los convenios
     * @param string $tipo 'C' -> Convenio; 'A' -> Acuerdo
     * @return string el código del convenio
     *
     * @author @DaniJCoello
     */
    public function generarCodigoConvenio(string $codCentroConvenio, string $tipo)
    {
        $numConvenio = AuxConvenio::create()->id;
        $codConvenio = $codCentroConvenio . '/' . $tipo . $numConvenio . '/' . Carbon::now()->year % 100;
        return $codConvenio;
    }

    /**
     * Devuelve el centro de estudios asociado a un determinado profesor
     * @param string $dniProfesor el DNI del profesor asociado al centro de estudios
     * @return CentroEstudios una colección con la información del centro de estudios
     *
     * @author @DaniJCoello
     */
    public function getCentroEstudiosFromProfesor(string $dniProfesor)
    {
        return CentroEstudios::find(Profesor::find($dniProfesor)->cod_centro_estudios);
    }

    /**
     * Devuelve el centro de estudios asociado a un determinado código de convenio
     * @param string $codConvenio el código de convenio
     * @return CentroEstudios una colección con la información del centro de estudios
     *
     * @author @DaniJCoello
     */
    public function getCentroEstudiosFromConvenio(string $codConvenio)
    {
        return CentroEstudios::find(Convenio::where('cod_convenio', $codConvenio)->first()->cod_centro);
    }

    /**
     * Devuelve el director de un centro de estudios
     * @param string $codCentroEstudios el código irrepetible del centro de estudios
     * @return Profesor una colección con la información del director
     *
     * @author @DaniJCoello
     */
    public function getDirectorCentroEstudios(string $codCentroEstudios)
    {
        // SELECT * FROM profesor
        // WHERE profesor.cod_centro_estudios = 24101
        // AND profesor.dni IN (
        //     SELECT rol_profesor_asignado.dni
        //     FROM rol_profesor_asignado
        //     WHERE rol_profesor_asignado.id_rol = 1
        // );
        return Profesor::whereIn('dni', RolProfesorAsignado::where('id_rol', 1)->get('dni'))->where('cod_centro_estudios', $codCentroEstudios)->first();

        // SELECT profesor.*
        // FROM profesor JOIN rol_profesor_asignado
        // ON profesor.dni = rol_profesor_asignado.dni
        // WHERE profesor.cod_centro_estudios = 24101
        // AND rol_profesor_asignado.id_rol = 1;

    }

    /**
     * Devuelve la empresa asociada a un CIF
     * @param string $cif el CIF de la empresa
     * @return Empresa una colección con la información de la empresa
     *
     * @author @DaniJCoello
     */
    public function getEmpresaFromCIF(string $cif)
    {
        return Empresa::where('cif', $cif)->first();
    }

    /**
     * Devuelve la empresa asociada a una ID de la base de datos
     * @param int $id la ID autonumérica de la empresa en la base de datos de la aplicación
     * @return Empresa una colección con la información de la empresa
     *
     * @author @DaniJCoello
     */
    public function getEmpresaFromID(int $id)
    {
        return Empresa::find($id);
    }

    /**
     * Devuelve la empresa asociada a un código de convenio
     * @param string $codConvenio el código del convenio
     * @return Empresa una colección con la información de la empresa
     *
     * @author @DaniJCoello
     */
    public function getEmpresaFromConvenio(string $codConvenio)
    {
        return Empresa::find(Convenio::where('cod_convenio', $codConvenio)->first()->id_empresa);
    }

    /**
     * Devuelve el representante legal de una empresa
     * @param int $id la ID autonumérica de la empresa en la base de datos de la aplicación
     * @return Empresa una colección con la información de la empresa
     *
     * @author @DaniJCoello
     */
    public function getRepresentanteLegal(int $id)
    {
        return Trabajador::whereIn('dni', RolTrabajadorAsignado::where('id_rol', 1)->get('dni'))->where('id_empresa', $id)->first();
    }

    /**
     * Devuelve una response JSON con los datos del representante legal de una empresa
     * @param int $id La ID de la empresa
     * @return response JSON con los datos del representante legal
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function getRepresentanteLegalResponse(int $id)
    {
        return response()->json($this->getRepresentanteLegal($id), 200);
    }

    /**
     * Devuelve las empresas asociadas a un profesor mediante los convenios con su centro de estudios
     *
     * @param string $dniProfesor el DNI del profesor
     * @return response JSON con la colección de empresas asociadas
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function getEmpresasFromProfesor(string $dniProfesor)
    {
        $codCentro = Profesor::find($dniProfesor)->cod_centro_estudios;
        $empresas = Empresa::join('convenio', 'empresa.id', '=', 'convenio.id_empresa')
            ->where('convenio.cod_centro', $codCentro)
            ->get();
        return response()->json($empresas, 200);
    }

    /**
     * Actualiza la información de una empresa
     *
     * @param Request $req contiene los datos de la empresa
     * @return response JSON con la respuesta del servidor: 200 -> todo OK, 400 -> error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function updateEmpresa(Request $req)
    {
        $nombreEmpresa = Empresa::find($req->id)->nombre;
        try {
            Empresa::where('id', $req->id)->update([
                'cif' => $req->cif,
                'nombre' => $req->nombre,
                'email' => $req->email,
                'telefono' => $req->telefono,
                'localidad' => $req->localidad,
                'provincia' => $req->provincia,
                'direccion' => $req->direccion,
                'cp' => $req->cp
            ]);
            return response()->json(['title' => 'Empresa actualizada', 'message' => 'Se han actualizado los datos de ' . $nombreEmpresa], 200);
        } catch (Exception $e) {
            return response()->json(['title' => 'Error de actualización', 'message' => 'No se han podido actualizar los datos de ' . $nombreEmpresa], 400);
        }
    }

    /**
     * Actualiza la información de un trabajador de la empresa
     *
     * @param Request $req contiene los datos del trabajador
     * @return response JSON con la respuesta del servidor: 200 -> todo OK, 400 -> error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function updateTrabajador(Request $req)
    {
        $title = '';
        $message = '';
        $code = 0;
        try {
            Trabajador::where('dni', $req->dni)->update([
                'nombre' => $req->nombre,
                'apellidos' => $req->apellidos,
                'email' => $req->email
            ]);
            $title = 'Representante actualizado';
            $message = 'Se han actualizado los datos de ';
            $code = 200;
        } catch (Exception $e) {
            $title = 'Error de actualización';
            $message = 'No se han podido actualizar los datos de ';
            $code = 400;
        } finally {
            $trabajador = Trabajador::find($req->dni);
            $message .= $trabajador->nombre . ' ' . $trabajador->apellidos;
            return response()->json(['title' => $title, 'message' => $message], $code);
        }
    }

    /**
     * Elimina una empresa de la base de datos y sus trabajadores asociados
     * @param int $idEmpresa el ID de la empresa a eliminar
     * @return response JSON con la respuesta del servidor: 200 -> OK, 400 -> error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function deleteEmpresa(int $idEmpresa)
    {
        $nombreEmpresa = Empresa::find($idEmpresa)->nombre;
        try {
            // Primero eliminamos a los trabajadores de la empresa
            Trabajador::where('id_empresa', $idEmpresa)->delete();
            // Ahora eliminamos la empresa en sí
            Empresa::destroy($idEmpresa);
            return response()->json(['title' => 'Empresa eliminada', 'message' => 'Se ha eliminado con éxito la empresa ' . $nombreEmpresa], 200);
        } catch (Exception $e) {
            return response()->json(['title' => 'Error de eliminación', 'message' => 'No se ha podido eliminar la empresa ' . $nombreEmpresa], 400);
        }
    }

    /**
     * Recoge los datos que se envía desde el cliente, y añade estos a sus correspondientes tablas.
     * También, se generará el Anexo0 al añadir las empresas.
     * @author @Malena
     */
    public function addDatosEmpresa(Request $req)
    {
        try {
            $empresa = Empresa::create($req->empresa);
            $repre_aux = $req->representante;
            $repre_aux["id_empresa"] = $empresa->id;
            $repre_aux["password"] = Hash::make($repre_aux["password"]);
            $representante = Trabajador::create($repre_aux);
            RolTrabajadorAsignado::create([
                'dni' => $representante->dni,
                'id_rol' => 1,
            ]);
            $convenio = $this->addConvenio($req->dni, $empresa->id, $empresa->es_privada);
            $rutaAnexo = $this->generarAnexo0($convenio->cod_convenio, $req->dni);
            return response()->json(['message' => 'Registro correcto', 'ruta_anexo' => $rutaAnexo], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Registro fallido'], 400);
        }
    }

    /**
     * Descarga el anexo 0 obteniendo la ruta donde se encuentra el anexo.
     * @author Malena.
     */
    public function descargarAnexo0(Request $req)
    {
        $ruta_anexo = $req->get('ruta_anexo');
        // error_log($ruta_anexo);
        return response()->download($ruta_anexo);
    }

    /**
     * Registrar el convenio en la BBDD con los diferentes datos que necesitamos.
     * @author Malena
     * @param string $dniTutor, el dni del tutor que se encuentra logueado.
     * @param int $id_empresa, el id de la empresa que se registra.
     * @param boolean $privada true --> empresa privada; false --> empresa pública
     * @return Convenio convenio entre la empresa y el centro de estudios.
     */
    public function addConvenio(string $dniTutor, int $id_empresa, bool $privada)
    {
        //Consigo el centro de estudios a partir del Dni del tutor:
        $centroEstudios = $this->getCentroEstudiosFromProfesor($dniTutor);
        //Fabrico el codigo del convenio:
        $codConvenio = $this->generarCodigoConvenio($centroEstudios->cod_centro_convenio, $privada ? 'C' : 'A');
        $convenio = Convenio::create([
            'cod_convenio' => $codConvenio,
            'cod_centro' => $centroEstudios->cod,
            'id_empresa' => $id_empresa,
            'curso_academico_inicio' => '',
            'curso_academico_fin' => '',
            'firmado_director' => 0,
            'firmado_empresa' => 0,
            'ruta_anexo' => ''
        ]);
        return $convenio;
    }

    /**
     *@author Laura <lauramorenoramos97@gmail.com>
     * En esta funcion, enviamos el dni del director/jefe estudios para recoger el centro de estudios
     * al que pertenecen, con ese dato, recogemos los grupos de un centro de estudios desde la tabla
     * Tutorias, ya que esto tiene la finalidad de recoger los grupos de los distintos tutores del
     * centro para devolverlos y poder ver sus anexos en otra funcion.
     */
    public function verGrupos($dni)
    {
        $centroEstudios = Profesor::select('cod_centro_estudios')->where('dni', '=', $dni)->get();
        $grupos = Tutoria::select('cod_grupo', 'dni_profesor')->where('cod_centro', '=', $centroEstudios[0]->cod_centro_estudios)->get();
        return response()->json($grupos, 200);
    }


    public function verHistorialAnexos($dni_tutor)
    {
        $datos = array();
        $habilitado = 0;

        ///////////////////////////////ANEXO 0  Y 0A//////////////////////////////////////
        $Anexos0 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->whereIn('tipo_anexo', ['Anexo0', 'Anexo0A'])->where('ruta_anexo', 'like', "$dni_tutor%")->get();

        foreach ($Anexos0 as $a) {
            //Esto sirve para poner las barras segun el so que se este usando
            $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
            $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

            $convenioAux = $rutaAux[2];
            $convenioAux = explode('_', $convenioAux);
            $convenioAux = explode('.', $convenioAux[1]);
            $convenioAux = str_replace('-', DIRECTORY_SEPARATOR, $convenioAux[0]);

            $id_empresa = Convenio::select('id_empresa')->where('cod_convenio', '=', $convenioAux)->get();
            $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa[0]->id_empresa)->get();

            //FECHA
            $fechaAux = explode(':', $a->created_at);
            $fechaAux = explode(' ', $fechaAux[0]);

            $datos[] = [
                'nombre' => $rutaAux[1],
                'codigo' => $rutaAux[2],
                'empresa' => $empresa_nombre[0]->nombre,
                'firma_empresa' => $a->firmado_empresa,
                'firma_centro' => $a->firmado_director,
                'created_at' => $fechaAux[0]
            ];
        }


        ///////////////////////////////ANEXO 1//////////////////////////////////////
        $Anexos1 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->where('tipo_anexo', '=', 'Anexo1')->where('ruta_anexo', 'like', "$dni_tutor%")->distinct()->get();

        foreach ($Anexos1 as $a) {

            //Esto sirve para poner las barras segun el so que se este usando
            $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
            $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

            $nombreArchivo = $rutaAux[2];

            //Para sacar el id de la empresa
            $id_empresa = explode('_', $rutaAux[2]);
            $id_empresa = $id_empresa[1];

            $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa)->get();

            //FECHA
            $fechaAux = explode(':', $a->created_at);
            $fechaAux = explode(' ', $fechaAux[0]);

            //meter ese nombre en un array asociativo
            $datos[] = [
                'nombre' => 'Anexo1',
                'codigo' => $nombreArchivo,
                'empresa' => $empresa_nombre[0]->nombre,
                'firma_empresa' =>  $a->firmado_empresa,
                'firma_centro' => $a->firmado_director,
                'created_at' => $fechaAux[0]
            ];
        }
        return response()->json($datos, 200);
    }
}



