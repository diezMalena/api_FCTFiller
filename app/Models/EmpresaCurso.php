<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaCurso extends Model
{
    use HasFactory;
    protected $fillable = ['id_empresa', 'cod_curso'];
    protected $table = 'empresa_curso';
    protected $primaryKey = ['id_empresa,cod_curso'];
    public $incrementing = false;
    protected $keyType = ['string,string'];

    public function empresasMany()
    {
        return $this->hasOne('App\Models\Empresa', 'id_empresa', 'id');
    }

    public function cursosMany()
    {
        return $this->hasOne('App\Models\Curso', 'cod_curso', 'cod_curso');
    }
}
