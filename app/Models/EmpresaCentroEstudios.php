<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaCentroEstudios extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_convenio',
        'cod_centro',
        'cif_empresa',
        'fecha',
        'firmado_director',
        'firmado_empresa',
        'ruta_anexo'
    ];
    protected $table = 'convenio';
    protected $primaryKey = ['cod_convenio'];
    public $incrementing = false;
    protected $keyType = ['string'];

    /**
     * Union entre la tabla empresa_centro y la tabla centro, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroEstudiosMany()
    {
        return $this->hasMany('App\Models\CentroEstudios', 'cod_centro', 'cod_centro');
    }

    /**
     * Union entre la tabla empresa_centro y la tabla centro, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroEstudiosHasOne()
    {
        return $this->hasOne('App\Models\CentroEstudios', 'cod_centro', 'cod_centro');
    }

    /**
     * Union entre la tabla empresa_centro y la tabla empresa, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaMany()
    {
        return $this->hasMany('App\Models\Empresa', 'cif_empresa', 'cif');
    }

    /**
     * Union entre la tabla empresa_centro y la tabla empresa, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function empresaHasOne()
    {
        return $this->hasOne('App\Models\Empresa', 'cif_empresa', 'cif');
    }
}
