<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreguntasCuestionario extends Model
{
    use HasFactory;

    protected $fillable = ['id','id_cuestionario','tipo','pregunta'];
    protected $table = 'preguntas_cuestionario';

}


