<?php

namespace Database\Seeders;

use App\Models\NivelEstudios;
use Illuminate\Database\Seeder;

class NivelEstudiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NivelEstudios::create(['cod' => 'CFGS', 'descripcion' => 'Ciclo Formativo Grado Superior']);
        NivelEstudios::create(['cod' => 'CFGM', 'descripcion' => 'Ciclo Formativo Grado Medio']);
        NivelEstudios::create(['cod' => 'CFGB', 'descripcion' => 'Ciclo Formativo Grado Básico']);
    }
}
