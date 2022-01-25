<?php

namespace Database\Seeders;

use App\Models\CentroEstudios;
use App\Models\Profesor;
use App\Models\RolProfesorAsignado;
use Database\Factories\ProfesorFactory;
use Database\Factories\RolProfesorAsignadoFactory;
use Illuminate\Database\Seeder;

class CentroEstudiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author @DaniJCoello
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            //Creo los centros
            $centro = CentroEstudios::factory()->create();
            //Saco el código del centro
            $cod = $centro->cod_centro;
            //Lo establezco en la factoría del profesor para que los profesores se asocien al centro
            ProfesorFactory::$CODCENTRO = $cod;
            //Creo unos cuantos profesores
            for ($j = 0; $j < rand(15,25); $j++) {
                //Creo el profesor
                $profe = Profesor::factory()->create();
                //Extraigo su clave primaria y la establezco en la factoría de los roles del profe
                $dni = $profe->dni;
                RolProfesorAsignadoFactory::$DNI = $dni;
                //Y ahora creo un director, dos jefes de estudios y, el resto, tutores
                if ($j == 0) {
                    //Establezco el rol a 1 (director)
                    RolProfesorAsignadoFactory::$ROL = 1;
                } else if ($j < 3) {
                    //Establezco el rol a 2 (jefatura)
                    RolProfesorAsignadoFactory::$ROL = 2;
                } else {
                    //Establezco el rol a 3 (tutor)
                    RolProfesorAsignadoFactory::$ROL = 3;
                }
                //Creo el registro en la tabla de roles asignados
                RolProfesorAsignado::factory()->create();
            }
        }
    }
}
