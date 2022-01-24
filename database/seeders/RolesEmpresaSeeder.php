<?php

namespace Database\Seeders;

use App\Models\RolesEmpresa;
use Illuminate\Database\Seeder;

class RolesEmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RolesEmpresa::create(['descripcion' => 'Representante legal']);
        RolesEmpresa::create(['descripcion' => 'Responsable de centro']);
        RolesEmpresa::create(['descripcion' => 'Tutor de empresa']);
    }
}
