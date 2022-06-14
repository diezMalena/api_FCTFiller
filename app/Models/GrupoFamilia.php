<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla Grupo Familia
 *
 * Alberga la relación entre las tablas grupo y familia_profesional.
 * Es necesaria porque hay grupos que tienen más de una familia_profesional
 */
class GrupoFamilia extends Model
{
    use HasFactory;

    protected $fillable = ['cod_grupo', 'id_familia'];
    protected $primaryKey = ['cod_grupo', 'id_familia'];
    protected $table = 'grupo_familia';
    public $incrementing = false;
    protected $keyType = ['string', 'unsignedBigInteger'];
}
