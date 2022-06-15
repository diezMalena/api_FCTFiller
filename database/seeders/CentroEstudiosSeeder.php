<?php

namespace Database\Seeders;

use App\Auxiliar\Auxiliar;
use App\Models\CentroEstudios;
use App\Models\Profesor;
use App\Models\RolProfesorAsignado;
use App\Models\User;
use Database\Factories\ProfesorFactory;
use Database\Factories\RolProfesorAsignadoFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            if ($i == 0) {
                $centro = CentroEstudios::create([
                    'cod' => '1111VDG',
                    'cif' => '1VDG',
                    'cod_centro_convenio' => 'VdG',
                    'nombre' => 'CIFP Virgen de Gracia',
                    'localidad' => 'Puertollano',
                    'provincia' => 'Ciudad Real',
                    'direccion' => 'Paseo de San Gregorio, 82-84',
                    'cp' => '13500',
                    'telefono' => '926426250',
                    'email' => 'secretaria@cifpvirgendegracia.com'
                ]);
            } else {
                $centro = CentroEstudios::factory()->create();
            }
            //Saco el código del centro
            $cod = $centro->cod;
            //Lo establezco en la factoría del profesor para que los profesores se asocien al centro
            ProfesorFactory::$CODCENTRO = $cod;
            //Creo unos cuantos profesores
            for ($j = 0; $j < 10; $j++) {
                //Creo el profesor
                if ($i == 0 && $j == 0) {
                    $profe = Profesor::create([
                        'dni' => '1A',
                        'email' => 'anabelen@cifpvirgendegracia.es',
                        'password' => Hash::make('superman'),
                        'nombre' => 'Ana Belén',
                        'apellidos' => 'Santos Cabaña',
                        'cod_centro_estudios' => $centro->cod,
                    ]);
                    User::create([
                        'name' => 'Ana Belén Santos Cabaña',
                        'email' => 'anabelen@cifpvirgendegracia.es',
                        'password' => Hash::make('superman'),
                        'tipo' => 'profesor'
                    ]);
                    // Auxiliar::addUser($profe, "profesor");
                } else {
                    $profe = Profesor::factory()->create();
                }
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
