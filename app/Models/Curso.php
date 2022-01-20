<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $fillable=['cod_curso','anio','estudio','dni_tutor'];
    protected $table = 'curso';
    protected $primaryKey = 'cod_curso';
    public $incrementing = false;
    protected $keyType = 'string';



     /**
     * Union entre la tabla curso y la tabla alumno, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoMany(){
        return $this->hasMany('App\Models\Alumno','cod_curso','cod_curso');
      }
     /**
     * Union entre la tabla curso y la tabla alumno, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function alumnoHasOne(){
         return $this->hasOne('App\Models\Alumno','cod_curso','cod_curso');
      }



     /**
     * Union entre la tabla curso y la tabla alumno_curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function CursoAlumnoMany(){
        return $this->hasMany('App\Models\AlumnoCurso','cod_curso','cod_curso');
      }
     /**
     * Union entre la tabla curso y la tabla alumno_curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function CursoAlumnoHasOne(){
         return $this->hasOne('App\Models\AlumnoCurso','cod_curso','cod_curso');
      }



    /**
     * Union entre la tabla curso y la tabla materia_curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function CursoMateriaMany(){
        return $this->hasMany('App\Models\MateriaCurso','cod_curso','cod_curso');
      }
      /**
     * Union entre la tabla curso y la tabla materia_curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function CursoMateriaHasOne(){
         return $this->hasOne('App\Models\MateriaCurso','cod_curso','cod_curso');
      }
}
