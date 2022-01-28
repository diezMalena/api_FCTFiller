<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;
    protected $fillable=['cod_materia','descripcion','abreviatura'];
    protected $table = 'materia';
    protected $primaryKey = 'cod_materia';
    public $incrementing = false;
    protected $keyType = 'string';




      /**
     * Union entre la tabla materia y la tabla materia_curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function materiaCursoMany(){
       return $this->hasMany('App\Models\MateriaCurso','cod_materia','cod_materia');
    }
    /**
     * Union entre la tabla materia y la tabla materia_curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function materiaCursoHasOne(){
        return $this->hasOne('App\Models\MateriaCurso','cod_materia','cod_materia');
     }



    /**
     * Union entre la tabla materia y la tabla alumno_materia, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function materiaAlumnoMany(){
        return $this->hasMany('App\Models\AlumnoMateria','cod_materia','cod_materia');
     }
    /**
     * Union entre la tabla materia y la tabla alumno_materia, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function materiaAlumnoHasOne(){
        return $this->hasOne('App\Models\AlumnoMateria','cod_materia','cod_materia');
     }


}
