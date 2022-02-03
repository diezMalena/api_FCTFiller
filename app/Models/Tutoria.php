<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutoria extends Model
{
    use HasFactory;

    protected $table = 'tutoria';
    protected $fillable = ['dni_profesor', 'cod_grupo', 'curso_academico'];
    protected $primaryKey = ['dni_profesor', 'cod_grupo'];
    public $incrementing = false;
}
