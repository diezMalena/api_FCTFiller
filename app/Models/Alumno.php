<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;
    protected $fillable=['dni','nombre','apellido','localidad','provincia','horario','num_horas','fecha_ini','fecha_fin','cif','cod_curso'];
    protected $table = 'alumno';
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';


    /**
     * Union entre la tabla alumno y la tabla alumno_materia, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoMateriaMany(){
       return $this->hasMany('App\Models\AlumnoMateria','dni','dni');
     }

    /**
     * Union entre la tabla alumno y la tabla alumno_materia, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoMateriaHasOne(){
        return $this->hasOne('App\Models\AlumnoMateria','dni','dni');
     }

/**
 * Union entre la tabla alumno y la tabla empresa, intercambiando
 * múltiple información
 *@author laura <lauramorenoramos97@gmail.com>
 * @return void
 */
    public function empresaMany(){
        return $this->hasMany('App\Models\Empresa','cif','cif');
     }

     /**
      * Union entre la tabla alumno y la tabla empresa, intercambiando
      *información concreta
      *@author laura <lauramorenoramos97@gmail.com>
      * @return void
      */
     public function empresaHasOne(){
        return $this->hasOne('App\Models\Empresa','cif','cif');
     }



     /**
     * Union entre la tabla alumno y la tabla curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoCursoMany(){
        return $this->hasMany('App\Models\Curso','cod_curso','cod_curso');
     }
      /**
      * Union entre la tabla alumno y la tabla curso, intercambiando
      *información concreta
      *@author laura <lauramorenoramos97@gmail.com>
      * @return void
      */
     public function alumnoCursoHasOne(){
        return $this->hasOne('App\Models\Curso','cod_curso','cod_curso');
     }
}
