<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semana extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_fct',
        'id_quinto_dia',
    ];

    protected $table = 'semana';

}
