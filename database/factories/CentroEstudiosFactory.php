<?php

namespace Database\Factories;

use App\Models\CentroEstudios;
use Illuminate\Database\Eloquent\Factories\Factory;

class CentroEstudiosFactory extends Factory
{
    protected $model = CentroEstudios::class;

    /**
     * Define the model's default state.
     * @author @DaniJCoello
     * @return array
     */
    public function definition()
    {
        return [
            'cod' => rand(11111,99999),
            'cif' => $this->faker->cif(),
            'cod_centro_convenio' => $this->faker->countryCode(),
            'nombre' => $this->faker->company(),
            'localidad' => $this->faker->city(),
            'provincia' => $this->faker->state(),
            'direccion' => $this->faker->streetAddress(),
            'cp' => $this->faker->postcode(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail()
        ];
    }
}
