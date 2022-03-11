<?php

namespace Database\Seeders;

use App\Models\RolProfesor;
use Illuminate\Database\Seeder;

class RolesEstudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author @DaniJCoello
     * @return void
     */
    public function run()
    {
        RolProfesor::create(['descripcion' => 'Director']);
        RolProfesor::create(['descripcion' => 'Jefatura']);
        RolProfesor::create(['descripcion' => 'Tutor']);
        RolProfesor::create(['descripcion' => 'Profesor']);
    }
}
