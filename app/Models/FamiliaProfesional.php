<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla familia_profesional
 *
 * Contiene la descripción de las distintas familias profesionales disponibles
 * (Informática y comunicaciones, Actividades físicas y deportivas, Administración y gestión,...)
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class FamiliaProfesional extends Model
{
    use HasFactory;

    protected $fillable = ['descripcion'];
    protected $primaryKey = 'id';
    protected $table = 'familia_profesional';
    protected $hidden = ['created_at', 'updated_at'];

}
