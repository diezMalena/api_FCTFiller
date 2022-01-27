<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Auxiliar;
use App\Auxiliar\Parametros as AuxiliarParametros;
use App\Http\Controllers\Controller;
use App\Models\AuxConvenio;
use App\Models\CentroEstudios;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class ControladorTutorFCT extends Controller
{

    /**
     * Genera el Anexo 0, convenio entre una empresa y un centro
     * @param string $dniTutor el DNI del tutor que está loggeado en el sistema
     * @param string $cifEmpresa el CIF de la empresa con la que se hará el convenio
     *
     * @author @DaniJCoello
     */
    public function generarAnexo0(string $dniTutor, string $cifEmpresa)
    {
        //Primero consigo los datos del centro de estudios asociado al tutor y su director
        $centroEstudios = $this->getCentroEstudios($dniTutor)->makeHidden('created_at', 'updated_at');
        $director = $this->getDirectorCentroEstudios($centroEstudios->cod_centro)->makeHidden('created_at', 'updated_at', 'password');

        //Ahora hago lo propio con la empresa en cuestión
        $empresa = $this->getEmpresaFromCIF($cifEmpresa)->makeHidden('created_at', 'updated_at');
        $responsableLegal = $this->getResponsableLegal($empresa->id)->makeHidden('created_at', 'updated_at', 'password');

        //Construyo el array con todos los datos
        $auxPrefijos = ['director', 'centro', 'representante', 'empresa'];
        $auxDatos = [$director, $centroEstudios, $responsableLegal, $empresa];
        $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

        //Ahora extraigo los datos de fecha
        $fecha = Carbon::now();
        $datos['dia'] = $fecha->day;
        $datos['mes'] = AuxiliarParametros::MESES[$fecha->month];
        $datos['anio'] = $fecha->year % 100;

        //Esta parte no se hará así, sino que se tomarán los datos de la tabla de convenios directamente o se recibirán por parámetro
        //El registro del convenio se hará cuando el tutor registre los datos de la empresa
        $codConvenio = $this->generarCodigoConvenio($centroEstudios->cod_centro_convenio, 'C');
        $datos['cod_convenio'] = $codConvenio;
        //Esta variable se usa sólo para el nombre del archivo
        $codConvenioAux = str_replace('/', '-', $codConvenio);

        //Ahora genero el Word y el PDF en sí
        //Establezco las variables que necesito
        $nombrePlantilla = 'anexo0';
        $nombreTemporal = $nombrePlantilla . '-' . $codConvenioAux . '-tmp';
        $rutaOrigen = 'anexos/plantillas/' . $nombrePlantilla . '.docx';
        $rutaTemporal = 'tmp/anexos/' . $nombreTemporal . '.docx';
        $rutaDestino = 'anexos/rellenos/anexo0/' . $nombrePlantilla . '-' . $codConvenioAux . '.pdf';

        //Creo la plantilla y la relleno
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaTemporal);

        //Convierto el documento a PDF
        $this->convertirWordPDF($rutaTemporal, $rutaDestino);
    }

    /**
     * Esta función convierte un archivo word en pdf
     */
    private function convertirWordPDF(string $rutaArchivo, string $rutaDestino)
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
    public function getCentroEstudios(string $dniProfesor)
    {
        return CentroEstudios::find(Profesor::find($dniProfesor)->cod_centro_estudios);
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
    public function getEmpresaFromID($id)
    {
        return Empresa::find($id);
    }

    /**
     * Devuelve el representante legal de una empresa
     * @param int $id la ID autonumérica de la empresa en la base de datos de la aplicación
     * @return Empresa una colección con la información de la empresa
     *
     * @author @DaniJCoello
     */
    public function getResponsableLegal($id)
    {
        return Trabajador::whereIn('dni', RolTrabajadorAsignado::where('id_rol', 1)->get('dni'))->where('id_empresa', $id)->first();
    }

    /**
     * Recoge los datos que se envía desde el cliente, y añade estos a sus correspondientes tablas.
     * También, se generará el Anexo0 al añadir las empresas.
     * @author @Malena
     */
    public function addDatosEmpresa(Request $req){
        try{
            $empresa = Empresa::create($req->empresa);
            $repre_aux = $req->representante;
            $repre_aux["id_empresa"] = $empresa->id;
            $representante = Trabajador::create($repre_aux);
            RolTrabajadorAsignado::create([
                'dni' => $representante->dni,
                'id_rol' => 1,
            ]);
            return response()->json(['message'=>'Registro correcto'],200);
        }catch(Exception $ex){
            return response()->json(['message'=>'Registro fallido'],400);
        }



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
}
