<?php

namespace Tests\Feature;

use App\Auxiliar\Auxiliar;
use App\Models\RolEmpresa;
use App\Models\RolProfesor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuxiliarTest extends TestCase
{

    /**
     * Ejecuta un test sobre la función auxiliar modelToArray
     * @return void
     * @see Auxiliar::modelToArray(Model $modelo, string $prefijoClave)
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function test_modelToArray()
    {
        $model = RolProfesor::find(1);
        $array = Auxiliar::modelToArray($model, 'rol');
        $this->assertEquals($model->descripcion, $array['rol.descripcion']);
    }

    /**
     * Ejecuta un test sobre la función auxiliar modelsToArray
     * @return void
     * @see Auxiliar::modelsToArray(array $modelos, array $prefijos)
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function test_modelsToArray()
    {
        $rol_profesor = RolProfesor::find(1);
        $rol_empresa = RolEmpresa::find(1);
        $array = Auxiliar::modelsToArray([$rol_profesor, $rol_empresa], ['rol_profesor', 'rol_empresa']);
        $this->assertEquals($rol_profesor->id, $array['rol_profesor.id']);
        $this->assertEquals($rol_empresa->descripcion, $array['rol_empresa.descripcion']);
    }
}
