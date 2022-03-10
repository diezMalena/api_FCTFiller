<?php

namespace Tests\Feature;

use App\Models\CentroEstudios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Profesor;
use Illuminate\Support\Facades\Hash;

class CruProfesoresTest extends TestCase
{
    /**
     * @author Laura <lauramorenoramos97@gmail.com>
     * Comprueba que el servicio /eliminarProfesor/{dni_profesor} borra un Profesor correctamente
     */
    public function test_borrar()
    {
        $faker = \Faker\Factory::create('es_ES');

        $codCentroEstudios = CentroEstudios::select('cod')->inRandomOrder()->first();

        $profesor = Profesor::create([
            'dni' => $faker->unique->dni,
            'email' => $faker->email,
            'password' => Hash::make('superman'),
            'nombre' => $faker->firstName,
            'apellidos' => $faker->lastName . ' ' . $faker->lastName,
            'cod_centro_estudios' => $codCentroEstudios->cod
        ]);

        $this->json('DELETE', 'api/eliminarProfesor/' . $profesor->dni, [])
            ->assertStatus(201);
    }


    /**
     *  @author Laura <lauramorenoramos97@gmail.com>
     * Comprueba que el servicio /modificarProfesor' edita un profesor correctamente.
     */
    public function test_modificar()
    {
        $faker = \Faker\Factory::create('es_ES');
        $roles[]=4;
        $codCentroEstudios = CentroEstudios::select('cod')->inRandomOrder()->first();

        $profesor = Profesor::create([
            'dni' => $faker->unique->dni,
            'email' => $faker->email,
            'password' => Hash::make('superman'),
            'nombre' => $faker->firstName,
            'apellidos' => $faker->lastName . ' ' . $faker->lastName,
            'cod_centro_estudios' => $codCentroEstudios->cod
        ]);


        $profesorModificado = [
            "dni" => $faker->unique->dni,
            "email" => $faker->email,
            "nombre" => $faker->firstName,
            "apellidos" => $faker->lastName . ' ' . $faker->lastName,
            "password1" =>'superman',
            "password2" => 'superman',
            "roles" => $roles,
            "personaAux" => $profesor->dni
        ];



        $this->json('POST', 'api/modificarProfesor' , $profesorModificado)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Perfil Modificado'
            ], 201);
    }

    /**
     *  @author Laura <lauramorenoramos97@gmail.com>
     *Comprueba que el servicio /api/addProfesor' funciona correctamente.
     */
    public function test_registrar()
    {
        $faker = \Faker\Factory::create('es_ES');
        $profesor=Profesor::inRandomOrder()->first();
        $roles= array();
        $roles[]=4;


        $profesorNuevo = ([
            "dni" => $faker->unique->dni,
            "email" => $faker->email,
            "nombre" => $faker->firstName,
            "apellidos" => $faker->lastName . ' ' . $faker->lastName,
            "password1" => 'superman',
            "password2" => 'superman',
            "roles" => $roles,
            "personaAux" => $profesor->dni
        ]);


        $response = $this->json('POST', '/api/addProfesor',$profesorNuevo );
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Profesor Creado con exito'
            ], 201);
            $this->assertEquals($response->getStatusCode(), 201);
    }
}
