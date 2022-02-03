<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla rol_profesor.
 * Esta tabla contendrá los roles de los profesores
 * (Jefes de estudios, Tutores de los alumnos, Director del centro, etc...)
 * @author David Sánchez Barragán
 */
class RolProfesor extends Model
{
    use HasFactory;

    protected $table = 'rol_profesor';
    protected $fillable = ['descripcion'];

}
