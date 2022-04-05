<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla fct
 *
 * Contiene información sobre la relación de alumnos y la empresa en la que hacen las FCT
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Fct extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_empresa',
        'dni_alumno',
        'dni_tutor_empresa',
        'curso_academico',
        'horario','num_horas',
        'fecha_ini',
        'fecha_fin',
        'firmado_director',
        'firmado_empresa',
        'ruta_anexo',
        'departamento'
    ];
    protected $table = 'fct';


}
