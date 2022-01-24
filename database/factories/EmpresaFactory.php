<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cif' => $this->faker->unique()->rand(11111111,99999999),
            'nombre' => $this->faker->company(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'localidad' => $this->faker->city(),
            'provincia' => $this->faker->country(),
            'direccion' => $this->faker->address(),
            'cp' => rand(10000,99999)
        ];
    }
}
