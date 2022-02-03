<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuxCursoAcademico extends Model
{
    use HasFactory;

    protected $fillable = ['cod_curso', 'fecha_inicio', 'fecha_fin'];
    protected $primaryKey = 'cod_curso';
    protected $table = 'aux_curso_academico';
    public $incrementing = false;
    protected $keyType = 'string';
}
