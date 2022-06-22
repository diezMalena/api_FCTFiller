<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla alumno
 *
 * Contiene información acerca de los alumnos dados de alta en el sistema
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22, rev 10/02/2022)
 */
class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'cod_alumno',
        'email',
        'password',
        'nombre',
        'apellidos',
        'provincia',
        'localidad',
        'va_a_fct',
        'foto',
        'curriculum',
        'cuenta_bancaria',
        'matricula_coche',
        'fecha_nacimiento',
        'domicilio',
        'telefono',
        'movil'
    ];
    protected $table = 'alumno';
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';
}
