<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolProfesorAsignado extends Model
{
    use HasFactory;
    protected $fillable=['dni','id_rol'];
    protected $table = 'rol_profesor_asignado';
    protected $primaryKey = ['dni,id_rol'];
    public $incrementing = false;
    protected $keyType = ['string,string'];




     /**
     * Union entre la tabla rol_profesor_asignado y la tabla profesor intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolProfesorMany(){
        return $this->hasMany('App\Models\Profesor','dni','dni');
      }
    /**
     * Union entre la tabla rol_profesor_asignado y la tabla profesor, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function rolProfesorHasOne(){
         return $this->hasOne('App\Models\Profesor','dni','dni');
      }



    /**
     * Union entre la tabla rol_profesor_asignado y la tabla roles_estudio intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function rolEstudioMany(){
        return $this->hasMany('App\Models\RolesEstudio','id_rol','id');
      }

    /**
     * Union entre la tabla rol_profesor_asignado y la tabla roles_estudio, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function rolEstudioHasOne(){
         return $this->hasOne('App\Models\RolesEstudio','id_rol','id');
      }
}
