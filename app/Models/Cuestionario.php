<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Migración para crear la tabla cuestionario
 *
 * Contiene el título e identificador de cada cuestionario registrado.
 *
 * @author Pablo García
 */
class Cuestionario extends Model
{
    use HasFactory;
    protected $fillable = ['id','titulo','destinatario','codigo_centro'];
    protected $table = 'cuestionarios';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
}
