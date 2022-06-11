<?php

namespace App\Auxiliar;

class Parametros
{

    /***********************************************************************/
    #region Roles

    //----Roles de empresa
    public const REPRESENTANTE_LEGAL = 1;
    public const RESPONSABLE_CENTRO = 2;
    public const TUTOR_EMPRESA = 3;

    //----Roles de docente
    public const DIRECTOR = 1;
    public const JEFE_ESTUDIOS = 2;
    public const TUTOR = 3;
    public const PROFESOR = 4;

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Otras constantes globales
    public const MESES = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    public const NUEVA_LINEA = '\r\n';

    public const COEFICIENTE_KM_VEHICULO_PRIVADO = 0.12;


    #endregion
    /***********************************************************************/
}
