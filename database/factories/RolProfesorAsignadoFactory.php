<?php

namespace Database\Factories;

use App\Models\RolProfesorAsignado;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolProfesorAsignadoFactory extends Factory
{
    protected $model = RolProfesorAsignado::class;
    public static $DNI;
    public static $ROL;

    /**
     * Define the model's default state.
     * @author @DaniJCoello
     * @return array
     */
    public function definition()
    {
        return [
            'dni' => self::$DNI,
            'id_rol' => self::$ROL
        ];
    }
}
