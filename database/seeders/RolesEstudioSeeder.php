<?php

namespace Database\Seeders;

use App\Models\RolesEstudio;
use Illuminate\Database\Seeder;

class RolesEstudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RolesEstudio::create(['descripcion' => 'Director']);
        RolesEstudio::create(['descripcion' => 'Jefatura']);
        RolesEstudio::create(['descripcion' => 'Tutor']);
    }
}
