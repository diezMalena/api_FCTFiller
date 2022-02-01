<?php

namespace App\Auxiliar;

use Illuminate\Database\Eloquent\Model;

class Auxiliar
{

    /**
     * Transforma un modelo en un vector asociativo y añade el prefijo a los índices
     * Está pensado para automatizar el proceso de relleno de documentos Word (.docx),
     * adaptando las variables a los nombres de las mismas en el documento Word.
     * @param Model $modelo el modelo que se quiere convertir en array
     * @param string $prefijoClave el prefijo que se quiere añadir antes de cada índice
     * @return array array asociativo con los índices modificados
     *
     * @author @DaniJCoello
     */
    public static function modelToArray(Model $modelo, string $prefijoClave)
    {
        $array = [];
        foreach ($modelo->toArray() as $key => $value) {
            $array[$prefijoClave . '.' . $key] = $value;
        }
        return $array;
    }

    /**
     * Transforma unos modelos en un solo vector asociativo y añade los prefijos correspondientes
     * Está pensado para automatizar el proceso de relleno de documentos Word (.docx),
     * adaptando las variables a los nombres de las mismas en el documento Word
     * @param array $modelos array de modelos a transformar
     * @param array $prefijos array de los prefijos que se quieren usar, en el mismo orden que los modelos
     * @return array array asociativo con los índices modificados que contiene los datos de todos los modelos
     * @see function Auxiliar::modelToArray()
     * @see function Auxiliar::modelsToSuperArray()
     *
     * @author @DaniJCoello
     */
    public static function modelsToArray(array $modelos, array $prefijos)
    {
        $superArray = [];
        foreach ($modelos as $key => $modelo) {
            self::modelToSuperArray($modelo, $prefijos[$key], $superArray);
        }
        return $superArray;
    }

    /**
     * Añade al array del método modelsToArray un modelo y monta el índice con el prefijo.
     * Función auxiliar para hacer funcionar modelsToArray
     * @param Model $modelo modelo que se quiere convertir en array
     * @param string $prefijoClave el prefijo que se quiere añadir a la key del vector
     * @param array &$superArray el array que contiene todos los datos
     * @see function Auxiliar::modelToArray()
     * @see function Auxiliar::modelsToArray()
     *
     * @author @DaniJCoello
     */
    private static function modelToSuperArray(Model $modelo, string $prefijoClave, array &$superArray)
    {
        foreach ($modelo->toArray() as $key => $value) {
            if ($value != null) {
                $superArray[$prefijoClave . '.' . $key] = $value;
            }
        }
    }
}
