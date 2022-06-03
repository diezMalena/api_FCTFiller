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
use Faker\Core\Number;
use Illuminate\Database\QueryException;
use Mockery\Undefined;
use PhpParser\Node\Expr\Cast\Array_;
use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Facades\Hash;

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
                ->select('trabajador.nombre')
                ->first();
            //Por si acaso la empresa no tiene un responsable asignado, ponemos al representante legal
            if (!$responsable) {
                $responsable = RolTrabajadorAsignado::join('trabajador', 'trabajador.dni', '=', 'rol_trabajador_asignado.dni')
                    ->join('empresa', 'empresa.id', '=', 'trabajador.id_empresa')
                    ->where([['rol_trabajador_asignado.id_rol', Parametros::REPRESENTANTE_LEGAL], ['empresa.id', $empresa->id]])
                    ->select('trabajador.nombre')
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
                    #endregion

                    #region Construcción de la tabla
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
                    #endregion

                    #region Relleno de datos en Word
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

    #endregion
    /***********************************************************************/

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD de anexos

    /**
     * Esta funcion devuelve los anexos de un tutor, sacando lo que va a mostrar de la tabla Anexos
     *
     * @param Request $val
     * @return void
     */
    public function verAnexos($dni_tutor)
    {
        $datos = array();
        $habilitado = 1;

        #region ANEXO 0 - 0A
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
        #endregion
        #region Anexo I
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
            #endregion
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
     * @author Laura <lauramorenoramos97@gmail.com>
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
    }

    /**
     * Esta funcion te permite eliminar un fichero de una carpeta
     * @author Laura <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function eliminarAnexo($dni_tutor, $cod_anexo)
    {
        $codAux = explode("_", $cod_anexo);
        if ($codAux[0] == 'Anexo1') {
            //Eliminar un fichero
            unlink(public_path() . DIRECTORY_SEPARATOR . $dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1' . DIRECTORY_SEPARATOR . $cod_anexo);
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
     * @author Laura <lauramorenoramos97@gmail.com>
     * Esta funcion sirve para deshabilitar un anexo y borrar su ruta de la tabla correspondiente
     */
    public function deshabilitarAnexo(Request $val)
    {

        $cod_anexo = $val->get('cod_anexo');

        Anexo::where('ruta_anexo', 'like', "%$cod_anexo")->update([
            'habilitado' => 0,
        ]);

        $codAux = explode("_", $cod_anexo);
        if ($codAux[0] == 'Anexo1') {
            FCT::where('ruta_anexo', 'like', "%$cod_anexo")->update([
                'ruta_anexo' => '',
            ]);
        } else {
            if ($codAux[0] == 'Anexo0' || $codAux[0] == 'Anexo0A') {
                Convenio::where('ruta_anexo', 'like', "%$cod_anexo")->update([
                    'ruta_anexo' => '',
                ]);
            }
        }
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

        $nombreZip = $this->montarZipCrud($dni, $zip, $nombreZip, $habilitado);

        return response()->download(public_path($nombreZip));
    }

    /**
     * Esta funcion sirve para generar el zip de todos los anexos del crud de anexos
     * Miramos los anexos de la carpeta de anexos del tutor, buscamos ese anexo habilitado o no habilitado, segun si
     * la consulta se hace desde el crud de anexos o desde el historial  y comprobamos
     * si este existe en el directorio, en tal caso se añade al zip
     * @author Laura <lauramorenoramos97@gmail.com>
     * @param String $dni_tutor, el dni del tutor, sirve para ubicar su directorio
     * @param ZipArchive $zip , el zip donde se almacenaran los archivos
     * @param String $nombreZip, el nombre que tendrá el zip
     * @return void
     */
    public function montarZipCrud(String $dni_tutor, ZipArchive $zip, String $nombreZip, $habilitado)
    {
        $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo1'));
        if ($zip->open(public_path($nombreZip), ZipArchive::CREATE)) {
            #region Anexo I
            foreach ($files as $value) {
                //El nombreAux es el nombre del anexo completo
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo1')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();


                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
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
            $files = File::files(public_path($dni_tutor . DIRECTORY_SEPARATOR . 'Anexo0A'));
            foreach ($files as $value) {
                $nombreAux = basename($value);
                $existeAnexo = Anexo::where('tipo_anexo', '=', 'Anexo0A')->where('habilitado', '=', $habilitado)->where('ruta_anexo', 'like', "%$nombreAux%")->get();

                if (count($existeAnexo) > 0) {
                    $zip->addFile($value, $nombreAux);
                }
            }
            #endregion
            $zip->close();
        }
        return $nombreZip;
    }


    /**
     * Esta funcion devuelve el historial de anexos de un tutor, sacando lo que va a mostrar de la tabla Anexos
     *
     * @param Request $val
     * @return void
     */
    public function verHistorialAnexos($dni_tutor)
    {
        $datos = array();
        $habilitado = 0;

        #region Anexo 0 - 0A
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
        #endregion
        #region Anexo I
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
        #endregion
        return response()->json($datos, 200);
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
            // $convenio = $this->addConvenio($req->dni, $empresa->id, $empresa->es_privada);
            // $rutaAnexo = $this->generarAnexo0($convenio->cod_convenio, $req->dni);
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
     * // Añadido control de errores
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
            $convenio = Convenio::create($req->convenio);
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
            Convenio::where('cod_convenio', $convenio->cod_convenio)->update(['ruta_anexo' => $ruta]);
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
     */
    public function verGrupos($dni)
    {
        $centroEstudios = Profesor::select('cod_centro_estudios')->where('dni', '=', $dni)->get();
        $grupos = Tutoria::select('cod_grupo', 'dni_profesor')->where('cod_centro', '=', $centroEstudios[0]->cod_centro_estudios)->get();
        return response()->json($grupos, 200);
    }

    public function getCentroEstudiosFromConvenioJSON(string $codConvenio)
    {
        return response()->json($this->getCentroEstudiosFromConvenio($codConvenio), 200);
    }

    #endregion
    /***********************************************************************/
}
