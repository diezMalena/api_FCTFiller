<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo auxiliar para generar el número autoincremental del convenio
 * @author Dani J. Coello <daniel.jimenezcoello@gmail.com @DaniJCoello
 */
class AuxConvenio extends Model
{
    use HasFactory;

    protected $table = 'aux_convenio';
    public $timestamps = false;
}
