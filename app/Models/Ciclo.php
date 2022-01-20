<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    use HasFactory;
    protected $fillable=['nombre','cod_ciclo'];
    protected $table = 'ciclo';
    protected $primaryKey = 'cod_ciclo';
    public $incrementing = false;
    protected $keyType = 'string';


     /**
     * Union entre la tabla ciclo y la tabla centro_ciclo, intercambiando
     * múltiple información
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
    public function cicloCentroMany(){
        return $this->hasMany('App\Models\CentroCiclo','cod_ciclo','cod_ciclo');
      }
    /**
     * Union entre la tabla ciclo y la tabla centro_ciclo, intercambiando
     * información concreta
     *@author laura <lauramorenoramos97@gmail.com>
     * @return void
     */
     public function cicloCentroHasOne(){
         return $this->hasOne('App\Models\CentroCiclo','cod_ciclo','cod_ciclo');
      }
}
