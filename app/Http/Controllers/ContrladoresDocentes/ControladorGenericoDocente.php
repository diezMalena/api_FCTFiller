<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Profesor;
use Illuminate\Http\Request;

class ControladorGenericoDocente extends Controller
{
    //

    /**
     * Devuelve las empresas asociadas a un profesor mediante los convenios con su centro de estudios
     *
     * @param $dniProfesor el DNI del profesor
     * @return response json con la colección de empresas asociadas
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
}
