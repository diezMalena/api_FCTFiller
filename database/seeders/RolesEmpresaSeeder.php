<?php

namespace Database\Seeders;

use App\Models\RolEmpresa;
use Illuminate\Database\Seeder;

class RolesEmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author @DaniJCoello
     * @return void
     */
    public function run()
    {
        RolEmpresa::create(['descripcion' => 'Representante legal']);
        RolEmpresa::create(['descripcion' => 'Responsable de centro']);
        RolEmpresa::create(['descripcion' => 'Tutor de empresa']);
    }
}
