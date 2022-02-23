<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Usuario extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'usuarios_view';
    protected $fillable = ['email', 'nombre', 'apellidos', 'dni', 'tipo', 'roles', 'token'];
    protected $hidden = ['token'];
}
