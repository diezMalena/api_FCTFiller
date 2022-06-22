<?php

namespace Database\Seeders;

use App\Models\AuxCursoAcademico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuxCursoAcademicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $anio = intval(date('Y').'') - 1;

        for ($i=0; $i < 50; $i++) {
            AuxCursoAcademico::create([
                'cod_curso' => substr(($anio + $i).'', 2, 2) . '/' . substr(($anio + $i + 1).'', 2, 2),
                'fecha_inicio' => ($anio + $i) . '-09-01',
                'fecha_fin' => ($anio + $i + 1).'-08-31'
            ]);
        }
    }
}
