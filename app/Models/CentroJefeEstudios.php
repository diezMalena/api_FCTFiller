<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroJefeEstudios extends Model
{
    use HasFactory;
    protected $fillable=['dni','cod_centro'];
    protected $table = 'centro_jefe_estudios';
    protected $primaryKey = ['dni,cod_centro'];
    public $incrementing = false;
    protected $keyType = ['string,string'];



/**
     * Union entre la tabla centro_jefe_estudios y la tabla centro_estudios, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function JefeEstudiosCentroMany(){
        return $this->hasMany('App\Models\CentroEstudios','cod_centro','cod_centro');
      }
    /**
     * Union entre la tabla centro_jefe_estudios y la tabla centro_estudios, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function JefeEstudiosCentroHasOne(){
         return $this->hasOne('App\Models\CentroEstudios','cod_centro','cod_centro');
      }



      /**
     * Union entre la tabla centro_jefe_estudios y la tabla profesor, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function centroCicloMany(){
        return $this->hasMany('App\Models\Profesores','dni','dni');
      }
    /**
     * Union entre la tabla centro_jefe_estudios y la tabla profesor, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroCicloHasOne(){
         return $this->hasOne('App\Models\Profesores','dni','dni');
      }
}
