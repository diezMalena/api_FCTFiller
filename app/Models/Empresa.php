<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Migración para crear la tabla empresa
 *
 * Contiene información sobre las empresas dadas de alta en el sistema.
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'cif',
        'nombre',
        'telefono',
        'email',
        'localidad',
        'provincia',
        'direccion',
        'cp',
        'es_privada'
    ];
    protected $table = 'empresa';
    protected $hidden = ['created_at', 'updated_at'];

}
