<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

 /**
 * Modelo para la tabla profesor.
 * Esta tabla incluye información relativa al profesorado del centro de estudios.
 * @author laura <lauramorenoramos97@gmail.com>
 * @author David Sánchez Barragán (1-1-22)
 */
class Profesor extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'email',
        'password',
        'nombre',
        'apellidos',
        'cod_centro_estudios'
    ];
    protected $table = 'profesor';
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';


    /**
     * Une la tabla profesor con centro_estudios mediante la relación
     * "profesor pertenece a centro de estudios"
     * @author @DaniJCoello
     */
    public function centroEstudios() {
        return $this->belongsTo(CentroEstudios::class, 'cod_centro_estudios', 'cod_centro');
    }
}
