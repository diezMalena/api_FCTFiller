<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Modelo CuestionarioRespondido
 * @author Pablo García
 */
class CuestionarioRespondido extends Model
{
    use HasFactory;
    protected $fillable = ['id','titulo','destinatario','id_usuario','codigo_centro','ciclo','curso_academico','dni_tutor_empresa'];
    protected $table = 'cuestionario_respondidos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
}
