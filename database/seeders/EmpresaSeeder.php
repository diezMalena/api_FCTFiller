<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use Database\Factories\RolTrabajadorAsignadoFactory;
use Database\Factories\TrabajadorFactory;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author @DaniJCoello
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            //Creo las empresas
            $empresa = Empresa::factory()->create();
            //Saco la id de la empresa
            $id = $empresa->id;
            //Lo establezco en la factoría del trabajador para que los trabajadores se asocien a la empresa
            TrabajadorFactory::$IDEMPRESA = $id;
            //Creo unos cuantos trabajadores
            for ($j = 0; $j < 15; $j++) {
                //Creo el trabajador
                $trabajador = Trabajador::factory()->create();
                //Extraigo su clave primaria y la establezco en la factoría de los roles del trabajador
                $dni = $trabajador->dni;
                RolTrabajadorAsignadoFactory::$DNI = $dni;
                //Y ahora creo un representante legal, dos responsables de centro y, el resto, tutores
                if ($j == 0) {
                    //Establezco el rol a 1 (representante legal)
                    RolTrabajadorAsignadoFactory::$ROL = 1;
                } else if ($j < 3) {
                    //Establezco el rol a 2 (responsable de centro)
                    RolTrabajadorAsignadoFactory::$ROL = 2;
                } else {
                    //Establezco el rol a 3 (tutor)
                    RolTrabajadorAsignadoFactory::$ROL = 3;
                }
                //Creo el registro en la tabla de roles asignados
                RolTrabajadorAsignado::factory()->create();
            }
        }
    }
}
