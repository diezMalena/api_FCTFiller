<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla convenio
 *
 * Antiguo EmpresaCentroEstudios
 *
 * Contiene información sobre los acuerdos de los centros educativos con cada empresa
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Convenio extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_convenio',
        'cod_centro',
        'id_empresa',
        'fecha_ini',
        'fecha_fin',
        'ruta_anexo'
    ];
    protected $table = 'convenio';
    protected $primaryKey = ['cod_convenio'];
    public $incrementing = false;
    protected $keyType = ['string'];
    protected $hidden = ['created_at', 'updated_at'];
}
