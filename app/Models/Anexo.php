<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo_anexo',
        'firmado_director',
        'firmado_empresa',
        'firmado_alumno',
        'ruta_anexo',
        'habilitado'
    ];
    protected $table = 'anexos';
    protected $primaryKey = 'id_anexo';
    public $incrementing = true;
}
