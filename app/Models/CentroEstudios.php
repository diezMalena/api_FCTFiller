<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroEstudios extends Model
{
    use HasFactory;
    protected $fillable=['nombre','cod_centro','ciudad','direccion','provincia','cod_postal','cif','telefono','email','dni_director'];
    protected $table = 'centro_estudios';
    protected $primaryKey = 'cod_centro';
    public $incrementing = false;
    protected $keyType = 'string';


    /**
     * Union entre la tabla centro_estudios y la tabla centro_ciclo, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroCicloMany(){
        return $this->hasMany('App\Models\CentroCiclo','cod_centro','cod_centro');
      }
    /**
     * Union entre la tabla centro_estudios y la tabla centro_ciclo, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroCicloHasOne(){
         return $this->hasOne('App\Models\CentroCiclo','cod_centro','cod_centro');
      }



    /**
     * Union entre la tabla centro_estudios y la tabla empresa_centro, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function centroEmpresaMany(){
        return $this->hasMany('App\Models\EmpresaCentroEstudios','cod_centro','cod_centro');
      }
    /**
     * Union entre la tabla centro_estudios y la tabla empresa_centro, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroEmpresaHasOne(){
         return $this->hasOne('App\Models\EmpresaCentroEstudios','cod_centro','cod_centro');
      }



   /**
     * Union entre la tabla centro_estudios y la tabla profesor, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroProfesorMany(){
        return $this->hasMany('App\Models\Profesor','dni_director','dni');
      }
    /**
     * Union entre la tabla centro_estudios y la tabla profesor, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroProfesorHasOne(){
         return $this->hasOne('App\Models\EmpresaCentroEstudios','dni_director','dni');
      }



  /**
     * Union entre la tabla centro_estudios y la tabla centro_jefe_estudios, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroJefeEstudiosMany(){
        return $this->hasMany('App\Models\CentroJefeEstudios','cod_centro','cod_centro');
      }
    /**
     * Union entre la tabla centro_estudios y la tabla centro_jefe_estudios, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroJefeEstudiosHasOne(){
         return $this->hasOne('App\Models\CentroJefeEstudios','cod_centro','cod_centro');
      }



}
