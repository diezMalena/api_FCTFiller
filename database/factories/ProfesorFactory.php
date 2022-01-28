<?php

namespace Database\Factories;

use App\Models\CentroEstudios;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;
    public static $CODCENTRO;

    /**
     * Define the model's default state.
     * @author @DaniJCoello
     * @return array
     */
    public function definition()
    {
        return [
            'dni' => rand(111111111, 999999999),
            'email' => $this->faker->email(),
            'password' => Hash::make('superman'),
            'nombre' => $this->faker->name(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'cod_centro_estudios' => self::$CODCENTRO
        ];
    }
}
