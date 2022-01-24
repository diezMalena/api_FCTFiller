<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Union entre la tabla profesor y la tabla curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorCursoMany()
    {
        return $this->hasMany('App\Models\Curso', 'dni', 'dni_tutor');
    }

    /**
     * Union entre la tabla profesor y la tabla curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorCursoHasOne()
    {
        return $this->hasOne('App\Models\Curso', 'dni', 'dni_tutor');
    }

    /**
     * Union entre la tabla profesor y la tabla rol_profesor_asignado, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorRolMany()
    {
        return $this->hasMany('App\Models\RolProfesorAsignado', 'dni', 'dni');
    }

    /**
     * Union entre la tabla profesor y la tabla rol_profesor_asignado, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorRolHasOne()
    {
        return $this->hasOne('App\Models\RolProfesorAsignado', 'dni', 'dni');
    }

    /**
     * Union entre la tabla profesor y la tabla centro_estudios, intercambiando
     * múltiple información
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorCentroEstudiosMany()
    {
        return $this->hasMany('App\Models\CentroEstudios', 'dni', 'dni_director');
    }

    /**
     * Union entre la tabla profesor y la tabla centro_estudios, intercambiando
     * información concreta
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorCentroEstudiosHasOne()
    {
        return $this->hasOne('App\Models\RolProfesorAsignado', 'dni', 'dni_director');
    }

    /**
     * Union entre la tabla profesor y la tabla centro_jefe_estudios, intercambiando
     * múltiple información
     * @author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorJefeEstudiosMany()
    {
        return $this->hasMany('App\Models\CentroJefeEstudios', 'dni', 'dni');
    }

    /**
     * Union entre la tabla profesor y la tabla centro_jefe_estudios, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function profesorJefeEstudiosHasOne()
    {
        return $this->hasOne('App\Models\CentroJefeEstudios', 'dni', 'dni');
    }

    /**
     * Une la tabla profesor con centro_estudios mediante la relación
     * "profesor pertenece a centro de estudios"
     * @author @DaniJCoello
     */
    public function centroEstudios() {
        return $this->belongsTo(CentroEstudios::class, 'cod_centro_estudios', 'cod_centro');
    }
}
