<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Migración correspondiente: seguimientos
 * Contiene los datos de las jornadas diarias que realizan los alumnos en las empresas.
 * @author Malena.
 */

class Seguimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_fct',
        'orden_jornada',
        'fecha_jornada', //Cuando el alumno hizo las actividades
        'actividades',
        'observaciones',
        'tiempo_empleado'
    ];
    protected $table = 'seguimiento';
}
