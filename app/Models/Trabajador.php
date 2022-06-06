<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla trabajador
 *
 * Contiene a los trabajadores de las empresas dadas de alta
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Trabajador extends Model
{
    use HasFactory;
    protected $fillable = [
        'dni',
        'email',
        'password',
        'nombre',
        'apellidos',
        'id_empresa'
    ];
    protected $table = 'trabajador';
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['created_at', 'updated_at'];
}
