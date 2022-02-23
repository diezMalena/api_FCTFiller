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
        FamiliaProfesional::create(['descripcion' => 'Actividades físicas y deportivas']);
        FamiliaProfesional::create(['descripcion' => 'Administración y gestión']);
        FamiliaProfesional::create(['descripcion' => 'Agraria']);
        FamiliaProfesional::create(['descripcion' => 'Artes gráficas']);
        FamiliaProfesional::create(['descripcion' => 'Artes y artesanías']);
        FamiliaProfesional::create(['descripcion' => 'Comercio y marketing']);
        FamiliaProfesional::create(['descripcion' => 'Edificación y obra civil']);
        FamiliaProfesional::create(['descripcion' => 'Electricidad y electrónica']);
        FamiliaProfesional::create(['descripcion' => 'Energía y agua']);
        FamiliaProfesional::create(['descripcion' => 'Fabricación mecánica']);
        FamiliaProfesional::create(['descripcion' => 'Hostelería y turismo']);
        FamiliaProfesional::create(['descripcion' => 'Imagen personal']);
        FamiliaProfesional::create(['descripcion' => 'Imagen y sonido']);
        FamiliaProfesional::create(['descripcion' => 'Industrias alimentarias']);
        FamiliaProfesional::create(['descripcion' => 'Industrias extractivas']);
        FamiliaProfesional::create(['descripcion' => 'Informática y comunicaciones']);
        FamiliaProfesional::create(['descripcion' => 'Instalación y mantenimiento']);
        FamiliaProfesional::create(['descripcion' => 'Madera, mueble y corcho']);
        FamiliaProfesional::create(['descripcion' => 'Marítimo-pesquera']);
        FamiliaProfesional::create(['descripcion' => 'Química']);
        FamiliaProfesional::create(['descripcion' => 'Sanidad']);
        FamiliaProfesional::create(['descripcion' => 'Seguridad y medio ambiente']);
        FamiliaProfesional::create(['descripcion' => 'Servicios socioculturales y a la comunidad']);
        FamiliaProfesional::create(['descripcion' => 'Textil, confección y piel']);
        FamiliaProfesional::create(['descripcion' => 'Transporte y mantenimiento de vehículos']);
        FamiliaProfesional::create(['descripcion' => 'Vidrio y cerámica']);
    }
}
