<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla grupo
 *
 * Contiene información sobre los grupos de los ciclos formativos
 * (2DAM, 2DAW, 2ASIR,...)
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22, rev 08/02/22)
 */
class Grupo extends Model
{
    use HasFactory;

    protected $fillable = ['cod', 'nombre_largo', 'nombre_ciclo', 'cod_nivel'];
    protected $primaryKey = ['cod'];
    protected $table = 'grupo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['created_at', 'updated_at'];

}
