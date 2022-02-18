<?php

namespace Database\Seeders;

use App\Models\Grupo;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Grupo::create([
            'cod' => '2AF',
            'nombre_largo' => '2º de CFGS (LOE) - Acondicionamiento Físico',
            'nombre_ciclo' => 'Acondicionamiento Físico',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EAS',
            'nombre_largo' => '2º de CFGS (LOE) - Enseñanza y Animación Sociodeportiva',
            'nombre_ciclo' => 'Enseñanza y Animación Sociodeportiva',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AYF',
            'nombre_largo' => '2º de CFGS (LOE) - Administración y Finanzas',
            'nombre_ciclo' => 'Administración y Finanzas',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AALD',
            'nombre_largo' => '2º de CFGS (LOE) - Asistencia a la Dirección',
            'nombre_ciclo' => 'Asistencia a la Dirección',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GAESA',
            'nombre_largo' => '2º de CFGS (LOE) - Ganadería y Asistencia en Sanidad Animal',
            'nombre_ciclo' => 'Ganadería y Asistencia en Sanidad Animal',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GFMN',
            'nombre_largo' => '2º de CFGS (LOE) - Gestión Forestal y del Medio Natural',
            'nombre_ciclo' => 'Gestión Forestal y del Medio Natural',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PMR',
            'nombre_largo' => '2º de CFGS (LOE) - Paisajismo y Medio Rural',
            'nombre_ciclo' => 'Paisajismo y Medio Rural',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DEPIM',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño y Edición de Publicaciones Impresas y Multimedia',
            'nombre_ciclo' => 'Diseño y Edición de Publicaciones Impresas y Multimedia',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DGLPG',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño y Gestión de la Producción Gráfica',
            'nombre_ciclo' => 'Diseño y Gestión de la Producción Gráfica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2TSAFCE',
            'nombre_largo' => '2º de CFGS (LOE) - Técnico Superior Artista Fallero y Construcción de Escenografías',
            'nombre_ciclo' => 'Técnico Superior Artista Fallero y Construcción de Escenografías',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2CI',
            'nombre_largo' => '2º de CFGS (LOE) - Comercio Internacional',
            'nombre_ciclo' => 'Comercio Internacional',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GVEC',
            'nombre_largo' => '2º de CFGS (LOE) - Gestión de Ventas y Espacios Comerciales',
            'nombre_ciclo' => 'Gestión de Ventas y Espacios Comerciales',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MP',
            'nombre_largo' => '2º de CFGS (LOE) - Marketing y Publicidad',
            'nombre_ciclo' => 'Marketing y Publicidad',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2TL',
            'nombre_largo' => '2º de CFGS (LOE) - Transporte y Logística',
            'nombre_ciclo' => 'Transporte y Logística',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2OCOC',
            'nombre_largo' => '2º de CFGS (LOE) - Organización y Control de Obras de Construcción',
            'nombre_ciclo' => 'Organización y Control de Obras de Construcción',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PDEF',
            'nombre_largo' => '2º de CFGS (LOE) - Proyectos de Edificación',
            'nombre_ciclo' => 'Proyectos de Edificación',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2POC',
            'nombre_largo' => '2º de CFGS (LOE) - Proyectos de Obra Civil',
            'nombre_ciclo' => 'Proyectos de Obra Civil',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ARI',
            'nombre_largo' => '2º de CFGS (LOE) - Automatización y Robótica Industrial',
            'nombre_ciclo' => 'Automatización y Robótica Industrial',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EC',
            'nombre_largo' => '2º de CFGS (LOE) - Electromedicina Clínica',
            'nombre_ciclo' => 'Electromedicina Clínica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ME',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento Electrónico',
            'nombre_ciclo' => 'Mantenimiento Electrónico',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2SEA',
            'nombre_largo' => '2º de CFGS (LOE) - Sistemas Electrotécnicos y Automatizados',
            'nombre_ciclo' => 'Sistemas Electrotécnicos y Automatizados',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2STEI',
            'nombre_largo' => '2º de CFGS (LOE) - Sistemas de Telecomunicaciones e Informáticos',
            'nombre_ciclo' => 'Sistemas de Telecomunicaciones e Informáticos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2CE',
            'nombre_largo' => '2º de CFGS (LOE) - Centrales Eléctricas',
            'nombre_ciclo' => 'Centrales Eléctricas',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EEEST',
            'nombre_largo' => '2º de CFGS (LOE) - Eficiencia Energética y Energía Solar Térmica',
            'nombre_ciclo' => 'Eficiencia Energética y Energía Solar Térmica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ER',
            'nombre_largo' => '2º de CFGS (LOE) - Energías Renovables',
            'nombre_ciclo' => 'Energías Renovables',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GDA',
            'nombre_largo' => '2º de CFGS (LOE) - Gestión del Agua',
            'nombre_ciclo' => 'Gestión del Agua',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2CM',
            'nombre_largo' => '2º de CFGS (LOE) - Construcciones Metálicas',
            'nombre_ciclo' => 'Construcciones Metálicas',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DEFM',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño en Fabricación Mecánica',
            'nombre_ciclo' => 'Diseño en Fabricación Mecánica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PLPEFM',
            'nombre_largo' => '2º de CFGS (LOE) - Programación de la Producción en Fabricación Mecánica',
            'nombre_ciclo' => 'Programación de la Producción en Fabricación Mecánica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PLPEMMP',
            'nombre_largo' => '2º de CFGS (LOE) - Programación de la Producción en Moldeo de Metales y Polímeros',
            'nombre_ciclo' => 'Programación de la Producción en Moldeo de Metales y Polímeros',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AVGE',
            'nombre_largo' => '2º de CFGS (LOE) - Agencias de Viajes y Gestión de Eventos',
            'nombre_ciclo' => 'Agencias de Viajes y Gestión de Eventos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DC',
            'nombre_largo' => '2º de CFGS (LOE) - Dirección de Cocina',
            'nombre_ciclo' => 'Dirección de Cocina',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DSR',
            'nombre_largo' => '2º de CFGS (LOE) - Dirección de Servicios de Restauración',
            'nombre_ciclo' => 'Dirección de Servicios de Restauración',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GAT',
            'nombre_largo' => '2º de CFGS (LOE) - Gestión de Alojamientos Turísticos',
            'nombre_ciclo' => 'Gestión de Alojamientos Turísticos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2GIAT',
            'nombre_largo' => '2º de CFGS (LOE) - Guía, Información y Asistencias Turísticas',
            'nombre_ciclo' => 'Guía, Información y Asistencias Turísticas',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AIPC',
            'nombre_largo' => '2º de CFGS (LOE) - Asesoría de Imagen Personal y Corporativa',
            'nombre_ciclo' => 'Asesoría de Imagen Personal y Corporativa',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2CMP',
            'nombre_largo' => '2º de CFGS (LOE) - Caracterización y Maquillaje Profesional',
            'nombre_ciclo' => 'Caracterización y Maquillaje Profesional',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EDP',
            'nombre_largo' => '2º de CFGS (LOE) - Estilismo y Dirección de Peluquería',
            'nombre_ciclo' => 'Estilismo y Dirección de Peluquería',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EIB',
            'nombre_largo' => '2º de CFGS (LOE) - Estética Integral y Bienestar',
            'nombre_ciclo' => 'Estética Integral y Bienestar',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2TB',
            'nombre_largo' => '2º de CFGS (LOE) - Termalismo y Bienestar',
            'nombre_ciclo' => 'Termalismo y Bienestar',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2A3JEI',
            'nombre_largo' => '2º de CFGS (LOE) - Animaciones 3D, Juegos y Entornos Interactivos',
            'nombre_ciclo' => 'Animaciones 3D, Juegos y Entornos Interactivos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ICTI',
            'nombre_largo' => '2º de CFGS (LOE) - Iluminación, Captación y Tratamiento de Imagen',
            'nombre_ciclo' => 'Iluminación, Captación y Tratamiento de Imagen',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PAYE',
            'nombre_largo' => '2º de CFGS (LOE) - Producción de Audiovisuales y Espectáculos',
            'nombre_ciclo' => 'Producción de Audiovisuales y Espectáculos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2RPAE',
            'nombre_largo' => '2º de CFGS (LOE) - Realización de Proyectos Audiovisuales y Espectáculos',
            'nombre_ciclo' => 'Realización de Proyectos Audiovisuales y Espectáculos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2SPAE',
            'nombre_largo' => '2º de CFGS (LOE) - Sonido para Audiovisuales y Espectáculos',
            'nombre_ciclo' => 'Sonido para Audiovisuales y Espectáculos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PCELIA',
            'nombre_largo' => '2º de CFGS (LOE) - Procesos y Calidad en la Industria Alimentaria',
            'nombre_ciclo' => 'Procesos y Calidad en la Industria Alimentaria',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2V',
            'nombre_largo' => '2º de CFGS (LOE) - Vitivinicultura',
            'nombre_ciclo' => 'Vitivinicultura',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ASIER',
            'nombre_largo' => '2º de CFGS (LOE) - Administración de Sistemas Informáticos en Red',
            'nombre_ciclo' => 'Administración de Sistemas Informáticos en Red',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DAM',
            'nombre_largo' => '2º de CFGS (LOE) - Desarrollo de Aplicaciones Multiplataforma',
            'nombre_ciclo' => 'Desarrollo de Aplicaciones Multiplataforma',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DAW',
            'nombre_largo' => '2º de CFGS (LOE) - Desarrollo de Aplicaciones Web',
            'nombre_ciclo' => 'Desarrollo de Aplicaciones Web',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DPITF',
            'nombre_largo' => '2º de CFGS (LOE) - Desarrollo de Proyectos de Instalaciones Térmicas y de Fluidos',
            'nombre_ciclo' => 'Desarrollo de Proyectos de Instalaciones Térmicas y de Fluidos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MITF',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento de Instalaciones Térmicas y de Fluidos',
            'nombre_ciclo' => 'Mantenimiento de Instalaciones Térmicas y de Fluidos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MI',
            'nombre_largo' => '2º de CFGS (LOE) - Mecatrónica Industrial',
            'nombre_ciclo' => 'Mecatrónica Industrial',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DA1',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño y Amueblamiento',
            'nombre_ciclo' => 'Diseño y Amueblamiento',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ACC',
            'nombre_largo' => '2º de CFGS (LOE) - Acuicultura',
            'nombre_ciclo' => 'Acuicultura',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2OMMBE',
            'nombre_largo' => '2º de CFGS (LOE) - Organización del Mantenimiento de Maquinaria de Buques y Embarcaciones',
            'nombre_ciclo' => 'Organización del Mantenimiento de Maquinaria de Buques y Embarcaciones',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2TMPA',
            'nombre_largo' => '2º de CFGS (LOE) - Transporte Marítimo y Pesca de Altura',
            'nombre_ciclo' => 'Transporte Marítimo y Pesca de Altura',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2FPFBA',
            'nombre_largo' => '2º de CFGS (LOE) - Fabricación de Productos Farmacéuticos, Biotecnológicos y Afines',
            'nombre_ciclo' => 'Fabricación de Productos Farmacéuticos, Biotecnológicos y Afines',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2LACC',
            'nombre_largo' => '2º de CFGS (LOE) - Laboratorio de Análisis y de Control de Calidad',
            'nombre_ciclo' => 'Laboratorio de Análisis y de Control de Calidad',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2QI',
            'nombre_largo' => '2º de CFGS (LOE) - Química Industrial',
            'nombre_ciclo' => 'Química Industrial',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2APC',
            'nombre_largo' => '2º de CFGS (LOE) - Anatomía Patológica y Citodiagnóstico',
            'nombre_ciclo' => 'Anatomía Patológica y Citodiagnóstico',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AP',
            'nombre_largo' => '2º de CFGS (LOE) - Audiología Protésica',
            'nombre_ciclo' => 'Audiología Protésica',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DAS',
            'nombre_largo' => '2º de CFGS (LOE) - Documentación y Administración Sanitarias',
            'nombre_ciclo' => 'Documentación y Administración Sanitarias',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2HB',
            'nombre_largo' => '2º de CFGS (LOE) - Higiene Bucodental',
            'nombre_ciclo' => 'Higiene Bucodental',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2IPEDMN',
            'nombre_largo' => '2º de CFGS (LOE) - Imagen para el Diagnóstico y Medicina Nuclear',
            'nombre_ciclo' => 'Imagen para el Diagnóstico y Medicina Nuclear',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2LCB',
            'nombre_largo' => '2º de CFGS (LOE) - Laboratorio Clínico y Biomédico',
            'nombre_ciclo' => 'Laboratorio Clínico y Biomédico',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2OPA',
            'nombre_largo' => '2º de CFGS (LOE) - Ortoprótesis y Productos de Apoyo',
            'nombre_ciclo' => 'Ortoprótesis y Productos de Apoyo',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PDE',
            'nombre_largo' => '2º de CFGS (LOE) - Prótesis Dentales',
            'nombre_ciclo' => 'Prótesis Dentales',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2RD',
            'nombre_largo' => '2º de CFGS (LOE) - Radioterapia y Dosimetría',
            'nombre_ciclo' => 'Radioterapia y Dosimetría',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2CEPC',
            'nombre_largo' => '2º de CFGS (LOE) - Coordinación de Emergencias y Protección Civil',
            'nombre_ciclo' => 'Coordinación de Emergencias y Protección Civil',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ECA',
            'nombre_largo' => '2º de CFGS (LOE) - Educación y Control Ambiental',
            'nombre_ciclo' => 'Educación y Control Ambiental',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2QSA',
            'nombre_largo' => '2º de CFGS (LOE) - Química y Salud Ambiental',
            'nombre_ciclo' => 'Química y Salud Ambiental',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AST',
            'nombre_largo' => '2º de CFGS (LOE) - Animación Sociocultural y Turística',
            'nombre_ciclo' => 'Animación Sociocultural y Turística',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2EI',
            'nombre_largo' => '2º de CFGS (LOE) - Educación Infantil',
            'nombre_ciclo' => 'Educación Infantil',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2FPLMSS',
            'nombre_largo' => '2º de CFGS (LOE) - Formación para la Movilidad Segura y Sostenible',
            'nombre_ciclo' => 'Formación para la Movilidad Segura y Sostenible',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2IS',
            'nombre_largo' => '2º de CFGS (LOE) - Integración Social',
            'nombre_ciclo' => 'Integración Social',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MC',
            'nombre_largo' => '2º de CFGS (LOE) - Mediación Comunicativa',
            'nombre_ciclo' => 'Mediación Comunicativa',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PIG',
            'nombre_largo' => '2º de CFGS (LOE) - Promoción de Igualdad de Género',
            'nombre_ciclo' => 'Promoción de Igualdad de Género',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DTETP',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño Técnico en Textil y Piel',
            'nombre_ciclo' => 'Diseño Técnico en Textil y Piel',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DPCC',
            'nombre_largo' => '2º de CFGS (LOE) - Diseño y Producción de Calzado y Complementos',
            'nombre_ciclo' => 'Diseño y Producción de Calzado y Complementos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2PM',
            'nombre_largo' => '2º de CFGS (LOE) - Patronaje y Moda',
            'nombre_ciclo' => 'Patronaje y Moda',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2VAME2',
            'nombre_largo' => '2º de CFGS (LOE) - Vestuario a Medida y de Espectáculos',
            'nombre_ciclo' => 'Vestuario a Medida y de Espectáculos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2ATM',
            'nombre_largo' => '2º de CFGS (LOE) - Automoción',
            'nombre_ciclo' => 'Automoción',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MAACMP',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento Aeromecánico de Aviones con Motor de Pistón',
            'nombre_ciclo' => 'Mantenimiento Aeromecánico de Aviones con Motor de Pistón',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MAACMT',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento Aeromecánico de Aviones con Motor de Turbina',
            'nombre_ciclo' => 'Mantenimiento Aeromecánico de Aviones con Motor de Turbina',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MAHCMP',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento Aeromecánico de Helicópteros con Motor de Pistón',
            'nombre_ciclo' => 'Mantenimiento Aeromecánico de Helicópteros con Motor de Pistón',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MAHCMT',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento Aeromecánico de Helicópteros con Motor de Turbina',
            'nombre_ciclo' => 'Mantenimiento Aeromecánico de Helicópteros con Motor de Turbina',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2MSEAA',
            'nombre_largo' => '2º de CFGS (LOE) - Mantenimiento de Sistemas Electrónicos y Aviónicos de Aeronaves',
            'nombre_ciclo' => 'Mantenimiento de Sistemas Electrónicos y Aviónicos de Aeronaves',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2DFPC',
            'nombre_largo' => '2º de CFGS (LOE) - Desarrollo y Fabricación de Productos Cerámicos',
            'nombre_ciclo' => 'Desarrollo y Fabricación de Productos Cerámicos',
            'cod_nivel' => 'CFGS'
        ]);
        Grupo::create([
            'cod' => '2AE',
            'nombre_largo' => '2º de CFGM (LOE) - Actividades Ecuestres',
            'nombre_ciclo' => 'Actividades Ecuestres',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2GEEMNTLD',
            'nombre_largo' => '2º de CFGM (LOE) - Guía en el Medio Natural y de Tiempo Libre',
            'nombre_ciclo' => 'Guía en el Medio Natural y de Tiempo Libre',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2GAD',
            'nombre_largo' => '2º de CFGM (LOE) - Gestión Administrativa',
            'nombre_ciclo' => 'Gestión Administrativa',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2ACMN',
            'nombre_largo' => '2º de CFGM (LOE) - Aprovechamiento y Conservación del Medio Natural',
            'nombre_ciclo' => 'Aprovechamiento y Conservación del Medio Natural',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2JF',
            'nombre_largo' => '2º de CFGM (LOE) - Jardinería y Floristería',
            'nombre_ciclo' => 'Jardinería y Floristería',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PAE',
            'nombre_largo' => '2º de CFGM (LOE) - Producción Agroecológica',
            'nombre_ciclo' => 'Producción Agroecológica',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PAPE',
            'nombre_largo' => '2º de CFGM (LOE) - Producción Agropecuaria',
            'nombre_ciclo' => 'Producción Agropecuaria',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IG',
            'nombre_largo' => '2º de CFGM (LOE) - Impresión Gráfica',
            'nombre_ciclo' => 'Impresión Gráfica',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PAG',
            'nombre_largo' => '2º de CFGM (LOE) - Postimpresión y Acabados Gráficos',
            'nombre_ciclo' => 'Postimpresión y Acabados Gráficos',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PDI',
            'nombre_largo' => '2º de CFGM (LOE) - Preimpresión Digital',
            'nombre_ciclo' => 'Preimpresión Digital',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2AC1',
            'nombre_largo' => '2º de CFGM (LOE) - Actividades Comerciales',
            'nombre_ciclo' => 'Actividades Comerciales',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CPA',
            'nombre_largo' => '2º de CFGM (LOE) - Comercialización de Productos Alimentarios',
            'nombre_ciclo' => 'Comercialización de Productos Alimentarios',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2C',
            'nombre_largo' => '2º de CFGM (LOE) - Construcción',
            'nombre_ciclo' => 'Construcción',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2OIDR',
            'nombre_largo' => '2º de CFGM (LOE) - Obras de Interior, Decoración y Rehabilitación',
            'nombre_ciclo' => 'Obras de Interior, Decoración y Rehabilitación',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IEA',
            'nombre_largo' => '2º de CFGM (LOE) - Instalaciones Eléctricas y Automáticas',
            'nombre_ciclo' => 'Instalaciones Eléctricas y Automáticas',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IT',
            'nombre_largo' => '2º de CFGM (LOE) - Instalaciones de Telecomunicaciones',
            'nombre_ciclo' => 'Instalaciones de Telecomunicaciones',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2RETA',
            'nombre_largo' => '2º de CFGM (LOE) - Redes y Estaciones de Tratamiento de Aguas',
            'nombre_ciclo' => 'Redes y Estaciones de Tratamiento de Aguas',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CPMMP',
            'nombre_largo' => '2º de CFGM (LOE) - Conformado por Moldeo de Metales y Polímeros',
            'nombre_ciclo' => 'Conformado por Moldeo de Metales y Polímeros',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2M',
            'nombre_largo' => '2º de CFGM (LOE) - Mecanizado',
            'nombre_ciclo' => 'Mecanizado',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MEEISA',
            'nombre_largo' => '2º de CFGM (LOE) - Montaje de Estructuras e Instalación de Sistemas Aeronáuticos',
            'nombre_ciclo' => 'Montaje de Estructuras e Instalación de Sistemas Aeronáuticos',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2SYC',
            'nombre_largo' => '2º de CFGM (LOE) - Soldadura y Calderería',
            'nombre_ciclo' => 'Soldadura y Calderería',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CG',
            'nombre_largo' => '2º de CFGM (LOE) - Cocina y Gastronomía',
            'nombre_ciclo' => 'Cocina y Gastronomía',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2SER',
            'nombre_largo' => '2º de CFGM (LOE) - Servicios en Restauración',
            'nombre_ciclo' => 'Servicios en Restauración',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EB',
            'nombre_largo' => '2º de CFGM (LOE) - Estética y Belleza',
            'nombre_ciclo' => 'Estética y Belleza',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PCC',
            'nombre_largo' => '2º de CFGM (LOE) - Peluquería y Cosmética Capilar',
            'nombre_ciclo' => 'Peluquería y Cosmética Capilar',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2VDS',
            'nombre_largo' => '2º de CFGM (LOE) - Vídeo Disc-Jockey y Sonido',
            'nombre_ciclo' => 'Vídeo Disc-Jockey y Sonido',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2AOV1',
            'nombre_largo' => '2º de CFGM (LOE) - Aceites de Oliva y Vinos',
            'nombre_ciclo' => 'Aceites de Oliva y Vinos',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EPA',
            'nombre_largo' => '2º de CFGM (LOE) - Elaboración de Productos Alimenticios',
            'nombre_ciclo' => 'Elaboración de Productos Alimenticios',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PRC',
            'nombre_largo' => '2º de CFGM (LOE) - Panadería, Repostería y Confitería',
            'nombre_ciclo' => 'Panadería, Repostería y Confitería',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EYS',
            'nombre_largo' => '2º de CFGM (LOE) - Excavaciones y Sondeos',
            'nombre_ciclo' => 'Excavaciones y Sondeos',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PN',
            'nombre_largo' => '2º de CFGM (LOE) - Piedra Natural',
            'nombre_ciclo' => 'Piedra Natural',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2SMR',
            'nombre_largo' => '2º de CFGM (LOE) - Sistemas Microinformáticos y Redes',
            'nombre_ciclo' => 'Sistemas Microinformáticos y Redes',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IFC',
            'nombre_largo' => '2º de CFGM (LOE) - Instalaciones Frigoríficas y de Climatización',
            'nombre_ciclo' => 'Instalaciones Frigoríficas y de Climatización',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IPC',
            'nombre_largo' => '2º de CFGM (LOE) - Instalaciones de Producción de Calor',
            'nombre_ciclo' => 'Instalaciones de Producción de Calor',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MEM',
            'nombre_largo' => '2º de CFGM (LOE) - Mantenimiento Electromecánico',
            'nombre_ciclo' => 'Mantenimiento Electromecánico',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CAYMM',
            'nombre_largo' => '2º de CFGM (LOE) - Carpintería y Mueble',
            'nombre_ciclo' => 'Carpintería y Mueble',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2IYA',
            'nombre_largo' => '2º de CFGM (LOE) - Instalación y Amueblamiento',
            'nombre_ciclo' => 'Instalación y Amueblamiento',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PTLM',
            'nombre_largo' => '2º de CFGM (LOE) - Procesado y Transformación de la Madera',
            'nombre_ciclo' => 'Procesado y Transformación de la Madera',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CA',
            'nombre_largo' => '2º de CFGM (LOE) - Cultivos Acuícolas',
            'nombre_ciclo' => 'Cultivos Acuícolas',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MCLMBE',
            'nombre_largo' => '2º de CFGM (LOE) - Mantenimiento y Control de la Maquinaria de Buques y Embarcaciones',
            'nombre_ciclo' => 'Mantenimiento y Control de la Maquinaria de Buques y Embarcaciones',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2NPL',
            'nombre_largo' => '2º de CFGM (LOE) - Navegación y Pesca de Litoral',
            'nombre_ciclo' => 'Navegación y Pesca de Litoral',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2OSEH2',
            'nombre_largo' => '2º de CFGM (LOE) - Operaciones Subacuáticas e Hiperbáricas',
            'nombre_ciclo' => 'Operaciones Subacuáticas e Hiperbáricas',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2OL',
            'nombre_largo' => '2º de CFGM (LOE) - Operaciones de Laboratorio',
            'nombre_ciclo' => 'Operaciones de Laboratorio',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2PQ',
            'nombre_largo' => '2º de CFGM (LOE) - Planta Química',
            'nombre_ciclo' => 'Planta Química',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2ES',
            'nombre_largo' => '2º de CFGM (LOE) - Emergencias Sanitarias',
            'nombre_ciclo' => 'Emergencias Sanitarias',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2FP',
            'nombre_largo' => '2º de CFGM (LOE) - Farmacia y Parafarmacia',
            'nombre_ciclo' => 'Farmacia y Parafarmacia',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EPC',
            'nombre_largo' => '2º de CFGM (LOE) - Emergencias y Protección Civil',
            'nombre_ciclo' => 'Emergencias y Protección Civil',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2AAPESD',
            'nombre_largo' => '2º de CFGM (LOE) - Atención a Personas en Situación de Dependencia',
            'nombre_ciclo' => 'Atención a Personas en Situación de Dependencia',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CCM2',
            'nombre_largo' => '2º de CFGM (LOE) - Calzado y Complementos de Moda',
            'nombre_ciclo' => 'Calzado y Complementos de Moda',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CYM',
            'nombre_largo' => '2º de CFGM (LOE) - Confección y Moda',
            'nombre_ciclo' => 'Confección y Moda',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2FEPT',
            'nombre_largo' => '2º de CFGM (LOE) - Fabricación y Ennoblecimiento de Productos Textiles',
            'nombre_ciclo' => 'Fabricación y Ennoblecimiento de Productos Textiles',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CARR',
            'nombre_largo' => '2º de CFGM (LOE) - Carrocería',
            'nombre_ciclo' => 'Carrocería',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2CVTPC',
            'nombre_largo' => '2º de CFGM (LOE) - Conducción de Vehículos de Transporte por Carretera',
            'nombre_ciclo' => 'Conducción de Vehículos de Transporte por Carretera',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EM',
            'nombre_largo' => '2º de CFGM (LOE) - Electromecánica de Maquinaria',
            'nombre_ciclo' => 'Electromecánica de Maquinaria',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2EVA',
            'nombre_largo' => '2º de CFGM (LOE) - Electromecánica de Vehículos Automóviles',
            'nombre_ciclo' => 'Electromecánica de Vehículos Automóviles',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MER',
            'nombre_largo' => '2º de CFGM (LOE) - Mantenimiento de Embarcaciones de Recreo',
            'nombre_ciclo' => 'Mantenimiento de Embarcaciones de Recreo',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MEMMER',
            'nombre_largo' => '2º de CFGM (LOE) - Mantenimiento de Estructuras de Madera y Mobiliario de Embarcaciones de Recreo',
            'nombre_ciclo' => 'Mantenimiento de Estructuras de Madera y Mobiliario de Embarcaciones de Recreo',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2MMRF',
            'nombre_largo' => '2º de CFGM (LOE) - Mantenimiento de Material Rodante Ferroviario',
            'nombre_ciclo' => 'Mantenimiento de Material Rodante Ferroviario',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2FPC',
            'nombre_largo' => '2º de CFGM (LOE) - Fabricación de Productos Cerámicos',
            'nombre_ciclo' => 'Fabricación de Productos Cerámicos',
            'cod_nivel' => 'CFGM'
        ]);
        Grupo::create([
            'cod' => '2ACEID',
            'nombre_largo' => '2º de CFGB (LOE) - Acceso y Conservación en Instalaciones Deportivas',
            'nombre_ciclo' => 'Acceso y Conservación en Instalaciones Deportivas',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2IO',
            'nombre_largo' => '2º de CFGB (LOE) - Informática de Oficina',
            'nombre_ciclo' => 'Informática de Oficina',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2SA',
            'nombre_largo' => '2º de CFGB (LOE) - Servicios Administrativos',
            'nombre_ciclo' => 'Servicios Administrativos',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2AA',
            'nombre_largo' => '2º de CFGB (LOE) - Actividades Agropecuarias',
            'nombre_ciclo' => 'Actividades Agropecuarias',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2ACF',
            'nombre_largo' => '2º de CFGB (LOE) - Agro-jardinería y Composiciones Florales',
            'nombre_ciclo' => 'Agro-jardinería y Composiciones Florales',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2AFO',
            'nombre_largo' => '2º de CFGB (LOE) - Aprovechamientos Forestales',
            'nombre_ciclo' => 'Aprovechamientos Forestales',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2AG',
            'nombre_largo' => '2º de CFGB (LOE) - Artes Gráficas',
            'nombre_ciclo' => 'Artes Gráficas',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2SCO',
            'nombre_largo' => '2º de CFGB (LOE) - Servicios Comerciales',
            'nombre_ciclo' => 'Servicios Comerciales',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2RME',
            'nombre_largo' => '2º de CFGB (LOE) - Reforma y Mantenimiento de Edificios',
            'nombre_ciclo' => 'Reforma y Mantenimiento de Edificios',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2EE',
            'nombre_largo' => '2º de CFGB (LOE) - Electricidad y Electrónica',
            'nombre_ciclo' => 'Electricidad y Electrónica',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2FEM',
            'nombre_largo' => '2º de CFGB (LOE) - Fabricación de Elementos Metálicos',
            'nombre_ciclo' => 'Fabricación de Elementos Metálicos',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2IEM',
            'nombre_largo' => '2º de CFGB (LOE) - Instalaciones Electrotécnicas y Mecánica',
            'nombre_ciclo' => 'Instalaciones Electrotécnicas y Mecánica',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2FM',
            'nombre_largo' => '2º de CFGB (LOE) - Fabricación y Montaje',
            'nombre_ciclo' => 'Fabricación y Montaje',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2APP',
            'nombre_largo' => '2º de CFGB (LOE) - Actividades de Panadería y Pastelería',
            'nombre_ciclo' => 'Actividades de Panadería y Pastelería',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2AL',
            'nombre_largo' => '2º de CFGB (LOE) - Alojamiento y Lavandería',
            'nombre_ciclo' => 'Alojamiento y Lavandería',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2CR',
            'nombre_largo' => '2º de CFGB (LOE) - Cocina y Restauración',
            'nombre_ciclo' => 'Cocina y Restauración',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2PYE',
            'nombre_largo' => '2º de CFGB (LOE) - Peluquería y Estética',
            'nombre_ciclo' => 'Peluquería y Estética',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2IA',
            'nombre_largo' => '2º de CFGB (LOE) - Industrias Alimentarias',
            'nombre_ciclo' => 'Industrias Alimentarias',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2IC',
            'nombre_largo' => '2º de CFGB (LOE) - Informática y Comunicaciones',
            'nombre_ciclo' => 'Informática y Comunicaciones',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2MV',
            'nombre_largo' => '2º de CFGB (LOE) - Mantenimiento de Viviendas',
            'nombre_ciclo' => 'Mantenimiento de Viviendas',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2CAYMB',
            'nombre_largo' => '2º de CFGB (LOE) - Carpintería y Mueble',
            'nombre_ciclo' => 'Carpintería y Mueble',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2AM',
            'nombre_largo' => '2º de CFGB (LOE) - Actividades Marítimo-Pesqueras',
            'nombre_ciclo' => 'Actividades Marítimo-Pesqueras',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2MEDR',
            'nombre_largo' => '2º de CFGB (LOE) - Mantenimiento de Embarcaciones Deportivas y de Recreo',
            'nombre_ciclo' => 'Mantenimiento de Embarcaciones Deportivas y de Recreo',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2ADLE',
            'nombre_largo' => '2º de CFGB (LOE) - Actividades Domésticas y Limpieza de Edificios',
            'nombre_ciclo' => 'Actividades Domésticas y Limpieza de Edificios',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2ARATP',
            'nombre_largo' => '2º de CFGB (LOE) - Arreglo y Reparación de Artículos Textiles y de Piel',
            'nombre_ciclo' => 'Arreglo y Reparación de Artículos Textiles y de Piel',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2TC',
            'nombre_largo' => '2º de CFGB (LOE) - Tapicería y Cortinaje',
            'nombre_ciclo' => 'Tapicería y Cortinaje',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2MVH',
            'nombre_largo' => '2º de CFGB (LOE) - Mantenimiento de Vehículos',
            'nombre_ciclo' => 'Mantenimiento de Vehículos',
            'cod_nivel' => 'CFGB'
        ]);
        Grupo::create([
            'cod' => '2VA',
            'nombre_largo' => '2º de CFGB (LOE) - Vidriería y Alfarería',
            'nombre_ciclo' => 'Vidriería y Alfarería',
            'cod_nivel' => 'CFGB'
        ]);
    }
}
