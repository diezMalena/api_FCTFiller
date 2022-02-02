<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla matricula
 *
 * Contiene la información referida a los
 *
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-2-22)
 */
class Matricula extends Model
{
    use HasFactory;
    /**
     * $table->string('cod_centro');
     * $table->string('dni_alumno');
     * $table->string('cod_grupo');
     *$table->string('curso_academico');
     */
    protected $fillable = ['cod_centro', 'dni_alumno', 'cod_grupo','curso_academico'];
    protected $table = 'matricula';
    protected $primaryKey = 'matricula';
    public $incrementing = false;
    protected $keyType = 'string';
}
