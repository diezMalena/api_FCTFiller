<?php

namespace Tests\Feature;

use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\Trabajador;
use Database\Factories\EmpresaFactory;
use Database\Factories\TrabajadorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GestionEmpresasTest extends TestCase
{
    /**
     * Hace una prueba sobre la ruta update_empresa
     * @return void
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function test_updateEmpresa() {
        $empresa = Empresa::factory()->create();
        $payload = [
            'id' => $empresa->id,
            'cif' => $empresa->cif,
            'nombre' => 'nombre',
            'email' => 'email',
            'telefono' => $empresa->telefono,
            'localidad' => $empresa->localidad,
            "provincia" => $empresa->provincia,
            "direccion" => $empresa->direccion,
            "cp" => $empresa->cp,
        ];
        $this->json('PUT', '/api/update_empresa', $payload)->assertStatus(200);
        $this->assertNotEquals($empresa->nombre, Empresa::find($empresa->id)->nombre);
        Empresa::destroy($empresa->id);
    }

    /**
     * Hace una prueba sobre la ruta delete_empresa
     * @return void
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function test_deleteEmpresa() {
        $empresa = Empresa::factory()->create();
        $route = '/api/delete_empresa/id=' . $empresa->id;
        $this->json('DELETE', $route, [])->assertStatus(200);
        $this->assertNull(Empresa::find($empresa->id));
    }

    /**
     * Comprueba que se eliminan los trabajadores de una empresa cuando se elimina la empresa
     * @return void
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     */
    public function test_deleteTrabajadoresEmpresa() {
        $empresa = Empresa::factory()->create();
        TrabajadorFactory::$IDEMPRESA = $empresa->id;
        for ($i=0; $i < 5; $i++)
            Trabajador::factory()->create();
        $route = '/api/delete_empresa/id=' . $empresa->id;
        $this->json('DELETE', $route, []);
        $this->assertEmpty(Trabajador::where('id_empresa', $empresa->id)->get());
    }

}
