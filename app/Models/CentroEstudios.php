<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Migración para crear la tabla centro_estudios
 *
 * Contiene información sobre los centros educativos dados de alta en el sistema.
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class CentroEstudios extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod',
        'cif',
        'cod_centro_convenio',
        'nombre',
        'localidad',
        'provincia',
        'direccion',
        'cp',
        'telefono',
        'email'
    ];
    protected $table = 'centro_estudios';
    protected $primaryKey = 'cod';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['created_at', 'updated_at'];
}
