<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Auxiliar;
use App\Auxiliar\Parametros as AuxiliarParametros;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use App\Models\Curso;
use App\Models\EmpresaCurso;
use App\Models\Fct;
use App\Auxiliar\Parametros;
use App\Models\AuxConvenio;
use App\Models\AuxCursoAcademico;
use App\Models\CentroEstudios;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\EmpresaGrupo;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use Carbon\Carbon;
use App\Models\Grupo;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Tutoria;

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
        $hoy = date("Y-m-d H:i:s");
        $cursoAcademico = AuxCursoAcademico::where([['fecha_inicio', '<', $hoy], ['fecha_fin', '>', $hoy]])
            ->get()->first();
        if ($cursoAcademico) {
            $cursoAcademico = $cursoAcademico->cod_curso;
        } else {
            $cursoAcademico = AuxCursoAcademico::where('id', AuxCursoAcademico::max('id'))->get()->first()->cod_curso;
        }
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
            ->select(['empresa.id', 'empresa.nombre'])
            ->get();

        foreach ($empresas as  $empresa) {
            $alumnos = Grupo::join('matricula', 'matricula.cod_grupo', '=', 'grupo.cod')
                ->join('alumno', 'alumno.dni', '=', 'matricula.dni_alumno')
                ->join('fct', 'fct.dni_alumno', '=', 'alumno.dni')
                ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
                ->where([['tutoria.dni_profesor', $dni], ['fct.id_empresa', $empresa->id]])
                ->select(['alumno.nombre', 'alumno.dni', 'alumno.va_a_fct'])
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
     *  @param $empresas son las empresas
     */
    public function actualizarEmpresaAsignadaAlumno(Request $request)
    {
        return response()->json($request, 200);
    }

    /**
     * Esta funcion nos permite rellenar el Anexo 1
     *@author LauraM <lauramorenoramos97@gmail.com>
     * @param Request $val
     * @return void
     */
    public function rellenarAnexo1(Request $val)
    {

        $dni_tutor = $val->get('dni_tutor');
        $grupo = Tutoria::select('cod_grupo')->where('dni_profesor', $dni_tutor)->get();
        $empresas_id = EmpresaGrupo::select('id_empresa')->where('cod_grupo', $grupo[0]->cod_grupo)->get();
        //Recorrido id empresas
        foreach ($empresas_id as $id) {
            try {
                $rutaOriginal = 'anexos/plantillas/Anexo1';
                $rutaDestino = 'anexos/rellenos/anexo1/Anexo1' . $id->id;
                $template = new TemplateProcessor($rutaOriginal . '.docx');

                //Fecha //CHECK
                $fecha = Carbon::now();
                //Codigo del centro //CHECK
                $cod_centro = Profesor::select('cod_centro_estudios')->where('dni', $dni_tutor)->get();
                //Numero de Convenio //CHECK
                $num_convenio = Convenio::select('cod_convenio')->where('id_empresa', '=', $id->id_empresa)->where('cod_centro', '=', $cod_centro[0]->cod_centro_estudios)->get();
                //Nombre del centro //CHECK
                $nombre_centro = CentroEstudios::select('nombre')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();
                //Nombre de la empresa //CHECK
                $nombre_empresa = Empresa::select('nombre')->where('id', $id->id_empresa)->get();
                //Cif empresa //CHECK
                $cif_empresa = Empresa::select('cif')->where('id', $id->id_empresa)->get();
                //Direccion del centro //CHECK
                $dir_centro = Empresa::select('direccion')->where('id', $id->id_empresa)->get();
                //Nombre del ciclo //CHECK
                $nombre_ciclo = Grupo::select('nombre_ciclo')->where('cod', $grupo[0]->cod_grupo)->get();
                //Año del curso //CHECK
                $curso_anio = Convenio::select('curso_academico_inicio')->where('cod_convenio', $num_convenio[0]->cod_convenio)->get();
                //Nombre del tutor  //CHECK
                $nombre_tutor = Profesor::select('nombre')->where('dni', $dni_tutor)->get();
                //Responsable de la empresa //CHECK
                $responsable_empresa = Empresa::join('trabajador', 'trabajador.id_empresa', '=', 'empresa.id')
                    ->join('rol_trabajador_asignado', 'rol_trabajador_asignado.dni', '=', 'trabajador.dni')
                    ->select('trabajador.nombre')
                    ->where('trabajador.id_empresa', '=', $id->id_empresa)
                    ->where('rol_trabajador_asignado.id_rol', '=', Parametros::REPRESENTANTE_LEGAL)
                    ->get();

                //Ciudad del centro de estudios //CHECK
                $ciudad_centro_estudios = CentroEstudios::select('localidad')->where('cod', $cod_centro[0]->cod_centro_estudios)->get();

                //Alumnos //CHECK
                $alumnos = Fct::join('alumno', 'alumno.dni', '=', 'fct.dni_alumno')
                    ->select('alumno.nombre', 'alumno.apellidos', 'alumno.dni', 'alumno.localidad', 'fct.horario', 'fct.num_horas', 'fct.fecha_ini', 'fct.fecha_fin')
                    ->where('id_empresa', '=', $id->id_empresa)
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
                    $table->addCell(1500)->addText($a->apellidos . ' ' . $a->nombre);
                    $table->addCell(1500)->addText($a->dni);
                    $table->addCell(1500)->addText($a->localidad);
                    $table->addCell(1500)->addText($a->horario);
                    $table->addCell(1500)->addText($a->num_horas);
                    $table->addCell(1500)->addText($a->fecha_ini);
                    $table->addCell(1500)->addText($a->fecha_fin);
                }


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
                    'ciclo_nombre' => $nombre_ciclo[0]->nombre,
                    'responsable_empresa' => $responsable_empresa[0]->nombre,
                ];


                $template->setValues($datos);
                $template->setComplexBlock('{table}', $table);
                $template->saveAs($rutaDestino . '.docx');
                // $this->convertirWordPDF($rutaDestino);


                //$file = public_path(). $rutaDestino.".docx";
                //$headers = ['Content-Type: application/vnd.ms-word.document.macroEnabled.12'];
                //return \Response::download($file, 'plugin.jpg', $headers);

                //return response()->download(public_path($rutaDestino . '.docx'));
            } catch (Exception $e) {
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
     * Genera el Anexo 0, convenio entre una empresa y un centro
     * @param string $dniTutor el DNI del tutor que está loggeado en el sistema
     * @param string $cifEmpresa el CIF de la empresa con la que se hará el convenio
     *
     * @author @DaniJCoello
     */
    public function generarAnexo0(string $codConvenio)
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
        $nombrePlantilla = 'anexo0';
        // $nombreTemporal = $nombrePlantilla . '-' . $codConvenioAux . '-tmp';
        $rutaOrigen = 'anexos/plantillas/' . $nombrePlantilla . '.docx';
        // $rutaTemporal = 'tmp/anexos/' . $nombreTemporal . '.docx';
        $rutaDestino = 'anexos/rellenos/anexo0/' . $nombrePlantilla . '-' . $codConvenioAux . '.docx'/*.pdf*/;

        //Creo la plantilla y la relleno
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);

        //Y guardo la ruta en la base de datos
        // $convenio = EmpresaCentroEstudios::find($codConvenio);
        // $convenio->ruta_anexo = $rutaDestino;
        // $convenio->save();

        //Convierto el documento a PDF
        //Pendiente de revisar: no convierte las cabeceras. Se queda en Word de momento
        // $this->convertirWordPDF0($rutaTemporal, $rutaDestino);
    }

    /**
     * Esta función convierte un archivo word en pdf
     * @param string $rutaArchivo la ruta del archivo .docx
     * @param string $rutaDestino la ruta de destino del .pdf
     * @return void
     *
     * @author @DaniJCoello
     */
    private function convertirWordPDF0(string $rutaArchivo, string $rutaDestino)
    {
        /* Set the PDF Engine Renderer Path */
        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        // Load temporarily create word file
        $Content = \PhpOffice\PhpWord\IOFactory::load($rutaArchivo);

        //Save it into PDF
        $savePdfPath = public_path($rutaDestino);

        /*@ If already PDF exists then delete it */
        if (file_exists($savePdfPath)) {
            unlink($savePdfPath);
        }

        //Save it into PDF
        $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content, 'PDF');
        $PDFWriter->save($savePdfPath);

        /*@ Remove temporarily created word file */
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
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
     * Actualiza la información de una empresa y su representante legal en la base de datos
     *
     * @param Request $req contiene los datos de la empresa
     * @return response JSON con la respuesta del servidor: 200 -> todo OK, 400 -> error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function updateEmpresa(Request $req) {
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
        return response()->json(['message' => 'Empresa actualizada'], 200);
    }

    public function updateRepresentante(Request $req) {
        Trabajador::where('dni', $req->dni)->update([
            'nombre' => $req->nombre,
            'apellidos' => $req->apellidos,
            'email' => $req->email
        ]);
        return response()->json(['message' => 'Representante actualizado'], 200);
    }

    /**
     * Elimina una empresa de la base de datos
     * @param int $idEmpresa el ID de la empresa a eliminar
     * @return response JSON con la respuesta del servidor: 200 -> OK, 400 -> error
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function deleteEmpresa(int $idEmpresa) {
        $nombreEmpresa = Empresa::find($idEmpresa)->nombre;
        Empresa::destroy($idEmpresa);
        return response()->json(['message' => 'Empresa eliminada: ' . $nombreEmpresa], 200);
    }

    /**
     * Recoge los datos que se envía desde el cliente, y añade estos a sus correspondientes tablas.
     * También, se generará el Anexo0 al añadir las empresas.
     * @author @Malena
     */
    public function addDatosEmpresa(Request $req)
    {
        //try{
        $empresa = Empresa::create($req->empresa);
        $repre_aux = $req->representante;
        $repre_aux["id_empresa"] = $empresa->id;
        $representante = Trabajador::create($repre_aux);
        RolTrabajadorAsignado::create([
            'dni' => $representante->dni,
            'id_rol' => 1,
        ]);
        $convenio = $this->addConvenio($req->dni, $empresa->id);
        $this->generarAnexo0($convenio->cod_convenio);
        return response()->json(['message' => 'Registro correcto'], 200);
        /*}catch(Exception $ex){
            return response()->json(['message'=>'Registro fallido'],400);
        }*/



        //----------------------------------COMPROBACIONES FUTURAS---------------------------------------
        //Si la empresa no está registrada:
        /*if(!isset($empresa)){
            $empresa = Empresa::create($req->empresa);
            return response()->json(['message'=>'Empresa insertada: '.$empresa],201);
        }else{
            return response()->json(['message'=>'La empresa no se ha insertado: '.$empresa],400);
        }

        $representante = Trabajador::find($req->representante->dni);
        //Si el representante no está registrado:
        if(!isset($representante)){
            $representante = Trabajador::create($req->representante);

            return response()->json(['message'=>'Representante insertado: '.$representante],201);
        }else{
            return response()->json(['message'=>'El representante no se ha insertado: '.$representante],400);
        }*/
    }

    /**
     * Registrar el convenio en la BBDD con los diferentes datos que necesitamos.
     * @author Malena
     * @param string $dniTutor, el dni del tutor que se encuentra logueado.
     * @param int $id_empresa, el id de la empresa que se registra.
     * @return Convenio convenio entre la empresa y el centro de estudios.
     */
    public function addConvenio(string $dniTutor, int $id_empresa)
    {
        //Consigo el centro de estudios a partir del Dni del tutor:
        $centroEstudios = $this->getCentroEstudiosFromProfesor($dniTutor);
        //Fabrico el codigo del convenio:
        $codConvenio = $this->generarCodigoConvenio($centroEstudios->cod_centro_convenio, 'C');
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
}
