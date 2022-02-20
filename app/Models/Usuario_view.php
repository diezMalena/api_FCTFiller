<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario_view extends Model
{
    use HasFactory;

    protected $table = 'usuarios_view';
    protected $fillable = ['email', 'password', 'perfil'];
}
