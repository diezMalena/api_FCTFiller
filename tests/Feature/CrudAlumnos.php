<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciudad;
use App\Models\Profesor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrudAlumnos extends TestCase
{

    /**
     * Comprueba que el servicio '/api/jefatura/listarAlumnos' devuelve un listado de alumnos.
     *  @author David Sánchez Barragán
     */
    public function test_listar_alumnos()
    {
        $DNIProfesor = Profesor::join('rol_profesor_asignado', 'rol_profesor_asignado.dni', '=', 'profesor.dni')
            ->where('rol_profesor_asignado.id_rol', '=', '1')
            ->select('profesor.dni')->inRandomOrder()->first();




        $response = $this->json('GET', '/api/jefatura/listarAlumnos/' . $DNIProfesor)
            ->assertStatus(200);

        $this->assertIsObject($response);
    }

    /**
     * Comprueba que el servicio '/api/jefatura/eliminarAlumno' devuelve un listado de alumnos.
     *  @author David Sánchez Barragán
     */
    public function test_borrar_alumno()
    {
        $faker = \Faker\Factory::create('es_ES');

        $provincia = Ciudad::select('provincia')->distinct()->inRandomOrder()->first()->provincia;
        $ciudad = Ciudad::where('provincia', '=', $provincia)->select('ciudad')->inRandomOrder()->first()->ciudad;

        $alumno = Alumno::create([
            'dni' => $faker->unique->dni,
            'cod_alumno' => rand(900, 1000),
            'email' => $faker->email,
            'password' => Hash::make('superman'),
            'nombre' => $faker->firstName,
            'apellidos' => $faker->lastName,
            'provincia' => $provincia,
            'localidad' => $ciudad,
            'va_a_fct' => rand(0, 1),
        ]);


        $this->json('DELETE', '/api/jefatura/eliminarAlumno/' . $alumno->dni)
            ->assertStatus(200)
            ->assertJson([
                'mensaje' => 'Alumno borrado correctamente'
            ], 200);
    }
}
