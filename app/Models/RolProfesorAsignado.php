<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla rol_profesor_asignado
 *
 * Especifica los roles de cada uno de los profesores.
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class RolProfesorAsignado extends Model
{
    use HasFactory;

    protected $fillable = ['dni', 'id_rol'];
    protected $table = 'rol_profesor_asignado';
    protected $primaryKey = ['dni', 'id_rol'];
    public $incrementing = false;
    protected $keyType = ['string', 'unsignedBigInteger'];
}
