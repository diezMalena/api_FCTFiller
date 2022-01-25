<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use App\Models\CentroCiclo;
use App\Models\CentroEstudios;
use App\Models\Profesor;
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
    public function generarAnexo0(string $dniTutor, string $cifEmpresa) {
        //Primero consigo los datos del centro de estudios asociado al tutor
        $centroEstudios = $this->getCentroEstudios($dniTutor);

        $nombreArchivo = 'anexo0';
        $rutaArchivo = 'anexos/plantillas/' . $nombreArchivo . '.docx';
        $template = new TemplateProcessor($rutaArchivo);


    }

    public function getCentroEstudios(string $dniTutor) {
        $codCentro = Profesor::find($dniTutor)->cod_centro_estudios;
        dd(CentroEstudios::find($codCentro));
        return CentroEstudios::find($codCentro);
    }

}
