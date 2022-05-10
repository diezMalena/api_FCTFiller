<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni_alumno',
        'curso_academico',
        'tipo_desplazamiento',
        'total_gastos',
        'residencia_alumno',
        'ubicacion_centro_trabajo',
        'distancia_centroEd_centroTra',
        'distancia_centroEd_residencia',
        'distancia_centroTra_residencia',
        'dias_transporte_privado'
    ];
    protected $primaryKey = ['dni_alumno', 'curso_academico'];
    protected $table = 'gasto';
    public $incrementing = false;
    protected $keyType = ['string', 'string'];
}
