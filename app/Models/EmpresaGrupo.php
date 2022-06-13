<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaGrupo extends Model
{
    use HasFactory;

    protected $fillable = ['id_empresa', 'cod_grupo'];
    protected $table = 'empresa_grupo';
    protected $primaryKey = ['id_empresa,cod_grupo'];
    public $incrementing = false;
    protected $keyType = ['string,string'];

}
