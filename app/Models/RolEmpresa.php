<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla rol_empresa
 *
 * Contiene los roles que desempeñan los empleados de las empresas dadas de alta
 * (tutor del alumno en el centro de trabajo, representante legal,
 * representante del centro de trabajo,...)
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class RolEmpresa extends Model
{
    use HasFactory;

    protected $table = 'rol_empresa';
    protected $fillable = ['descripcion'];
}
