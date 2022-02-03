<?php

namespace Database\Factories;

use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    /**
     * Define the model's default state.
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     * @return array
     */
    public function definition()
    {
        return [
            'dni' => $this->faker->unique()->dni(),
            'email' => $this->faker->email(),
            'password' => Hash::make('superman'),
            'nombre' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'localidad' => $this->faker->city(),
            'provincia' => $this->faker->state(),
            'va_a_fct' => rand(0,1),
        ];
    }
}
