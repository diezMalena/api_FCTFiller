<?php

namespace Database\Factories;

use App\Models\CentroEstudios;
use Faker\Factory as FakerFactory;
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
        $faker = FakerFactory::create('es_ES');
        return [
            'cod' => rand(11111,99999),
            'cif' => $faker->dni(),
            'cod_centro_convenio' => $faker->countryCode(),
            'nombre' => $faker->company(),
            'localidad' => $faker->city(),
            'provincia' => $faker->state(),
            'direccion' => $faker->streetAddress(),
            'cp' => $faker->postcode(),
            'telefono' => $faker->phoneNumber(),
            'email' => $faker->companyEmail()
        ];
    }
}
