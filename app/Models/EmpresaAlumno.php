<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaAlumno extends Model
{
    use HasFactory;
    protected $fillable = ['id_empresa', 'dni_alumno', 'curso_academico','horario','num_horas','fecha_ini','fecha_fin',];
    protected $table = 'empresa_alumno';
    protected $primaryKey = ['id_empresa,dni_alumno,curso_academico'];
    public $incrementing = false;
    protected $keyType = ['string,string,string'];

    public function alumnoOne()
    {
        return $this->hasOne('App\Models\Alumno', 'dni_alumno', 'dni');
    }

    public function empresasMany()
    {
        return $this->hasOne('App\Models\Empresa', 'id_empresa', 'id');
    }
}
