<?php

namespace App\Auxiliar;

use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\AuxCursoAcademico;
use App\Models\GrupoFamilia;
use App\Models\CentroEstudios;
use App\Models\Profesor;
use App\Models\Matricula;
use App\Models\Tutoria;
use App\Models\RolEmpresa;
use App\Models\Fct;
use App\Models\RolProfesorAsignado;
use App\Models\RolTrabajadorAsignado;
use App\Models\Trabajador;
use App\Models\User;
use Exception;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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
     * @param User $user Modelo con los datos básicos del usuario
     * @return Model $usuario Modelo con los datos del usuario, según su perfil
     * @author alvaro <alvarosantosmartin6@gmail.com>
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function getDatosUsuario($user)
    {
        if ($user->tipo == 'alumno') {
            $usuario = Alumno::where('email', '=', $user->email)
                ->select(['email', 'nombre', 'apellidos', 'dni'])
                ->first();
        } else if ($user->tipo == 'trabajador') {
            $usuario = Trabajador::where('email', '=', $user->email)
                ->select(['email', 'nombre', 'apellidos', 'dni'])
                ->first();
            $roles = RolTrabajadorAsignado::where('dni', '=', $usuario->dni)
                ->select('id_rol')
                ->get();
            $usuario->roles = $roles;
        } else {
            $usuario = Profesor::where('email', '=', $user->email)
                ->select(['email', 'nombre', 'apellidos', 'dni', 'cod_centro_estudios'])
                ->first();
            $roles = RolProfesorAsignado::where('dni', '=', $usuario->dni)
                ->select('id_rol')
                ->get();
            $usuario->roles = $roles;
            //DJC Cambio 28-05-2022: añadido objeto de centro de estudios al profesor. Lo siento por la ñapa
            $usuario->centro = CentroEstudios::find($usuario->cod_centro_estudios);
            $controller = new ControladorTutorFCT();
            $usuario->centro->director = $controller->getDirectorCentroEstudios($usuario->cod_centro_estudios);
        }
        $usuario->tipo = $user->tipo;
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
     * Borra el fichero según la ruta indicada en el parámetro $path
     *
     * @param string $path Ruta del fichero a eliminar
     * @return boolean Devuelve true en caso de haber eliminado correctamente el
     * fichero indicado en la ruta. Devuelve false si no se ha podido eliminar o ha ocurrido
     * algún error (la ruta era incorrecta, el fichero no existía,...)
     * @author David Sánchez Barragán
     */
    public static function borrarFichero($path)
    {
        try {
            if (file_exists($path)) {
                unlink($path);
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Comprime el directorio indicado en un fichero ZIP
     * @param string $rutaAComprimir Carpeta o directorio a comprimir
     * @param string $nombreFichero Nombre con el que se guardará el fichero comprimido
     * @return
     */
    public static function comprimirDirectorio($rutaAComprimir, $nombreFichero) {

    }

    /**
     * Devuelve la URL del server de ejecución actual de PHP
     * @author David Sánchez Barragán
     */
    public static function obtenerURLServidor()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD Users - Métodos auxiliares

    /**
     * Añade un usuario a la tabla de usuarios (users)
     * a partir de un modelo de profesor, alumno o trabajador
     * @param Model $model Modelo de tipo `Profesor`, `Trabajador` o `Alumno`
     * @param string $perfil Perfil del usuario (profesor, trabajador o alumno)
     * @return int Código web de respuesta (200-500)
     *
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function addUser(Model $model, string $perfil)
    {
        try {
            $name = $model->nombre . ' ' . $model->apellidos;
            User::create([
                'name' => $name,
                'email' => $model->email,
                'password' => $model->password,
                'tipo' => $perfil
            ]);
            return 201; // Created
        } catch (QueryException $ex) {
            if ($ex->errorInfo[1] == 1062) {
                return 409; // Conflicto (Registro ya creado)
            } else {
                return 400; // Error
            }
        } catch (Exception $ex) {
            return 500; // Error del servidor
        }
    }

    /**
     * Obtiene un usuario de la tabla de usuarios (users)
     * @param string $email e-mail del usuario
     * @return User Modelo con los datos del usuario
     * @return int Código web de respuesta correspondiente
     *
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function getUser(string $email)
    {
        try {
            if ($user = User::where('email', $email)->first()) {
                return $user;
            } else {
                return 204; // No Content
            }
        } catch (Exception $ex) {
            return 500; // Internal Server Error
        }
    }

    /**
     * Actualiza los datos de un usuario en la tabla de usuarios (users)
     * @param Model $model Modelo de tipo `Profesor`, `Trabajador` o `Alumno`
     * @param string $email e-mail del usuario
     * @return int Código web de respuesta (200-500)
     *
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function updateUser(Model $model, string $email)
    {
        try {
            $update = User::where('email', $email)->update([
                'email' => $model->email,
                'password' => $model->password,
                'name' => $model->nombre . ' ' . $model->apellidos,
            ]);
            if ($update > 0) {
                return 200; // OK
            } else if ($update == 0) {
                $get = self::getUser($email);
                if (gettype($get) == "integer") {
                    return $get;
                } else {
                    return 304; // Not Modified
                }
            }
        } catch (Exception $ex) {
            return 500; // Internal server error
        }
    }

    /**
     * Elimina un usuario de la tabla users
     * @param string $email e-mail del usuario a eliminar
     * @return int Código de respuesta web correspondiente (200-500)
     *
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function deleteUser(string $email)
    {
        try {
            $delete = User::where('email', $email)->delete();
            if ($delete > 0) {
                return 200; // OK
            } else if ($delete == 0) {
                return 204; // Not Found
            }
        } catch (Exception $ex) {
            return 500; // Internal Server Error
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Obtención de roles

    /**
     * Devuelve los roles de un profesor a partir de su email
     *
     * @param string $email email del usuario
     * @return array Array de roles del profesor
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function getRolesProfesorFromEmail(string $email)
    {
        return RolProfesorAsignado::where('dni', Profesor::where('email', $email)->first()->dni)
            ->pluck('id_rol')->toArray();
    }

    /**
     * Devuelve los roles de un trabajador a partir de su email
     *
     * @param string $email email del usuario
     * @return array Array de roles del trabajador
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public static function getRolesTrabajadorFromEmail(string $email)
    {
        return RolTrabajadorAsignado::where('dni', Trabajador::where('email', $email)->first()->dni)
            ->pluck('id_rol')->toArray();
    }

    #endregion
    /***********************************************************************/

    public static function templateProcessorAndSetValues($rutaOrigen, $rutaDestino, $datos)
    {
        $template = new TemplateProcessor($rutaOrigen);
        $template->setValues($datos);
        $template->saveAs($rutaDestino);
    }
}
