<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla matricula
 *
 * Contiene la información referida a los alumnos matriculados
 * por centro y por curso académico en un grupo concreto
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Matricula extends Model
{
    use HasFactory;

    protected $fillable = ['cod', 'cod_centro', 'dni_alumno', 'cod_grupo','curso_academico'];
    protected $table = 'matricula';
    protected $primaryKey = 'cod';
    public $incrementing = false;
    protected $keyType = 'string';
}
