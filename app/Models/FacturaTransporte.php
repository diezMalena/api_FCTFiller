<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaTransporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'dni_alumno',
        'curso_academico',
        'fecha',
        'importe',
        'origen',
        'destino',
        'imagen_ticket'
    ];
    protected $primaryKey = 'id';
    protected $table = 'factura_transporte';
    public $incrementing = true;
}
