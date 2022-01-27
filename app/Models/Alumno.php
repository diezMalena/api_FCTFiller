<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;
    protected $fillable=['dni','nombre','apellido','localidad','provincia','cod_curso', 'va_a_fct'];
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
     * Union entre la tabla alumno y la tabla alumno_curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoCursoMany(){
        return $this->hasMany('App\Models\AlumnoCurso','dni','dni');
     }
      /**
      * Union entre la tabla alumno y la tabla alumno_curso, intercambiando
      *información concreta
      *@author laura <lauramorenoramos97@gmail.com>
      * @return void
      */
     public function alumnoCursoHasOne(){
        return $this->hasOne('App\Models\AlumnoCurso','dni','dni');
     }
}
