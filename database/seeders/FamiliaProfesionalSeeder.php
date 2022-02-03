<?php

namespace Database\Seeders;

use App\Models\FamiliaProfesional;
use Illuminate\Database\Seeder;

class FamiliaProfesionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FamiliaProfesional::create([
            'descripcion' => 'Actividades físicas y deportivas'
        ]);
        FamiliaProfesional::create([
            'descripcion' => 'Administración y gestión'
        ]);
        FamiliaProfesional::create([
            'descripcion' => 'Comercio y marketing'
        ]);
        FamiliaProfesional::create([
            'descripcion' => 'Informática y comunicaciones'
        ]);
    }
}
