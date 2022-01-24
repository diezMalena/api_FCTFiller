<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    use HasFactory;
    protected $fillable = [
        'dni',
        'email',
        'password',
        'nombre',
        'apellidos',
        'cif_empresa',
        // 'nombre_centro_trabajo'
    ];
    protected $table = 'trabajador';
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Union entre la tabla trabajador y la tabla empresa, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function trabajadorEmpresaMany()
    {
        return $this->hasMany('App\Models\Empresa', 'cif_empresa', 'cif');
    }

    /**
     * Union entre la tabla trabajador y la tabla empresa, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function trabajadorEmpresaHasOne()
    {
        return $this->hasOne('App\Models\Empresa', 'cif_empresa', 'cif');
    }

    /**
     * Union entre la tabla trabajador y la tabla rol_trabajador_asignado, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function trabajadorRolMany()
    {
        return $this->hasMany('App\Models\RolTrabajadorAsignado', 'dni', 'dni');
    }

    /**
     * Union entre la tabla trabajador y la tabla rol_trabajador_asignado, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function trabajadorRolHasMany()
    {
        return $this->hasOne('App\Models\RolTrabajadorAsignado', 'dni', 'dni');
    }
}
