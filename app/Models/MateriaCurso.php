<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaCurso extends Model
{
    use HasFactory;
    protected $fillable=['cod_curso','cod_materia'];
    protected $table = 'materia_curso';
    protected $primaryKey = ['cod_curso','cod_materia'];
    public $incrementing = false;
    protected $keyType = ['string','string'];



    /**
     * Union entre la tabla materia_curso y la tabla materia, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function materiaMany(){
        return $this->hasMany('App\Models\Materia','cod_materia','cod_materia');
      }
    /**
     * Union entre la tabla materia_curso y la tabla materia, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function materiaHasOne(){
         return $this->hasOne('App\Models\Materia','cod_materia','cod_materia');
      }



     /**
     * Union entre la tabla materia_curso y la tabla curso, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function cursoMateria(){
        return $this->hasMany('App\Models\Curso','cod_curso','cod_curso');
      }
     /**
     * Union entre la tabla materia_curso y la tabla curso, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function cursoHasOne(){
         return $this->hasOne('App\Models\Curso','cod_curso','cod_curso');
      }
}
