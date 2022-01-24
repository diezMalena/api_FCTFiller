<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TrabajadorFactory extends Factory
{
    protected $model = Trabajador::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'dni' => $this->faker->rand(111111111, 999999999),
            'email' => $this->faker->email(),
            'password' => Hash::make('superman'),
            'nombre' => $this->faker->name(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'cif_empresa' => Empresa::all()->random(1)->get('cif')
        ];
    }
}
