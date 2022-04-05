<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaGrupo extends Model
{
    use HasFactory;

    protected $fillable = ['cod_centro', 'cod_grupo'];
    protected $table = 'oferta_grupo';
    protected $primaryKey = ['cod_centro', 'cod_grupo'];
    public $incrementing = false;
    protected $keyType = ['string', 'string'];
}
