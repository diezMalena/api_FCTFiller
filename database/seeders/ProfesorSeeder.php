<?php

namespace Database\Seeders;

use App\Models\Profesor;
use Illuminate\Database\Seeder;

class ProfesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 300; $i++) {
            $profe = Profesor::factory()->create();
            $dni = $profe->dni;

        }
    }
}
