<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla nivel_estudios
 *
 * Contiene la descripción del nivel de estudios al que pertenece un ciclo
 * (CFGS, CFGM, CFGB)
 *
 * @author David Sánchez Barragán (1-2-22)
 */
class NivelEstudios extends Model
{
    use HasFactory;

    protected $table = 'nivel_estudios';
    protected $primaryKey = 'cod';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['cod', 'descripcion'];
}
