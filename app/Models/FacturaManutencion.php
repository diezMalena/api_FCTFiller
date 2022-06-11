<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaManutencion extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'dni_alumno',
        'curso_academico',
        'fecha',
        'importe',
        'imagen_ticket'
    ];
    protected $primaryKey = 'id';
    protected $table = 'factura_manutencion';
    public $incrementing = true;
}
