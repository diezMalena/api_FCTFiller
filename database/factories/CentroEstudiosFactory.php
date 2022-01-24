<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CentroEstudiosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cod_centro' => $this->faker->unique()->rand(11111,99999),
            'cif' => $this->faker->unique()->rand(11111111,99999999),
            'cod_centro_convenio' => $this->faker->unique()->rand(0,999),
            'nombre' => $this->faker->name(),
            'localidad' => $this->faker->city(),
            'provincia' => $this->faker->country(),
            'direccion' => $this->faker->address(),
            'cp' => rand(10000,99999),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->email()
        ];
    }
}
