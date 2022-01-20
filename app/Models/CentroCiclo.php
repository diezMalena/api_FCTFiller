<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCiclo extends Model
{
    use HasFactory;
    protected $fillable=['cod_centro','cod_ciclo'];
    protected $table = 'centro_ciclo';
    protected $primaryKey =['cod_centro','cod_ciclo'];
    public $incrementing = false;
    protected $keyType =['string','string'];




     /**
     * Union entre la tabla centro_ciclo y la tabla ciclo, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function cicloMany(){
        return $this->hasMany('App\Models\Ciclo','cod_ciclo','cod_ciclo');
      }
    /**
     * Union entre la tabla centro_ciclo y la tabla ciclo, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function cicloHasOne(){
         return $this->hasOne('App\Models\Ciclo','cod_ciclo','cod_ciclo');
      }



     /**
     * Union entre la tabla centro_ciclo y la tabla centro, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
      public function centroMany(){
        return $this->hasMany('App\Models\CentroEstudios','cod_centro','cod_centro');
      }
     /**
     * Union entre la tabla centro_ciclo y la tabla centro, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function centroHasOne(){
         return $this->hasOne('App\Models\CentroEstudios','cod_centro','cod_centro');
      }

}
