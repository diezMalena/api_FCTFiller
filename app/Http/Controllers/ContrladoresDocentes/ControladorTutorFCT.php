<?php

namespace App\Http\Controllers\ContrladoresDocentes;

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

class ControladorTutorFCT extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /***********************************************************************/
    #region Asignación de alumnos a empresas - Anexo I

    /***********************************************************************/
    #region Asignación de alumnos a empresas

    /**
     * Esta función se encarga de coger los datos dni y nombre de los
     * alumnos que no están asociados a ninguna empresa
     * asignados al dni del tutor que recibimos como parámetro.
     *
     * @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     * @param $dni es el dni del tutor
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
     * Esta función se encarga de coger el nombre del ciclo a partir del dni del tutor.
     *
     * @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     * @param $dni es el dni del tutor
     */
    public function solicitarNombreCiclo(string $dni)
    {
        $nombre = Tutoria::where('dni_profesor', '=', $dni)->get()[0]->cod_grupo;
        return response()->json($nombre, 200);
    }

    /**
     * Esta función se encarga de coger las empresas que solicitan el curso que está tutorizando
     * el profesor del que recibimos el dni, y dentro de esas empresas hay un array de alumnos que están
     * ya asociados a una empresa.
     *
     * @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     * @param $dni es el dni del tutor
     */
    public function solicitarEmpresasConAlumnos(string $dni)
    {
        $empresas = Grupo::join('empresa_grupo', 'empresa_grupo.cod_grupo', '=', 'grupo.cod')
            ->join('empresa', 'empresa.id', '=', 'empresa_grupo.id_empresa')
            ->join('tutoria', 'tutoria.cod_grupo', '=', 'grupo.cod')
            ->where('tutoria.dni_profesor', $dni)
            ->get();

        foreach ($empresas as  $empresa) {
            //Aquí rocojo el responsable de esa empresa. Si no hay, se saca al representante legal, que va a estar sí o sí
            $responsable = RolTrabajadorAsignado::join('trabajador', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                ->join('empresa', 'empresa.id', '=', 'trabajador.id_empresa')
                ->where([['rol_trabajador_asignado.id_rol', Parametros::RESPONSABLE_CENTRO], ['empresa.id', $empresa->id]])
                ->select(['trabajador.nombre', 'trabajador.dni'])
                ->first();
            //Por si acaso la empresa no tiene un responsable asignado, ponemos al representante legal
            if (!$responsable) {
                $responsable = RolTrabajadorAsignado::join('trabajador', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                    ->join('empresa', 'empresa.id', '=', 'trabajador.id_empresa')
                    ->where([['rol_trabajador_asignado.id_rol', Parametros::REPRESENTANTE_LEGAL], ['empresa.id', $empresa->id]])
                    ->select(['trabajador.nombre', 'trabajador.dni'])
                    ->first();
            }
            $empresa->nombre_responsable = $responsable->nombre;
            $empresa->dni_responsable = $responsable->dni;
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
     * Esta función se encarga de actualizar la empresa a la que están asignados
     * los alumnos.
     * @param $request tiene las empresas con los datos del id, el responsable, y un array con sus alumnos asiganados
     * que estos tienen dentro si van a fct, su dni, fecha de inicio de las prácticas y de finalización, el horario.
     * También tiene el array de alumnos sin empresa.
     * @author alvaro <alvarosantosmartin6@gmail.com>
     */
    public function actualizarEmpresaAsignadaAlumno(Request $request)
    {
        try {
            $cursoAcademico = Auxiliar::obtenerCursoAcademico();
            $alumnos_solos = $request->get('alumnos_solos');
            $empresas = $request->get('empresas');
            $dni_tutor = $request->get('dni_tutor');
            $this->borrarAnexosTablaFCT($dni_tutor);
            //elimita de la tabla fct los registros de los alumnos que ya no están en una empresa
            foreach ($alumnos_solos as $alumno) {
                Fct::where([['dni_alumno', $alumno['dni']], ['curso_academico', $cursoAcademico]])->delete();
            }

            //este for mete el nuevo nombre del responsable, se haya cambiado o no.
            //elimina el registro de la tabla fct de los alumnos que están en una empresa y
            //los inserta de nuevo con los cambios que se han hecho.
            foreach ($empresas as $empresa) {
                Trabajador::find($empresa['dni_responsable'])->update(['nombre' => $empresa['nombre_responsable']]);
                // $trabajador = Trabajador::find($empresa['dni_responsable']);
                // Auxiliar::updateUser($trabajador, $trabajador->email);
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

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Anexo I - Generación y gestión

    /**
     * A esta funcion le pasas el dni del tutor, con ese dni, busca las rutas de sus anexos en la tabla FCT
     * y borra esos anexos
     *@author LauraM <lauramorenoramos97@gmail.com>
     */
    public function borrarAnexosTablaFCT($dni_tutor)
    {
        $anexosArr = array();

        //buscar los anexos del tutor filtrando
        $anexosCreados = FCT::select('ruta_anexo')->where('ruta_anexo', 'like', "$dni_tutor%")->get();

        foreach ($anexosCreados as $a) {
            $anexosArr[] = $a->ruta_anexo;
        }

        $anexosArr = array_unique($anexosArr);

        foreach ($anexosArr as $a) {
            if (file_exists(public_path($a))) {
                unlink(public_path($a));
            }
        }
    }

    /**
     * A esta funcion le pasas el dni del tutor, con ese dni, busca las rutas de sus anexos en la tabla Anexos
     * y borra esos anexos, que esten habilitados
     */
    public function borrarAnexosTablaAnexos($tipoAnexo, $dni_tutor)
    {

        //borrar los anexos del tutor filtrando y tipo de Anexo
        Anexo::where('ruta_anexo', 'like', "$dni_tutor%")->where('tipo_anexo', '=', $tipoAnexo)->where('habilitado', '=', 1)->delete();
    }

    /**
     * Esta funcion nos permite rellenar el Anexo 1
     * @author LauraM <lauramorenoramos97@gmail.com>
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

        // Creación del .zip
        $zip = new ZipArchive;
        $nombreZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';

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
                    #region Recogida de datos
                    //Codigo del centro
                    $cod_centro = Profesor::select('cod_centro_estudios')->where('dni', $dni_tutor)->get();
                    //Numero de Convenio
                    $convenio = Convenio::select('cod_convenio')->where('id_empresa', '=', $id->id_empresa)->where('cod_centro', '=', $cod_centro[0]->cod_centro_estudios)->get();
                    //Nombre del ciclo
                    $nombre_ciclo = Grupo::select('nombre_ciclo')->where('cod', $grupo[0]->cod_grupo)->get();
                    //Codigo Ciclo
                    $cod_ciclo = Grupo::select('cod')->where('nombre_ciclo',  $nombre_ciclo[0]->nombre_ciclo)->get();

                    //ARCHIVO
                    $rutaOriginal = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . 'Anexo1';
                    $convenioAux = str_replace('/', '-', $convenio[0]->cod_convenio);
                    $AuxNombre = '_' . $id->id_empresa . '_' . $convenioAux . '_' . $cod_ciclo[0]->cod . '_' . $fecha->year . '_';
                    $rutaDestino = $dni_tutor  . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . 'Anexo1' . $AuxNombre;
                    $template = new TemplateProcessor($rutaOriginal . '.docx');

                    //Almacenamos las rutas de los anexos en la bbdd

                    foreach ($alumnos as $a) {
                        Fct::where('id_empresa', '=', $id->id_empresa)->where('dni_alumno', '=', $a->dni)->update(['ruta_anexo' => $rutaDestino . '.docx']);
                        Anexo::create(['tipo_anexo' => 'Anexo1', 'ruta_anexo' => $rutaDestino . '.docx']);
                    }

                    //Nombre de la empresa y Direccion
                    $nombre_empresa = Empresa::select('nombre', 'direccion')->where('id', $id->id_empresa)->get();
                    //Nombre del centro
                    $nombre_centro = CentroEstudios::select('nombre')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();
                    //Año del curso
                    $curso_anio = Carbon::createFromFormat('Y-m-d', Convenio::where('cod_convenio', $convenio[0]->cod_convenio)->select('fecha_ini')->get()->first()->fecha_ini)->year;

                    //Nombre del tutor
                    $nombre_tutor = Profesor::select('nombre', 'apellidos')->where('dni', $dni_tutor)->get();
                    //Responsable de la empresa
                    $responsable_empresa = Empresa::join('trabajador', 'trabajador.id_empresa', '=', 'empresa.id')
                        ->join('rol_trabajador_asignado', 'rol_trabajador_asignado.dni', '=', 'trabajador.dni')
                        ->select('trabajador.nombre', 'trabajador.apellidos')
                        ->where('trabajador.id_empresa', '=', $id->id_empresa)
                        ->where('rol_trabajador_asignado.id_rol', '=', Parametros::REPRESENTANTE_LEGAL)
                        ->get();

                    //representante del centro de trabajo
                    $representante_centro = Empresa::join('trabajador', 'trabajador.id_empresa', '=', 'empresa.id')
                        ->join('rol_trabajador_asignado', 'rol_trabajador_asignado.dni', '=', 'trabajador.dni')
                        ->select('trabajador.nombre', 'trabajador.apellidos')
                        ->where('trabajador.id_empresa', '=', $id->id_empresa)
                        ->where('rol_trabajador_asignado.id_rol', '=', Parametros::RESPONSABLE_CENTRO)
                        ->get();

                    //Directora
                    $directora = Profesor::join('rol_profesor_asignado', 'rol_profesor_asignado.dni', '=', 'profesor.dni')
                        ->select('profesor.nombre', 'profesor.apellidos')
                        ->where('profesor.cod_centro_estudios', '=', $cod_centro[0]->cod_centro_estudios)
                        ->where('rol_profesor_asignado.id_rol', '=', Parametros::DIRECTOR)
                        ->get();

                    //Ciudad del centro de estudios
                    $ciudad_centro_estudios = CentroEstudios::select('localidad')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();
                    #endregion

                    #Estilo tabla
                    $styleTable = array('borderSize' => 6, 'borderColor' => '888888', 'cellMargin' => 40);

                    #region Construcción de la tabla
                    $table = new Table(array('unit' => TblWidth::TWIP));
                    $table->addRow();
                    $table->addCell(1500, $styleTable)->addText('APELLIDOS Y NOMBRE');
                    $table->addCell(1500, $styleTable)->addText('D.N.I');
                    $table->addCell(1500, $styleTable)->addText('LOCALIDAD DE RESIDENCIA DEL ALUMNO/A (**)');
                    $table->addCell(1500, $styleTable)->addText('HORARIO DIARIO');
                    $table->addCell(1500, $styleTable)->addText('NUMERO HORAS');
                    $table->addCell(1500, $styleTable)->addText('FECHA DE COMIENZO');
                    $table->addCell(1500, $styleTable)->addText('FECHA DE FINALIZACION');
                    foreach ($alumnos as $a) {
                        $table->addRow();
                        $table->addCell(1500, $styleTable)->addText($a->apellidos . ' ' . $a->nombre);
                        $table->addCell(1500, $styleTable)->addText($a->dni);
                        $table->addCell(1500, $styleTable)->addText($a->localidad);
                        $table->addCell(1500, $styleTable)->addText($a->horario);
                        $table->addCell(1500, $styleTable)->addText($a->num_horas);
                        $table->addCell(1500, $styleTable)->addText($a->fecha_ini);
                        $table->addCell(1500, $styleTable)->addText($a->fecha_fin);
                    }
                    #endregion

                    #region Relleno de datos en Word
                    $auxPrefijos = ['convenio', 'centro', 'empresa', 'ciclo', 'responsable', 'centro', 'directora', 'representante', 'tutor'];
                    $auxDatos = [$convenio[0], $nombre_centro[0], $nombre_empresa[0], $nombre_ciclo[0], $responsable_empresa[0], $ciudad_centro_estudios[0], $directora[0], $representante_centro[0], $nombre_tutor[0]];

                    $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);
                    $datos = $datos +  [
                        'anio.curso' => $curso_anio,
                        'dia' => $fecha->day,
                        'mes' => Parametros::MESES[$fecha->month],
                        'year' => $fecha->year,
                    ];


                    $rutaCarpeta = public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1');
                    Auxiliar::existeCarpeta($rutaCarpeta);
                    $rutaCarpeta = public_path('tmp' . DIRECTORY_SEPARATOR . 'anexos');
                    Auxiliar::existeCarpeta($rutaCarpeta);

                    $template->setValues($datos);
                    $template->setComplexBlock('{table}', $table);
                    $template->saveAs($rutaDestino . '.docx');
                    #endregion
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
    public function montarZip(String $rutaArchivo, ZipArchive $zip, String $rutaZip)
    {
        if ($zip->open(public_path($rutaZip), ZipArchive::CREATE)) {

            $files = File::files(public_path($rutaArchivo));
            foreach ($files as $value) {
                $relativeNameZipFile = basename($value);
                $zip->addFile($value, $relativeNameZipFile);
            }
            $zip->close();
        }
        return $rutaZip;
    }

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD de anexos


    /**
     * Esta funcion devuelve los anexos de un tutor, ya sean historicos o no
     * sacando lo que va a mostrar de la tabla Anexos
     * @param [type] $dni_tutor es el dni del tutor
     * @param [type] $habilitado, indica si vamos a sacar anexos habilitados, o no(historicos)
     * @return void
     * @author Laura <lauramorenoramos97@gmail.com>
     */
    public function verAnexos($dni_tutor, $habilitado)
    {
        $datos = array();

        #region ANEXO 0 - 0A
        $Anexos0 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->whereIn('tipo_anexo', ['Anexo0', 'Anexo0A'])->where('ruta_anexo', 'like', "$dni_tutor%")->get();

        foreach ($Anexos0 as $a) {
            if (file_exists(public_path($a->ruta_anexo))) {
                //Esto sirve para poner las barras segun el so que se este usando
                $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
                $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

                $id_empresa = Convenio::select('id_empresa')->where('ruta_anexo', 'like', "$a->ruta_anexo")->first();

                $empresa_nombre = [];
                if ($id_empresa) {
                    $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa->id_empresa)->get();
                }

                //FECHA
                $fechaAux = explode(':', $a->created_at);
                $fechaAux = explode(' ', $fechaAux[0]);

                if (!$empresa_nombre) {
                    $datos[] = [
                        'nombre' => $rutaAux[1],
                        'codigo' => $rutaAux[2],
                        'empresa' => ' ',
                        'alumno' => ' ',
                        'firma_empresa' => $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                } else {
                    $datos[] = [
                        'nombre' => $rutaAux[1],
                        'codigo' => $rutaAux[2],
                        'empresa' => $empresa_nombre[0]->nombre,
                        'alumno' => ' ',
                        'firma_empresa' => $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                }
            }
        }

        #endregion
        #region Anexo I
        $Anexos1 = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->where('tipo_anexo', '=', 'Anexo1')->where('ruta_anexo', 'like', "$dni_tutor%")->distinct()->get();

        foreach ($Anexos1 as $a) {
            if (file_exists(public_path($a->ruta_anexo))) {
                //Esto sirve para poner las barras segun el so que se este usando
                $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
                $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);
                $nombreArchivo = $rutaAux[2];

                //Para sacar el id de la empresa
                $id_empresa = explode('_', $rutaAux[2]);
                $id_empresa = $id_empresa[1];

                $empresa_nombre = [];
                if ($id_empresa) {
                    $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa)->get();
                }

                //FECHA
                $fechaAux = explode(':', $a->created_at);
                $fechaAux = explode(' ', $fechaAux[0]);

                //meter ese nombre en un array asociativo
                if (!$empresa_nombre) {
                    $datos[] = [
                        'nombre' => 'Anexo1',
                        'codigo' => $nombreArchivo,
                        'empresa' => ' ',
                        'alumno' => ' ',
                        'firma_empresa' =>  $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                } else {
                    $datos[] = [
                        'nombre' => 'Anexo1',
                        'codigo' => $nombreArchivo,
                        'empresa' => $empresa_nombre[0]->nombre,
                        'alumno' => ' ',
                        'firma_empresa' =>  $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                }
            }
        }
        #endregion

        #region Anexo II Y IV
        $AnexosIIYIV = Anexo::select('tipo_anexo', 'firmado_empresa', 'firmado_director', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->whereIn('tipo_anexo', ['Anexo2', 'Anexo4'])->where('ruta_anexo', 'like', "$dni_tutor%")->get();

        foreach ($AnexosIIYIV as $a) {
            if (file_exists(public_path($a->ruta_anexo))) {
                //Esto sirve para poner las barras segun el so que se este usando
                $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
                $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);
                $nombreArchivo = $rutaAux[2];

                //Empresa ID y Nombre
                $id_empresa = explode('_', $rutaAux[2]);
                $id_empresa = $id_empresa[2];

                $empresa_nombre = [];
                if ($id_empresa) {
                    $empresa_nombre = Empresa::select('nombre')->where('id', '=', $id_empresa)->get();
                }

                //DNI alumno
                $dniAlumno = explode('_', $a->ruta_anexo);
                $dniAlumno = $dniAlumno[1];

                //FECHA
                $fechaAux = explode(':', $a->created_at);
                $fechaAux = explode(' ', $fechaAux[0]);

                //meter ese nombre en un array asociativo
                if (!$empresa_nombre) {
                    $datos[] = [
                        'nombre' => $rutaAux[1],
                        'codigo' => $rutaAux[2],
                        'empresa' => ' ',
                        'alumno' => $dniAlumno,
                        'firma_empresa' =>  $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                } else {
                    $datos[] = [
                        'nombre' => $rutaAux[1],
                        'codigo' => $rutaAux[2],
                        'empresa' => $empresa_nombre[0]->nombre,
                        'alumno' => $dniAlumno,
                        'firma_empresa' =>  $a->firmado_empresa,
                        'firma_centro' => $a->firmado_director,
                        'firma_alumno' => 0,
                        'created_at' => $fechaAux[0]
                    ];
                }
            }
        }
        #endregion

        #region Anexo XV
        $AnexosXV = Anexo::select('tipo_anexo', 'firmado_alumno', 'ruta_anexo', 'created_at')->where('habilitado', '=', $habilitado)->where('tipo_anexo', '=', 'AnexoXV')->where('ruta_anexo', 'like', "$dni_tutor%")->distinct()->get();

        foreach ($AnexosXV as $a) {
            if (file_exists(public_path($a->ruta_anexo))) {
                //Esto sirve para poner las barras segun el so que se este usando
                $rutaAux = str_replace('/', DIRECTORY_SEPARATOR, $a->ruta_anexo);
                $rutaAux = explode(DIRECTORY_SEPARATOR, $rutaAux);

                //DNI alumno
                $dniAlumno = explode('_', $a->ruta_anexo);
                $dniAlumno = $dniAlumno[1];

                $nombreArchivo = $rutaAux[2];

                //FECHA
                $fechaAux = explode(':', $a->created_at);
                $fechaAux = explode(' ', $fechaAux[0]);

                //meter ese nombre en un array asociativo
                $datos[] = [
                    'nombre' => 'AnexoXV',
                    'codigo' => $nombreArchivo,
                    'empresa' => ' ',
                    'alumno' => $dniAlumno,
                    'firma_empresa' => 0,
                    'firma_centro' => 0,
                    'firma_alumno' => $a->firmado_alumno,
                    'created_at' => $fechaAux[0]
                ];
            }
        }

        #endregion
        return response()->json($datos, 200);
    }

    /***********************************************************************/
    #region listar anexos
    /**
     * Esta función nos permite obtener los Anexos1 de la tabla FCT para proporcionarsela
     * a la vista FCT y así poder descargar los anexos filtrando por id_empresa
     * también nos facilita poder subir los anexos con el nombre correspondiente de archivo.
     * @author Laura <lauramorenoramos97@gmail.com>
     */
    public function listarAnexos1($dni_tutor)
    {

        $datos = array();
        $Anexos1 = FCT::select('ruta_anexo', 'id_empresa')->where('ruta_anexo', 'like', "$dni_tutor%")->distinct()->get();

        if (count($Anexos1) > 0) {
            foreach ($Anexos1 as $a) {
                if (file_exists(public_path($a->ruta_anexo))) {
                    $empresa = Empresa::select('nombre')->where('id', '=', $a->id_empresa)->get();
                    $nombreArchivo = explode(DIRECTORY_SEPARATOR, $a->ruta_anexo);

                    $datos[] = [
                        'id_empresa' => $empresa[0]->nombre,
                        'codigo' => $nombreArchivo[2]
                    ];
                }
            }

            return response()->json($datos, 200);
        } else {
            return response()->json($datos, 204);
        }
    }

    /**
     * Esta función nos permite obtener los Anexos2 y 4 de la tabla Anexo para proporcionarsela
     * a la vista AnexosII y IV y así poder descargar los anexos y subirlos con un nombre adecuado.
     * @author Laura <lauramorenoramos97@gmail.com>
     */
    public function listarAnexosIIYIV($dni_tutor)
    {

        $datos = array();
        $AnexosIIYIV = Anexo::where('ruta_anexo', 'like', "$dni_tutor%")->whereIn('tipo_anexo', ['Anexo2', 'Anexo4'])->distinct()->get();

        if (count($AnexosIIYIV) > 0) {
            foreach ($AnexosIIYIV as $a) {
                if (file_exists(public_path($a->ruta_anexo))) {
                    //Desgloso la ruta por la barra para extraer el nombre del archivo
                    $nombreArchivo = explode(DIRECTORY_SEPARATOR, $a->ruta_anexo);
                    //Saco el dni del alumno desglosando el nombre del archivo por la _ y obtengo su nombre y apellidos
                    $alumno_dni = explode('_', $nombreArchivo[2]);
                    $alumno_dni = $alumno_dni[1];
                    $alumno_nombre = Alumno::select('nombre', 'apellidos')->where('dni', '=', $alumno_dni)->get();


                    $datos[] = [
                        'tipo_anexo' => $a->tipo_anexo,
                        'codigo' => $nombreArchivo[2],
                        'alumno_dni' => $alumno_dni,
                        'alumno_nombre' => $alumno_nombre[0]->nombre . ' ' . $alumno_nombre[0]->apellidos
                    ];
                }
            }

            return response()->json($datos, 200);
        } else {
            return response()->json($datos, 204);
        }
    }

    #endregion
    /***********************************************************************/

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
     * @author Laura <lauramorenoramos97@gmail.com>
     * @param Request $val: dni_tutor y el codigo es el nombre del archivo. EJ: Anexo1_13_VdG-C3-22_2DAW_2022_.docx
     * @return void
     */
    public function descargarAnexo(Request $val)
    {
        // Request
        $dni_tutor = $val->get('dni_tutor');
        $cod_anexo = $val->get('codigo');

        // Otras variables
        $codAux = explode("_", $cod_anexo);
        $rutaOriginal = '';
        $rutaOriginal = public_path($dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo);
        $rutaOriginal  = str_replace('/', DIRECTORY_SEPARATOR, $rutaOriginal);

        return Response::download($rutaOriginal);
    }

    /**
     * Esta funcion te permite eliminar un fichero de una carpeta y de la base de datos
     * @author Laura <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function eliminarAnexo($dni_tutor, $cod_anexo)
    {
        $codAux = explode("_", $cod_anexo);
        Anexo::where('ruta_anexo', 'like', "%$cod_anexo")->delete();
        if ($codAux[0] == 'Anexo1') {
            //Eliminar un fichero
            FCT::where('ruta_anexo', 'like', "%$cod_anexo")->delete();
            unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . $cod_anexo);
        } else {
            if ($codAux[0] == 'Anexo0' || $codAux[0] == 'Anexo0A') {
                unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo);
                Convenio::where('ruta_anexo', 'like', "%$cod_anexo")->delete();
            } else {
                unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo);
            }
        }
        return response()->json(['message' => 'Archivo eliminado'], 200);
    }

    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * Esta funcion sirve para deshabilitar un anexo y borrar su ruta de la tabla correspondiente
     */
    public function deshabilitarAnexo(Request $val)
    {
        // Request
        $cod_anexo = $val->get('cod_anexo');

        // Deshabilitamos anexo de Anexos
        Anexo::where('ruta_anexo', 'like', "%$cod_anexo")->update([
            'habilitado' => 0,
        ]);

        // Vacio ruta de FCT
        $codAux = explode("_", $cod_anexo);
        if ($codAux[0] == 'Anexo1') {
            FCT::where('ruta_anexo', 'like', "%$cod_anexo")->update([
                'ruta_anexo' => '',
            ]);
        }

        // Response
        return response()->json(['message' => 'Archivo deshabilitado'], 200);
    }

    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * Esta funcion sirve para habilitar un anexo y añadir su ruta de la tabla correspondiente
     */
    public function habilitarAnexo(Request $val)
    {

        $cod_anexo = $val->get('cod_anexo');
        $dni_tutor = $val->get('dni_tutor');

        Anexo::where('ruta_anexo', 'like', "%$cod_anexo")->update([
            'habilitado' => 1,
        ]);

        $codAux = explode("_", $cod_anexo);
        //$codAux[0] es el tipo del Anexo
        if ($codAux[0] == 'Anexo1') {
            FCT::where('id_empresa', '=', $codAux[1])->update([
                'ruta_anexo' => $dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo,
            ]);
        } else {
            if ($codAux[0] == 'Anexo0' || $codAux[0] == 'Anexo0A') {

                $convenio = explode('_', $cod_anexo);
                $convenio = explode('.', $convenio[1]);
                $convenio = str_replace('-', '/', $convenio[0]);

                Convenio::where('cod_convenio', '=', $convenio)->update([
                    'ruta_anexo' => $dni_tutor . DIRECTORY_SEPARATOR . $codAux[0] . DIRECTORY_SEPARATOR . $cod_anexo,
                ]);
            }
        }
    }

    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * Esta funcion permite descargar todos los anexos del crud de anexos del tutor, menos el 3
     *
     * @param Request $val
     * @return void
     */
    public function descargarTodo(Request $val)
    {
        // Request
        $dni = $val->get('dni_tutor');
        $habilitado =  $val->get('habilitado');

        // Otras variables
        $AuxNombre = Str::random(7);
        $rutaZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';
        $nombreZip = $this->montarZipCrud($dni, $rutaZip, $habilitado);

        return response()->download(public_path($nombreZip));
    }

    /**
     * Esta funcion sirve para generar el zip de todos los anexos del crud de anexos
     * Miramos los anexos de la carpeta de anexos del tutor, buscamos ese anexo habilitado o no habilitado, segun si
     * la consulta se hace desde el crud de anexos o desde el historial  y comprobamos
     * si este existe en el directorio, en tal caso se añade al zip
     * Comprueba que el directorio en el que se busca el archivo existe y sino, lo crea.
     * @author Laura <lauramorenoramos97@gmail.com>
     * @param String $dni_tutor, el dni del tutor, sirve para ubicar su directorio
     * @param ZipArchive $zip , el zip donde se almacenaran los archivos
     * @param String $nombreZip, el nombre que tendrá el zip
     * @return void
     */
    public function montarZipCrud(String $dni_tutor, String $rutaZip, $habilitado)
    {
        $zip = new ZipArchive;

        Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
        $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
        if ($zip->open(public_path($rutaZip), ZipArchive::CREATE)) {

            #region Anexo 0
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0'));
            foreach ($files as $value) {
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo0')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
            #region Anexo 0A
            Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0A'));
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0A'));
            foreach ($files as $value) {
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo0A')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion

            #region Anexo I
            Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
            foreach ($files as $value) {

                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo1')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
            Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2'));
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo2'));
            #region Anexo II
            foreach ($files as $value) {
                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo2')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
            Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo4'));
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo4'));
            #region Anexo IV
            foreach ($files as $value) {
                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo4')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion

            #region Anexo XV
            Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'AnexoXV'));
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'AnexoXV'));
            foreach ($files as $value) {
                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'AnexoXV')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
            $zip->close();
        }
        return $rutaZip;
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD de empresas

    /**
     * Devuelve todas las empresas del sistema con un atributo booleano 'convenio',
     * que adquiere true cuando hay convenio entre el centro del profesor y la empresa
     *
     * @param string $dniProfesor el DNI del profesor
     * @return response JSON con la colección de empresas
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function getEmpresasFromProfesor(string $dniProfesor)
    {
        try {
            $codCentro = Profesor::find($dniProfesor)->cod_centro_estudios;
            $empresas = Empresa::all();
            foreach ($empresas as $empresa) {
                $empresa->convenio = Convenio::where('cod_centro', $codCentro)
                    ->where('id_empresa', $empresa->id)->first();
                $empresa->representante = $this->getRepresentanteLegal($empresa->id);
            }
            return response()->json($empresas, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Fallo al obtener las empresas'], 400);
        }
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
                'cp' => $req->cp,
                'es_privada' => $req->es_privada
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
            $email = Trabajador::find($req->dni)->email;
            Trabajador::where('dni', $req->dni)->update([
                'nombre' => $req->nombre,
                'apellidos' => $req->apellidos,
                'email' => $req->email
            ]);
            Auxiliar::updateUser(Trabajador::find($req->dni), $email);
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
     * Registra una empresa y su representante en la base de datos,
     * así como los ciclos de interés para la empresa
     *
     * @return Response JSON con la respuesta del servidor: 200 -> OK, 400 -> error
     * @author @Malena
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function addDatosEmpresa(Request $req)
    {
        try {
            $empresa = Empresa::create($req->empresa);
            $repre_aux = $req->representante;
            $repre_aux["id_empresa"] = $empresa->id;
            $repre_aux["password"] = Hash::make("superman");
            $representante = Trabajador::create($repre_aux);
            Auxiliar::addUser($representante, "trabajador");
            RolTrabajadorAsignado::create([
                'dni' => $representante->dni,
                'id_rol' => Parametros::REPRESENTANTE_LEGAL,
            ]);
            //Metemos al representante legal como responsable del centro para que haya alguno en la asignación
            RolTrabajadorAsignado::create([
                'dni' => $representante->dni,
                'id_rol' => Parametros::RESPONSABLE_CENTRO,
            ]);
            $this->asignarCiclosEmpresa($empresa->id, $req->ciclos);
            return response()->json(['message' => 'Registro correcto'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => 'Registro fallido'], 400);
        }
    }

    /**
     * Registra la asignación de ciclos de interés para una empresa dada
     *
     * @param int $idEmpresa ID de la empresa
     * @param array[string] $codCiclos array con los códigos de los ciclos de interés para la empresa
     * @return void|Response Si hay error, JSON con una respuesta de error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function asignarCiclosEmpresa($idEmpresa, $codCiclos)
    {
        try {
            EmpresaGrupo::where('id_empresa', $idEmpresa)->delete();
            foreach ($codCiclos as $cod) {
                EmpresaGrupo::create(['id_empresa' => $idEmpresa, 'cod_grupo' => $cod]);
            }
        } catch (Exception $ex) {
            return response()->json(['message' => 'Fallo al asignar los grupos'], 400);
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Gestión de convenios y acuerdos - Anexo 0 y 0A

    /***********************************************************************/
    #region Anexo 0 y 0A

    /**
     * Genera el Anexo 0, convenio entre una empresa y un centro
     * @param string $codConvenio el código del convenio entre la empresa y el centro
     * @param string $dniTutor el DNI del tutor que está loggeado en el sistema
     * @return string la ruta en la que se guarda el anexo
     *
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> 02/06/22 -> Generación de anexo no automática
     */
    public function generarAnexo0(Request $req)
    {
        // Primero consigo  todos los datos que hay que rellenar en el convenio
        $convenio = new Convenio($req->convenio);
        $centroEstudios = new CentroEstudios($req->centro);
        $director = new Profesor($req->director);
        $empresa = new Empresa($req->empresa);
        $representante = new Trabajador($req->representante);

        // Construyo el array con todos los datos
        $auxPrefijos = ['director', 'centro', 'representante', 'empresa'];
        $auxDatos = [$director, $centroEstudios, $representante, $empresa];
        $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

        // Ahora extraigo los datos de fecha
        $fecha = new Carbon($convenio->fecha_ini);
        $datos['dia'] = $fecha->day;
        $datos['mes'] = AuxiliarParametros::MESES[$fecha->month];
        $datos['anio'] = $fecha->year % 100;
        $datos['cod_convenio'] = $convenio->cod_convenio;

        // Esta variable se usa sólo para el nombre del archivo
        $codConvenioAux = str_replace('/', '-', $convenio->cod_convenio);

        // Voy a necesitar el DNI del tutor, así que lo obtengo
        $dniTutor = Profesor::where('email', $req->user()->email)->first()->dni;

        // Ahora genero el Word en sí
        // Establezco las variables que necesito
        $nombrePlantilla = $empresa->es_privada == 1 ? 'Anexo0' : 'Anexo0A';
        $rutaOrigen = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . $nombrePlantilla . '.docx';
        Auxiliar::existeCarpeta(public_path($dniTutor . DIRECTORY_SEPARATOR . $nombrePlantilla));
        $rutaDestino =  $dniTutor . DIRECTORY_SEPARATOR . $nombrePlantilla . DIRECTORY_SEPARATOR . $nombrePlantilla . '_' . $codConvenioAux . '.docx';
        // Creo la plantilla y la relleno
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);

        return $rutaDestino;
    }

    /**
     * Descarga el anexo 0 obteniendo la ruta donde se encuentra el anexo.
     * @author Malena.
     * 03/06/22 - Añadido control de errores
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function descargarAnexo0(Request $req)
    {
        $ruta_anexo = $req->get('ruta_anexo');
        if (file_exists($ruta_anexo)) {
            return response()->download($ruta_anexo);
        } else {
            return response()->json(['message' => 'Not Found'], 404);
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Gestión de convenios y acuerdos

    /**
     * Registrar el convenio en la BBDD con los diferentes datos que necesitamos.
     *
     * @param Request $req Contiene todos los datos que llegan desde el cliente
     * @return Response JSON con el código de respuesta del servidor
     * @author Malena
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function addConvenio(Request $req)
    {
        try {
            // Creamos el convenio en su tabla correspondiente
            $convenio = Convenio::create($req->convenio);
            // Y guardamos o generamos el fichero correspondiente, según el cliente haya o no subido el archivo
            if ($req->subir_anexo) {
                $dniTutor = Profesor::where('email', $req->user()->email)->first()->dni;
                $tipoAnexo = $req->empresa['es_privada'] == 1 ? 'Anexo0' : 'Anexo0A';
                $codConvenioAux = str_replace('/', '-', $convenio->cod_convenio);
                $carpeta = $dniTutor . DIRECTORY_SEPARATOR . $tipoAnexo;
                $archivo = $tipoAnexo . '_' . $codConvenioAux;
                $ruta = Auxiliar::guardarFichero($carpeta, $archivo, $req->anexo);
            } else {
                $ruta = $this->generarAnexo0($req);
            }
            // Acutalizamos la ruta en la tabla convenios, que es la unión con la tabla anexos
            Convenio::where('cod_convenio', $convenio->cod_convenio)->update(['ruta_anexo' => $ruta]);
            // Y creamos el registro en la tabla de anexos
            Anexo::create([
                'tipo_anexo' => $req->empresa['es_privada'] == 1 ? 'Anexo0' : 'Anexo0A',
                'ruta_anexo' => $ruta
            ]);
            return response()->json(['ruta_anexo' => $ruta], 201);
        } catch (QueryException $ex) {
            // Duplicado de una clave única
            if ($ex->errorInfo[1] == 1062) {
                return response()->json($ex->errorInfo[2], 409);
            } else {
                return response()->json($ex->errorInfo[2], 400);
            }
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * Actualiza un convenio existente
     *
     * @param Request $req Contiene todos los datos que llegan desde el cliente
     * @return Response JSON con el código de respuesta del servidor
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function updateConvenio(Request $req)
    {
        try {
            // Guardamos el código de convenio original, en caso de que haya cambiado
            if (array_key_exists('cod_convenio_anterior', $req->convenio)) {
                $codAnterior = $req->convenio['cod_convenio_anterior'];
            } else {
                $codAnterior = $req->convenio['cod_convenio'];
            }
            #region Guardado del anexo
            // Eliminamos el archivo que había antes
            $rutaAnterior = Convenio::where('cod_convenio', $codAnterior)->first()->ruta_anexo;
            Auxiliar::borrarFichero(($rutaAnterior));
            // Y generamos o guardamos (según lo que recibamos del cliente) uno nuevo
            if ($req->subir_anexo) {
                $dniTutor = Profesor::where('email', $req->user()->email)->first()->dni;
                $tipoAnexo = $req->empresa['es_privada'] == 1 ? 'Anexo0' : 'Anexo0A';
                $codConvenioAux = str_replace('/', '-', $req->convenio['cod_convenio']);
                $carpeta = $dniTutor . DIRECTORY_SEPARATOR . $tipoAnexo;
                $archivo = $tipoAnexo . '_' . $codConvenioAux;
                $ruta = Auxiliar::guardarFichero($carpeta, $archivo, $req->anexo);
            } else {
                $ruta = $this->generarAnexo0($req);
            }
            #endregion
            #region Actualización de la base de datos (Convenio y Anexo)
            Convenio::where('cod_convenio', $codAnterior)->update([
                'cod_convenio' => $req->convenio['cod_convenio'],
                'fecha_ini' => $req->convenio['fecha_ini'],
                'fecha_fin' => $req->convenio['fecha_fin'],
                'ruta_anexo' => $ruta
            ]);
            Anexo::where('ruta_anexo', $rutaAnterior)->update(['ruta_anexo' => $ruta]);
            #endregion
            return response()->json(['ruta_anexo' => $ruta], 201);
        } catch (QueryException $ex) {
            // Duplicado de una clave única
            if ($ex->errorInfo[1] == 1062) {
                return response()->json($ex->errorInfo[2], 409);
            } else {
                return response()->json($ex->errorInfo[2], 400);
            }
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * Elimina un convenio de la base de datos y deshabilita el anexo correspondiente
     *
     * @param String $cod El código de convenio con las '/' sustituidas por '-'
     * @return Response JSON con el código de estado HTTP correspondiente
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function deleteConvenio(String $cod)
    {
        $codAux = str_replace('-', '/', $cod);
        try {
            Anexo::where('ruta_anexo', Convenio::where('cod_convenio', $codAux)->first()->ruta_anexo)->update(['habilitado' => 0]);
            Convenio::where('cod_convenio', $codAux)->delete();
            return response()->json(['message' => 'Anulación de convenio correcta'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Funciones auxiliares - sin response

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
     * Devuelve el centro de estudios asociado a un determinado código de convenio.
     * Contiene también información del director
     *
     * @param string $codConvenio el código de convenio
     * @return CentroEstudios una colección con la información del centro de estudios
     * @author @DaniJCoello
     */
    public function getCentroEstudiosFromConvenio(string $codConvenio)
    {
        $centro = CentroEstudios::find(Convenio::where('cod_convenio', $codConvenio)->first()->cod_centro);
        $centro->director = $this->getDirectorCentroEstudios($centro->cod);

        return $centro;
    }

    /**
     * Devuelve el director de un centro de estudios
     *
     * @param string $codCentroEstudios el código irrepetible del centro de estudios
     * @return Profesor una colección con la información del director
     * @author @DaniJCoello
     */
    public function getDirectorCentroEstudios(string $codCentroEstudios)
    {
        return Profesor::whereIn('dni', RolProfesorAsignado::where('id_rol', 1)->get('dni'))->where('cod_centro_estudios', $codCentroEstudios)->first();
    }

    /**
     * Devuelve la empresa asociada a un CIF,
     * con los datos del representante legal dentro
     *
     * @param string $cif el CIF de la empresa
     * @return Empresa una colección con la información de la empresa
     * @author @DaniJCoello
     */
    public function getEmpresaFromCIF(string $cif)
    {
        $empresa = Empresa::where('cif', $cif)->first();
        $empresa->representante = $this->getRepresentanteLegal($empresa->id);
        return $empresa;
    }

    /**
     * Devuelve la empresa asociada a una ID de la base de datos,
     * con los datos del representante legal dentro
     *
     * @param int $id la ID autonumérica de la empresa en la base de datos de la aplicación
     * @return Empresa una colección con la información de la empresa
     * @author @DaniJCoello
     */
    public function getEmpresaFromID(int $id)
    {
        $empresa = Empresa::find($id);
        $empresa->representante = $this->getRepresentanteLegal($empresa->id);
        return $empresa;
    }

    /**
     * Devuelve la empresa asociada a un código de convenio
     * La empresa contiene los datos del representante legal
     *
     * @param string $codConvenio el código del convenio
     * @return Empresa una colección con la información de la empresa y el representante legal
     * @author @DaniJCoello
     */
    public function getEmpresaFromConvenio(string $codConvenio)
    {
        $empresa = Empresa::find(Convenio::where('cod_convenio', $codConvenio)->first()->id_empresa);
        $empresa->representante = $this->getRepresentanteLegal($empresa->id);

        return $empresa;
    }

    /**
     * Devuelve el representante legal de una empresa
     *
     * @param int $id la ID autonumérica de la empresa en la base de datos de la aplicación
     * @return Trabajador un objeto con los datos del representante legal
     * @author @DaniJCoello
     */
    public function getRepresentanteLegal(int $id)
    {
        return Trabajador::whereIn('dni', RolTrabajadorAsignado::where('id_rol', 1)->get('dni'))->where('id_empresa', $id)->first();
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Funciones auxiliares - response

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
     * Devuelve en una response JSON la empresa asociada al ID que se le pasa como argumento,
     * con los datos de su representante legal dentro
     *
     * @param int $id ID único de la empresa
     * @return response JSON con los datos de la empresa y su representante legal
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function getEmpresaID(int $id)
    {
        return response()->json($this->getEmpresaFromId($id), 200);
    }

    /**
     * Devuelve en una response JSON la empresa asociada al ID que se le pasa como argumento,
     * con los datos de su representante legal dentro
     *
     * @param int $id ID único de la empresa
     * @return response JSON con los datos de la empresa y su representante legal
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function getEmpresaCIF(string $cif)
    {
        return response()->json($this->getEmpresaFromCif($cif), 200);
    }

    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * En esta funcion, enviamos el dni del director/jefe estudios para recoger el centro de estudios
     * al que pertenecen, con ese dato, recogemos los grupos de un centro de estudios desde la tabla
     * Tutorias, ya que esto tiene la finalidad de recoger los grupos de los distintos tutores del
     * centro para devolverlos y poder ver sus anexos en otra funcion.
     * @author Laura <lauramorenoramos97@gmail.com>
     */
    public function verGrupos($dni)
    {
        $centroEstudios = Profesor::select('cod_centro_estudios')->where('dni', '=', $dni)->get();

        $grupos[] = [
            "cod_grupo" => "Mis Anexos",
            "dni_profesor" => $dni
        ];

        foreach (Tutoria::select('cod_grupo', 'dni_profesor')->where('cod_centro', '=', $centroEstudios[0]->cod_centro_estudios)->get() as $g) {
            $grupos[] = $g;
        }

        return response()->json($grupos, 200);
    }

    public function getCentroEstudiosFromConvenioJSON(string $codConvenio)
    {
        return response()->json($this->getCentroEstudiosFromConvenio($codConvenio), 200);
    }

    #endregion
    /***********************************************************************/

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


    /***********************************************************************/
    #region Funciones auxiliares - response

    /**
     * A esta funcion, le pasas el tipo de anexo, el dni del usuario , el fichero a subir y el nombre de fichero
     * y te sube el documento al servidor a la carpeta que corresponde.
     * Hace varias comprobaciones antes, como por ejemplo, si la carpeta a la que vas a subir el
     * archivo existe, si no existe, la crea.
     * Tambien comprueba que el archivo exista en bbdd , si no existe, lo crea, pero si existe,
     * no replicará información
     *
     * @param Request $req
     * @return void
     *@author Laura <lauramorenoramos97@gmail.com>
     */
    public function subirAnexoEspecifico(Request $req)
    {
        //Controlador
        $controlador = new ControladorAlumno();

        //Request
        $fichero = $req->get('file');
        $dni = $req->get('dni');
        $tipoAnexo = $req->get('tipoAnexo');
        $nombreArchivo = $req->get('nombreArchivo');

        //Archivos
        $rutaCarpeta = $dni . DIRECTORY_SEPARATOR . $tipoAnexo;


        if ($tipoAnexo == 'AnexoXV') {
            //Con el AnexoXV hay que hacer cosas especiales solo para el
            $controlador->subirAnexoXV($dni, $tipoAnexo, $nombreArchivo, $fichero, $rutaCarpeta);
        } else {
            try {

                //Comprobamos que existe la carpeta donde lo vamos a depositar, y sino existe, se crea
                Auxiliar::existeCarpeta($rutaCarpeta);

                //Abrimos el flujo de escritura para guardar el fichero
                $flujo = fopen($rutaCarpeta . DIRECTORY_SEPARATOR .  $nombreArchivo, 'wb');

                //Dividimos el string en comas
                // $datos[ 0 ] == "data:type/extension;base64"
                // $datos[ 1 ] == <actual base64 file>

                $datos = explode(',', $fichero);

                if (count($datos) > 1) {
                    fwrite($flujo, base64_decode($datos[1]));
                } else {
                    return false;
                }
                fclose($flujo);

                //También hay que añadir dicho anexo a la base de datos, pero no, sin antes comprobar si existe, por que sino, se duplicarian los datos,
                //lo buscamos sin extension, por que si existe, se actualizara con la nueva ruta y si no existe,
                //se crea, asi si es un .pdf u otra extension se actualiza
                $extension = explode('/', mime_content_type($fichero))[1];
                $rutaParaBBDD = $rutaCarpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
                $archivoNombreSinExtension = explode('.', $nombreArchivo);
                $rutaParaBBDDSinExtension = $rutaCarpeta . DIRECTORY_SEPARATOR . $archivoNombreSinExtension[0];
                $existeAnexo = Anexo::where('tipo_anexo', '=', $tipoAnexo)->where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->get();


                //Excluyo Anexos2 y 4 por que esto ya lo hacen ellos de manera especifica en su propia función
                if ($nombreArchivo != 'Anexo2.docx' && $nombreArchivo != 'Anexo4.docx') {
                    if (count($existeAnexo) == 0) {
                        Anexo::create(['tipo_anexo' => $tipoAnexo, 'ruta_anexo' => $rutaParaBBDD]);

                        //Firma
                        $this->firmarAnexo($dni, $rutaParaBBDD, $extension);
                    } else {

                        Anexo::where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->update([
                            'ruta_anexo' => $rutaParaBBDD,
                        ]);

                        //Añadidos a BBDD especiales
                        if ($tipoAnexo == 'Anexo1') {
                            FCT::where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->update([
                                'ruta_anexo' => $rutaParaBBDD,
                            ]);
                        } else {
                            if ($tipoAnexo == 'Anexo0' || $tipoAnexo == 'Anexo0A') {
                                Convenio::where('ruta_anexo', 'like', "$rutaParaBBDDSinExtension%")->update([
                                    'ruta_anexo' => $rutaParaBBDD,
                                ]);
                            }
                        }

                        //Firma
                        $this->firmarAnexo($dni, $rutaParaBBDD, $extension);
                    }
                }

                //Lo ponemos con su nombre original, en un directorio que queramos
                $fichero->move(public_path($rutaCarpeta), $nombreArchivo);
            } catch (\Throwable $th) {
                return false;
            }
        }
    }

    public function firmarAnexo($dni, $rutaParaBBDD, $extension)
    {
        $alumno = Alumno::where('dni', '=', $dni)->get();
        $profesor = Profesor::where('dni', '=', $dni)->get();
        $empresa = Trabajador::where('dni', '=', $dni)->get();

        if ($extension == 'pdf') {
            if (count($alumno) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_alumno' => 1,
                ]);
            }

            if (count($profesor) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_director' => 1,
                ]);
            }

            if (count($empresa) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_empresa' => 1,
                ]);
            }
        } else {
            if (count($alumno) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_alumno' => 0,
                ]);
            }
            if (count($profesor) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_director' => 0,
                ]);
            }
            if (count($empresa) > 0) {
                Anexo::where('ruta_anexo', 'like', "$rutaParaBBDD")->update([
                    'firmado_empresa' => 0,
                ]);
            }
        }
    }

    #endregion
    /***********************************************************************/


    /***********************************************************************/
    #region Anexo II Y IV - Programa formativo
    /**
     * Esta funcion nos permite rellenar el Anexo II Y IV
     * @param Request $val->get(dni_tutor) es el dni del tutor
     * @return void
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public function rellenarAnexoIIYIV(Request $val)
    {

        //Controlador
        $controlador = new ControladorAlumno();

        //Request
        $dni_tutor = $val->get('dni_tutor');
        $nombreDocumentoDefecto = $val->get('anexo');

        //zip
        $fecha = Carbon::now();
        $zip = new ZipArchive;
        $AuxNombre = $dni_tutor . '_' . $fecha->day . '_' . Parametros::MESES[$fecha->month] . '_' . $fecha->year . $fecha->format('_h_i_s_A');
        $nombreZip = 'tmp' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . 'myzip_' . $AuxNombre . '.zip';


        //Resto variables relleno Anexo
        if (strcmp($nombreDocumentoDefecto, 'Anexo4.docx') == 0 || strcmp($nombreDocumentoDefecto, 'Anexo4.pdf') == 0) {
            $tipo_anexo = 'Anexo4';
        } else {
            $tipo_anexo = 'Anexo2';
        }
        $centro_estudios_tutor = Tutoria::select('cod_centro')->where('dni_profesor', '=', $dni_tutor)->get();
        $centro = CentroEstudios::select('nombre', 'cif')->where('cod', '=', $centro_estudios_tutor[0]->cod_centro)->get();
        $tutor = Profesor::select('nombre', 'apellidos')->where('dni', '=', $dni_tutor)->get();
        $ciclo_nombre = $this->getNombreCicloTutor($dni_tutor);
        $familia_profesional_descripcion = ControladorAlumno::getDescripcionFamiliaProfesional($ciclo_nombre[0]->nombre_ciclo);
        $alumnos_del_tutor = $this->getAlumnosQueVanAFct($dni_tutor);


        //Rutas
        Auxiliar::existeCarpeta(public_path($dni_tutor . DIRECTORY_SEPARATOR . $tipo_anexo));
        $rutaOriginal = $dni_tutor . DIRECTORY_SEPARATOR . $tipo_anexo . DIRECTORY_SEPARATOR . $nombreDocumentoDefecto;

        //Control de archivos
        $this->borrarAnexosIIYIV($dni_tutor, $alumnos_del_tutor, $tipo_anexo);

        foreach ($alumnos_del_tutor as $a) {
            $alumno = Alumno::select('nombre', 'apellidos')->where('dni', '=', $a->dni_alumno)->get();
            $empresa_nombre = $controlador->getNombreEmpresa($a->dni_alumno);
            $id_empresa = Empresa::select('id')->where('nombre', '=', $empresa_nombre->nombre)->get();
            $tutor_empresa = $this->getNombreYApellidoTutorEmpresa($a->dni_alumno);
            $fct = $controlador->getDatosFct($a->dni_alumno);
            $rutaDestino = $dni_tutor . DIRECTORY_SEPARATOR . $tipo_anexo . DIRECTORY_SEPARATOR . $tipo_anexo . '_' . $a->dni_alumno . '_' . $id_empresa[0]->id . '_' . $fecha->year . '_.docx';

            $auxPrefijos = ['centro', 'tutor', 'empresa', 'tutor_empresa', 'alumno', 'familia_profesional', 'ciclo'];
            $auxDatos = [$centro[0], $tutor[0], $empresa_nombre, $tutor_empresa, $alumno[0], $familia_profesional_descripcion[0], $ciclo_nombre[0]];
            $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);
            $datos = $datos +  [
                'dia' => $fecha->day,
                'mes' => Parametros::MESES[$fecha->month],
                'year' => $fecha->year,
                'fct.fecha_ini' => $fct->fecha_ini,
                'fct.fecha_fin' => $fct->fecha_fin,
                'fct.horas' => $fct->horas,
                'fct.departamento' => $fct->departamento
            ];

            //Esto nos permite modificar un archivo con datos nuevos
            Auxiliar::templateProcessorAndSetValues($rutaOriginal, $rutaDestino, $datos);

            //Base de datos
            $existeAnexo = Anexo::where('tipo_anexo', '=', $tipo_anexo)->where('ruta_anexo', 'like', "%$tipo_anexo%$a->dni_alumno%")->get();

            //if (count($existeAnexo) == 0) {
            Anexo::create(['tipo_anexo' => $tipo_anexo, 'ruta_anexo' => $rutaDestino]);
            // } else {
            //    Anexo::where('ruta_anexo', '=', "%$tipo_anexo%$a->dni_alumno%")->update(['ruta_anexo' => $rutaDestino . '.docx']);
            // }
        }
        $nombreZip = $this->montarZip($dni_tutor . DIRECTORY_SEPARATOR . $tipo_anexo, $zip, $nombreZip);
        unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . $tipo_anexo . DIRECTORY_SEPARATOR . $nombreDocumentoDefecto);
        return response()->download(public_path($nombreZip))->deleteFileAfterSend(true);
    }

    /**
     * Esta funcion ha sido creada con la finalidad de limpiar los anexos 2 y 4 relaccionados con ciertos alumnos, tanto de base de datos
     * como de sus carpetas, con el fin de evitar problemas futuros, como por ejemplo, que se cambie la asignación
     * de empresa-alumno y un alumno ya no pertenezca a una empresa, sino a  otra. Por lo tanto se replicaria su
     * anexo para dos empresas distintas lo cual no sería correcto.
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function borrarAnexosIIYIV($dni_tutor, $alumnos_del_tutor, $tipo_anexo)
    {
        //Base de datos
        foreach ($alumnos_del_tutor as $a) {
            $AnexosTutor = Anexo::select('ruta_anexo')->where('habilitado', '=', 1)->where('ruta_anexo', 'like', "$dni_tutor%$a->dni_alumno%")->where('tipo_anexo', '=', $tipo_anexo)->get();
            Anexo::where('tipo_anexo', '=', $tipo_anexo)->where('habilitado', '=', 1)->where('ruta_anexo', 'like', "$dni_tutor%$a->dni_alumno%")->delete();

            foreach ($AnexosTutor as $anexo) {
                if (file_exists(public_path($anexo->ruta_anexo))) {
                    unlink(public_path($anexo->ruta_anexo));
                }
            }
        }


        //Carpetas
    }


    /***********************************************************************/
    #region Funciones auxiliares para el Anexo II y IV

    /**
     * Esta funcion nos permite obtener el nombre del ciclo al que pertenece el tutor
     * @param [type] $dni_alumno, es el dni del alumno
     * @return void $nombre_ciclo, devuelveel nombre del ciclo
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function getNombreCicloTutor($dni_tutor)
    {

        $nombre_ciclo = Tutoria::join('grupo', 'grupo.cod', '=', 'tutoria.cod_grupo')
            ->select('grupo.nombre_ciclo')
            ->where('tutoria.dni_profesor', '=', $dni_tutor)->get();

        return $nombre_ciclo;
    }

    /**
     * Esta funcion nos permite obtener el nombre y el apellido del tutor de la empresa
     * @param [type] $dni_tutor, es el dni del tutor
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function getNombreYApellidoTutorEmpresa($dni_alumno)
    {

        $tutor_empresa = Trabajador::join('fct', 'trabajador.dni', '=', 'fct.dni_tutor_empresa')
            ->select('trabajador.nombre', 'trabajador.apellidos')
            ->where('fct.dni_alumno', '=', $dni_alumno)
            ->first();

        return $tutor_empresa;
    }

    /**
     * Esta funcion nos permite obtener el nombre del los alumnos tutorizados por un tutor
     *que van a FCT
     * @param [type] $dni_alumno, es el dni del alumno
     * @return $nombre_ciclo, devuelveel nombre del ciclo
     * @author LauraM <lauramorenoramos97@gmail.com>
     */
    public static function getAlumnosQueVanAFct($dni_tutor)
    {
        $alumnosVanFct =  Tutoria::join('matricula', 'tutoria.cod_centro', '=', 'matricula.cod_centro')
            ->join('fct', 'matricula.dni_alumno', '=', 'fct.dni_alumno')
            ->where('tutoria.dni_profesor', '=', $dni_tutor)
            ->select('matricula.dni_alumno')
            ->get();

        return $alumnosVanFct;
    }

    #endregion
    /***********************************************************************/
    #endregion
    /***********************************************************************/
}
