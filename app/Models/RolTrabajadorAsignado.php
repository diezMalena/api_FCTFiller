<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla rol_trabajador_asignado
 *
 * Tabla que contendrá la relación entre los trabajadores y los roles desempeñados
 * en la empresa. Un trabajador puede tener más de un rol.
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class RolTrabajadorAsignado extends Model
{
    use HasFactory;

    protected $fillable = ['dni', 'id_rol'];
    protected $table = 'rol_trabajador_asignado';
    protected $primaryKey = ['dni', 'id_rol'];
    public $incrementing = false;
    protected $keyType = ['string', 'unsignedBigInteger'];
}
