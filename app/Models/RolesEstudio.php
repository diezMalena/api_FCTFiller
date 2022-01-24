<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesEstudio extends Model
{
    use HasFactory;

    protected $table = 'roles_estudio';
    protected $fillable = ['descripcion'];

    /**
     * Union entre la tabla roles_estudio y la tabla rol_trabajador_asignado intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolProfesorAsignadoMany()
    {
        return $this->hasMany('App\Models\RolProfesroAsignado', 'id', 'id_rol');
    }

    /**
     * Union entre la tabla roles_estudio y la tabla rol_trabajador_asignado, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolProfesorAsignadoHasOne()
    {
        return $this->hasOne('App\Models\RolProfesroAsignado', 'id', 'id_rol');
    }
}
