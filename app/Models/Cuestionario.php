<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Cuestionario
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
