<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoCurso extends Model
{
    use HasFactory;
    protected $fillable=['dni','cod_curso','matricula'];
    protected $table = 'alumno_curso';
    protected $primaryKey = 'matricula';
    public $incrementing = false;
    protected $keyType = 'string';



     /**
     * Union entre la tabla alumno_curso y la tabla curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function cursoMany(){
        return $this->hasMany('App\Models\Curso','cod_curso','cod_curso');
      }
    /**
     * Union entre la tabla alumno_curso y la tabla curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function cursoHasOne(){
         return $this->hasOne('App\Models\Curso','cod_curso','cod_curso');
      }


   /**
     * Union entre la tabla alumno_curso y la tabla alumno, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function alumnoMany(){
        return $this->hasMany('App\Models\Alumno','dni','dni');
      }
    /**
     * Union entre la tabla alumno_curso y la tabla alumno, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function alumnoHasOne(){
         return $this->hasOne('App\Models\Alumno','dni','dni');
      }
}
