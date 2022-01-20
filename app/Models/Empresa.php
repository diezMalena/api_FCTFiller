<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable=['cif','nombre','telefono','email','direccion','provincia','localidad'];
    protected $table = 'empresa';
    protected $primaryKey = 'cif';
    public $incrementing = false;
    protected $keyType = 'string';




    /**
     * Union entre la tabla empresa y la tabla alumno, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaAlumnoMany(){
       return $this->hasMany('App\Models\Alumno','cif','cif');
     }
      /**
     * Union entre la tabla empresa y la tabla alumno, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaAlumnoHasOne(){
        return $this->hasOne('App\Models\Alumno','cif','cif');
     }



    /**
     * Union entre la tabla empresa y la tabla empresa_centro, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaCentroMany(){
        return $this->hasMany('App\Models\EmpresaCentroEstudios','cif','cif_empresa');
     }
      /**
     * Union entre la tabla empresa y la tabla empresa_centro, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function empresaCentroHasOne(){
        return $this->hasOne('App\Models\EmpresaCentroEstudios','cif','cif_empresa');
     }




     /**
     * Union entre la tabla empresa y la tabla centro_trabajo, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaCentroTrabajoMany(){
        return $this->hasMany('App\Models\CentroTrabajo','cif','cif_empresa');
     }
     /**
     * Union entre la tabla empresa y la tabla centro_trabajo, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function empresaCentroTrabajoHasOne(){
        return $this->hasOne('App\Models\CentroTrabajo','cif','cif_empresa');
     }




    /**
     * Union entre la tabla empresa y la tabla trabajador, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function empresaTrabajadorMany(){
        return $this->hasMany('App\Models\Trabajador','cif','cif_empresa');
     }
     /**
     * Union entre la tabla empresa y la tabla trabajador, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function empresaTrabajadorHasOne(){
        return $this->hasOne('App\Models\Trabajador','cif','cif_empresa');
     }

}
