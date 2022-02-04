<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo auxiliar para generar el número autoincremental del convenio
 */
class AuxConvenio extends Model
{
    use HasFactory;

    protected $table = 'aux_convenio';
    public $timestamps = false;
}
