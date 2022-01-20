<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesEmpresa extends Model
{
    use HasFactory;
    protected $fillable=['descripcion'];
    protected $table = 'id';





    /**
     * Union entre la tabla roles_empresa y la tabla rol_profesor_asignado intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolTrabajadorAsignadoMany(){
        return $this->hasMany('App\Models\RolTrabajadorAsignado','id','id_rol');
    }
    /**
     * Union entre la tabla roles_empresa y la tabla rol_profesor_asignado, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolTrabajadorAsignadoHasOne(){
        return $this->hasOne('App\Models\RolTrabajadorAsignado','id','id_rol');
    }
}
