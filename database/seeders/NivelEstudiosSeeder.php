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
        NivelEstudios::create([
            'cod' => 'CFGS',
            'descripcion' => 'Ciclo formativo de Grado Superior'
        ]);
        NivelEstudios::create([
            'cod' => 'CFGM',
            'descripcion' => 'Ciclo formativo de Grado Medio'
        ]);
        NivelEstudios::create([
            'cod' => 'CFGB',
            'descripcion' => 'Ciclo formativo de Grado Básico'
        ]);
    }
}
