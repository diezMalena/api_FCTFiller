<?php

namespace App\Auxiliar;

use App\Models\Alumno;
use App\Models\AuxCursoAcademico;
use App\Models\CentroEstudios;
use App\Models\Profesor;
use App\Models\RolEmpresa;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Auxiliar
{

    /***********************************************************************/
    #region Model to array - Conversión de variables para rellenar word

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

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Curso académico

    /**
     *  Esta función devuelve el curso academico actual, y si aún no está en la base de datos, devuelve
     *  el último curso.
     *
     *  @author alvaro <alvarosantosmartin6@gmail.com> david <davidsanchezbarragan@gmail.com>
     *  @param $dni es el dni del tutor
     */
    public static function obtenerCursoAcademico()
    {
        $hoy = date("Y-m-d H:i:s");
        $cursoAcademico = AuxCursoAcademico::where([['fecha_inicio', '<', $hoy], ['fecha_fin', '>', $hoy]])
            ->get()->first();

        if ($cursoAcademico) {
            $cursoAcademico = $cursoAcademico->cod_curso;
        } else {
            $cursoAcademico = AuxCursoAcademico::where('id', AuxCursoAcademico::max('id'))->get()->first()->cod_curso;
        }
        return $cursoAcademico;
    }

    /**
     * Obtiene el id del curso académico según el año pasado por parámetro
     *
     * @param int $anio Año del cual se quiere conocer el curso académico
     * @return string Id del curso académico deseado
     * @author David Sánchez Barragán
     */
    public static function obtenerCursoAcademicoPorAnio($anio)
    {
        //Select realizada "a pelo" para utilizar la función YEAR() de MySQL
        return DB::select("select cod_curso from aux_curso_academico where year(fecha_inicio) = '" . $anio . "'")[0]->cod_curso;
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Rutas de autenticación

    /**
     * Obtiene el centro de estudios asociado al profesor, según el DNI
     * @param string $dni DNI del profesor
     * @return string Código del centro de estudios
     */
    public static function obtenerCentroPorDNIProfesor($dni)
    {
        try {
            return CentroEstudios::where('cod', (Profesor::where('dni', $dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * Obtiene todos los datos de un usuario a partir de su tipo de perfil y su email, según qué tipo de usuario sea.
     *
     * @param int $usuario array con los datos del usuario
     * @author alvaro <alvarosantosmartin6@gmail.com>
     */
    public static function getDatosUsuario($usuario_view)
    {
        if ($usuario_view->perfil == 'alumno') {
            $usuario = Alumno::where('email', '=', $usuario_view->email)
                ->select(['email', 'nombre', 'apellidos', 'dni'])
                ->first();
        } else if ($usuario_view->perfil == 'trabajador') {
            $usuario = Trabajador::where('email', '=', $usuario_view->email)
                ->select(['email', 'nombre', 'apellidos', 'dni'])
                ->first();
            $roles = RolTrabajadorAsignado::where('dni', '=', $usuario->dni)
                ->select('id_rol')
                ->get();
            $usuario->roles = $roles;
        } else {
            $usuario = Profesor::where('email', '=', $usuario_view->email)
                ->select(['email', 'nombre', 'apellidos', 'dni'])
                ->first();
            $roles = RolProfesorAsignado::where('dni', '=', $usuario->dni)
                ->select('id_rol')
                ->get();
            $usuario->roles = $roles;
        }
        $usuario->tipo = $usuario_view->perfil;
        return $usuario;
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Estructura de carpetas y archivos

    /**
     * Esta funcion comprueba si una carpeta existe o no, y si no, la crea.
     * @author Laura
     * @param string $ruta la ruta de la carpeta a comprobar
     * @return void
     */
    public static function existeCarpeta($ruta)
    {
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }
    }

    /**
     * Guarda un fichero en base64 en la carpeta indicada
     * @param string $path Ubicación en la que se desea guardar el fichero
     * @param string $nombreFichero Nombre con el que se va a guardar el fichero
     * @param string $fichero Cadena de texto en formato base64 que contiene el fichero
     * @return string Ruta del fichero generado, si se ha guardado sin errores.
     * @return boolean En caso de error, devuelve false.
     * @author David Sánchez Barragán
     */
    public static function guardarFichero($path, $nombreFichero, $fichero)
    {
        if (strlen($path) == 0 || strlen($nombreFichero) == 0 || strlen($fichero) == 0) {
            return false;
        } else {
            try {
                //Se comprueba que existe el directorio $path antes de guardar el fichero
                //Si el directorio no existe, se crea
                self::existeCarpeta($path);

                //Obtenemos la extensión del fichero:
                $extension = explode('.', explode('/', mime_content_type($fichero))[1])[0];
                //Abrimos el flujo de escritura para guardar el fichero
                $flujo = fopen($path . DIRECTORY_SEPARATOR .  $nombreFichero . '.' . $extension, 'wb');

                //Dividimos el string en comas
                // $datos[ 0 ] == "data:type/extension;base64"
                // $datos[ 1 ] == <actual base64 file>
                $datos = explode(',', $fichero);

                if (count($datos) > 1) {
                    fwrite($flujo, base64_decode($datos[1]));
                } else {
                    return false;
                }

                fclose($flujo);

                $devolver = $path . DIRECTORY_SEPARATOR . $nombreFichero . '.' . $extension;

                return $devolver;
            } catch (\Throwable $th) {
                return false;
            }
        }
    }

    /**
     * Borra el fichero según la ruta indicada en $path
     *
     * @param string $path Ruta del fichero a eliminar
     * @author David Sánchez Barragán
     */
    public static function borrarFichero($path)
    {
        try {
            unlink($path);
        } catch (\Throwable $th) {

        }
    }

    /**
     * Devuelve el server de ejecución del PHP
     */
    public static function obtenerURLServidor() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
    }

    #endregion
    /***********************************************************************/
}
