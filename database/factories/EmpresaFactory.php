<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    /**
     * Define the model's default state.
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com> @DaniJCoello
     * @return array
     */
    public function definition()
    {
        return [
            'cif' => $this->faker->unique()->dni(),
            'nombre' => $this->faker->company(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'localidad' => $this->faker->city(),
            'provincia' => $this->faker->state(),
            'direccion' => $this->faker->streetAddress(),
            'cp' => $this->faker->postcode(),
            'es_privada' => rand(0,1)
        ];
    }
}
