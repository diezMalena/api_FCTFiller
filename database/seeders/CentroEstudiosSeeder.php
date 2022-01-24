<?php

namespace Database\Seeders;

use App\Models\CentroEstudios;
use Illuminate\Database\Seeder;

class CentroEstudiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CentroEstudios::factory()->create(20);
    }
}
