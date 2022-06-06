<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'notificaciones';
    protected $fillable = ['email','mensaje','leido','semana'];
}
