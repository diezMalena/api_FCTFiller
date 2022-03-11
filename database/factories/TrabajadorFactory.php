<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TrabajadorFactory extends Factory
{
    protected $model = Trabajador::class;
    public static $IDEMPRESA;

    /**
     * Define the model's default state.
     * @author @DaniJCoello
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
            'id_empresa' => self::$IDEMPRESA
        ];
    }
}
