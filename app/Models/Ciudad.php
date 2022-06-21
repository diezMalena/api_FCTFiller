<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla ciudades
 *
 * Contiene información acerca de las ciudades dadas de alta en el sistema
 *
 * @author David Sánchez Barragán
 */
class Ciudad extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'pais', 'comunidad', 'provincia', 'ciudad'];
    protected $table = 'ciudades';
}
