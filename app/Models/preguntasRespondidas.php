<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preguntasRespondidas extends Model
{
    use HasFactory;

    protected $fillable = ['id','id_cuestionario_respondido','tipo','pregunta','respuesta'];
    protected $table = 'preguntas_respondidas';

}
