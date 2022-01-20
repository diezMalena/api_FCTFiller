<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoMateria extends Model
{
    use HasFactory;
    protected $fillable=['dni','cod_materia','aprobado','curso_academico'];
    protected $table = 'alumno_materia';
    protected $primaryKey = ['dni,cod_materia'];
    public $incrementing = false;
    protected $keyType = ['string,string'];


    /**
     * Union entre la tabla alumno_materia y la tabla alumno, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function alumnoMany(){
        return $this->hasMany('App\Models\Alumno','dni','dni');
      }
    /**
     * Union entre la tabla alumno_materia y la tabla alumno, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function alumnoHasOne(){
         return $this->hasOne('App\Models\Alumno','dni','dni');
      }


      /**
     * Union entre la tabla alumno_materia y la tabla materia, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function materiaMany(){
        return $this->hasMany('App\Models\Materia','cod_materia','cod_materia');
      }
       /**
     * Union entre la tabla alumno_materia y la tabla materia, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function materiaHasOne(){
         return $this->hasOne('App\Models\Materia','cod_materia','cod_materia');
      }

}
