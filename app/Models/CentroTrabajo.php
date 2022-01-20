<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroTrabajo extends Model
{
    use HasFactory;
    protected $fillable=['cif_empresa','nombre','direccion','provincia','telefono','email','localidad'];
    protected $table = 'centro_trabajo';
    protected $primaryKey = ['cif_empresa','nombre'];
    public $incrementing = false;
    protected $keyType = 'string';




    /**
     * Union entre la tabla centro_trabajo y la tabla empresa, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroEmpresaMany(){
        return $this->hasMany('App\Models\Empresa','cif_empresa','cif');
     }
     /**
     * Union entre la tabla centro_trabajo y la tabla empresa, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroEmpresaHasOne(){
        return $this->hasOne('App\Models\Empresa','cif_empresa','cif');
     }



    /**
     * Union entre la tabla centro_trabajo y la tabla trabajador, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroTrabajadorMany(){
        return $this->hasMany('App\Models\Trabajador','nombre','nombre_centro');
     }
    /**
     * Union entre la tabla centro_trabajo y la tabla trabajador, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroTrabajadorHasOne(){
        return $this->hasOne('App\Models\Trabajador','nombre','nombre_centro');
     }

}
