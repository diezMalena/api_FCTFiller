<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolTrabajadorAsignado extends Model
{
    use HasFactory;
    protected $fillable = ['dni', 'id_rol'];
    protected $table = 'rol_trabajador_asignado';
    protected $primaryKey = ['dni', 'id_rol'];
    public $incrementing = false;
    protected $keyType = ['string', 'unsignedBigInteger'];



    /**
     * Union entre la tabla rol_trabajador_asignado y la tabla trabajador intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolTrabajadorMany()
    {
        return $this->hasMany('App\Models\Trabajador', 'dni', 'dni');
    }
    /**
     * Union entre la tabla rol_trabajador_asignado y la tabla trabajador, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolTrabajadorHasOne()
    {
        return $this->hasOne('App\Models\Trabajador', 'dni', 'dni');
    }




    /**
     * Union entre la tabla rol_trabajador_asignado y la tabla roles_empresa intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolEmpresaMany()
    {
        return $this->hasMany('App\Models\RolesEmpresa', 'id_rol', 'id');
    }

    /**
     * Union entre la tabla rol_trabajador_asignado y la tabla roles_empresa, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function rolEmpresaHasOne()
    {
        return $this->hasOne('App\Models\RolesEmpresa', 'id_rol', 'id');
    }
}
