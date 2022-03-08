<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutoria extends Model
{
    use HasFactory;

    protected $table = 'tutoria';
    protected $fillable = ['dni_profesor', 'cod_grupo', 'curso_academico', 'cod_centro'];
    protected $primaryKey = ['dni_profesor', 'cod_grupo','cod_centro'];
    public $incrementing = false;
}
