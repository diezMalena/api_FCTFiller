<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Auxiliar;
use App\Auxiliar\Parametros;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\RolProfesorAsignado;
use App\Models\Alumno;
use App\Models\Fct;
use App\Models\AuxCursoAcademico;
use App\Models\CentroEstudios;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\OfertaGrupo;
use App\Models\Profesor;
use App\Models\Tutoria;
use App\Models\Cuestionario;
use App\Models\CuestionarioRespondido;
use App\Models\PreguntasCuestionario;
use App\Models\PreguntasRespondidas;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class ControladorJefatura extends Controller
{

    /***********************************************************************/
    #region Declaración de constantes
    const CABECERA_ALUMNOS = ["ALUMNO", "APELLIDOS", "NOMBRE", "SEXO", "DNI", "NIE", "FECHA_NACIMIENTO", "LOCALIDAD_NACIMIENTO", "PROVINCIA_NACIMIENTO", "NOMBRE_CORRESPONDENCIA", "DOMICILIO", "LOCALIDAD", "PROVINCIA", "TELEFONO", "MOVIL", "CODIGO_POSTAL", "TUTOR1", "DNI_TUTOR1", "TUTOR2", "DNI_TUTOR2", "PAIS", "NACIONALIDAD", "EMAIL_ALUMNO", "EMAIL_TUTOR2", "EMAIL_TUTOR1", "TELEFONOTUTOR1", "TELEFONOTUTOR2", "MOVILTUTOR1", "MOVILTUTOR2", "APELLIDO1", "APELLIDO2", "TIPODOM", "NTUTOR1", "NTUTOR2", "NSS"];
    const CABECERA_MATRICULAS = ["ALUMNO", "APELLIDOS", "NOMBRE", "MATRICULA", "ETAPA", "ANNO", "TIPO", "ESTUDIOS", "GRUPO", "REPETIDOR", "FECHAMATRICULA", "CENTRO", "PROCEDENCIA", "ESTADOMATRICULA", "FECHARESMATRICULA", "NUM_EXP_CENTRO", "PROGRAMA_2"];
    const CABECERA_PROFESORES = ["CODIGO", "APELLIDOS", "NOMBRE", "NRP", "DNI", "ABREVIATURA", "FECHA_NACIMIENTO", "SEXO", "TITULO", "DOMICILIO", "LOCALIDAD", "CODIGO_POSTAL", "PROVINCIA", "TELEFONO_RP", "ESPECIALIDAD", "CUERPO", "DEPARTAMENTO", "FECHA_ALTA_CENTRO", "FECHA_BAJA_CENTRO", "EMAIL", "TELEFONO"];
    const CABECERA_UNIDADES = ["ANNO", "GRUPO", "ESTUDIO", "CURSO", "TUTOR"];
    const ORDEN_PROCESAMIENTO = ['profesores', 'alumnos', 'unidades', 'matriculas'];
    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region Subida masiva de datos - Procesamiento de CSV

    /**
     * Función que recibe el fichero CSV y lo guarda en la ruta temporal
     * para su posterior procesamiento
     *
     * Modificación DSB 15 de Marzo de 2022:
     *   - Añadidos el orden de procesamiento: Profesores, Alumnos, Unidades, Matrículas.
     *   - Si el directorio tmp/csv no existe, se crea
     *   - Añadido el DNI de la persona logueada en el sistema
     * @param Request $r La request debe incluir como mínimo, el fichero en formato cadena Base64,
     * el nombre del fichero y el nombre de la caja en la que el usuario arrastra el fichero
     * @return Response Devuelve un error en caso de que el CSV esté mal formado u ocurra algún problema
     * al guardar el fichero
     * @author David Sánchez Barragán
     */
    public function recibirCSV(Request $r)
    {
        $documentosPeticion = [];
        $errores = [];

        //Metemos en al array $documentosPeticion los datos de la petición
        //para tenerlos indexados
        foreach ($r->ficheros as $item) {
            $documentosPeticion[strtolower($item['box_file'])] = $item;
        }

        #region Bucle procesamiento

        foreach (self::ORDEN_PROCESAMIENTO as $nombreCaja) {
            $nombreFichero = '';
            $fichero = '';
            try {
                $nombreFichero = $documentosPeticion[$nombreCaja]['file_name'];
                $fichero = $documentosPeticion[$nombreCaja]['file_content'];
            } catch (Exception $th) {
                //Si el fichero no se encuentra en el array, lanzará una excepción, que recogemos
                //e indicamos que se prosiga con la ejecución del bucle, saltándose la actual
                continue;
            }

            //Si se guarda el fichero satisfactoriamente, se comprueba
            //si el fichero es íntegro
            if ($this->guardarFichero($nombreCaja, $fichero)) {
                $resultado = $this->comprobarFichero($nombreCaja, $nombreFichero);
                //Si el resultado es distinto de cero, el fichero no está bien
                //por lo tanto, se mete el resultado en errores
                if ($resultado != 0) {
                    $errores[$nombreCaja] = $resultado;
                } else {
                    //Si es cero, el fichero está bien y pasamos a insertar los registros en
                    //base de datos
                    $resultado = $this->procesarFicheroABBDD($nombreCaja, $r->dni);

                    //Si el resultado es distinto de cero, el fichero ha tenido errores al insertarse
                    //por lo tanto, se mete el resultado en errores
                    if ($resultado != 0) {
                        $errores[$nombreCaja] = $resultado;
                    }
                }

                //Borramos el fichero al final
                Auxiliar::borrarFichero($this->getCSVPathFile($nombreCaja));
            } else {
                $errores[$nombreCaja] = 'No se pudo guardar el fichero en el servidor';
            }
        }
        #endregion

        if (count($errores) > 0) {
            $mensaje = "Los siguientes ficheros han tenido errores:" . Parametros::NUEVA_LINEA;

            foreach ($errores as $key => $variable) {
                $mensaje .= " " . $key . ".csv: " . ($variable) . Parametros::NUEVA_LINEA;
            }

            return response()->json(['mensaje' => $mensaje], 200);
        } else {
            return response()->json(['mensaje' => 'Todos los ficheros se han procesado correctamente'], 200);
        }
    }

    /**
     * Función intermedia que guarda los ficheros CSV en la base de datos
     * @param string $nombreCaja Nombre de la caja
     * @return Response Respuesta de la petición formateada en JSON con el
     * parámetro mensaje, que indicará el resultado de la llamada a esta función
     * @author David Sánchez Barragán
     */
    public function procesarFicheroABBDD($nombreCaja, $DNILogueado)
    {

        $resultado = false;
        switch ($nombreCaja) {

            case 'alumnos':
                $resultado = $this->procesarFicheroABBDDAlumnos($nombreCaja);
                break;
            case 'matriculas':
                $resultado =  $this->procesarFicheroABBDDMatriculas($nombreCaja, $DNILogueado);
                break;
            case 'profesores':
                $resultado =  $this->procesarFicheroABBDDProfesores($nombreCaja, $DNILogueado);
                break;
            case 'unidades':
                $resultado =  $this->procesarFicheroABBDDUnidades($nombreCaja, $DNILogueado);
                break;
            default:
                $resultado =  'Error';
                break;
        }

        return $resultado;
    }

    /**
     * Obtiene la ruta donde se alojan los ficheros temporales CSV
     * @return string Ruta de la carpeta donde se alojan los ficheros CSV
     * @author David Sánchez Barragán
     */
    private function getCSVPath()
    {
        return public_path() . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR;
    }

    /**
     * Obtiene la ruta junto con el nombre del fichero de los ficheros temporales CSV
     * @param string Nombre del fichero CSV
     * @return string Ruta de la carpeta donde se alojan los ficheros CSV
     * @author David Sánchez Barragán
     *
     */
    private function getCSVPathFile($nombreCaja)
    {
        return $this->getCSVPath() . $nombreCaja . ".csv";
    }

    /**
     * Guarda los ficheros recibidos por la petición en formato base64
     * @param string $nombreCaja
     * @param string $fichero
     * @return boolean True en caso de éxito, false en caso de error
     * @author David Sánchez Barragán
     */
    private function guardarFichero($nombreCaja, $fichero)
    {
        try {
            //Modificación DSB 15-3-2022: Comprobar que existe el directorio tmp/csv antes de guardar el fichero
            //Si el directorio no existe, se crea
            Auxiliar::existeCarpeta($this->getCSVPath());
            //Abrimos el flujo de escritura para guardar el fichero
            $flujo = fopen($this->getCSVPathFile($nombreCaja), 'wb');

            //Dividimos el string en comas
            // $datos[ 0 ] == "data:type/extension;base64"
            // $datos[ 1 ] == <actual base64 string>
            $datos = explode(',', $fichero);

            if (count($datos) > 1) {
                fwrite($flujo, base64_decode($datos[1]));
            } else {
                return false;
            }

            fclose($flujo);

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }



    /**
     * Método que procesa el fichero de Alumnos.csv e inserta su contenido en BBDD (tabla Alumno)
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDAlumnos($nombreCaja)
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));

                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        try {
                            //Se recoge el DNI, si está vacío, se recoge el NIE
                            $dni = trim($vec[array_search('DNI', self::CABECERA_ALUMNOS)] != '' ?  $vec[array_search('DNI', self::CABECERA_ALUMNOS)] : $vec[array_search('NIE', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\"");
                            $alu = Alumno::create([
                                'dni' => $dni,
                                'cod_alumno' => trim($vec[array_search('ALUMNO', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                //Para mantener la integridad de la base de datos, si el correo
                                //está vacío, se genera uno con el DNI
                                'email' => trim($vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] != '' ? $vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] : $dni . '@fctfiller.com', " \t\n\r\0\x0B\""),
                                //Se debería crear una contraseña por defecto para todos los usuarios dados de alta automáticamente
                                'password' => Hash::make('superman'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'provincia' => trim($vec[array_search('PROVINCIA', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'localidad' => trim($vec[array_search('LOCALIDAD', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'va_a_fct' => '0',
                                'fecha_nacimiento' => trim($vec[array_search('FECHA_NACIMIENTO', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'domicilio' => trim($vec[array_search('DOMICILIO', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'telefono' => trim($vec[array_search('TELEFONO', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'movil' => trim($vec[array_search('MOVIL', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\"")
                            ]);
                            Auxiliar::addUser($alu, 'alumno');
                        } catch (Exception $th) {
                            if (str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores . 'Error en línea' . $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                        }
                    }
                }
                $numLinea++;
            }
            fclose($file);
        } else {
            return 'Error al procesar el fichero.';
        }

        if ($errores != '') {
            return $errores;
        } else {
            return 0;
        }
    }

    /**
     * Método que procesa el fichero de Matriculas.csv e inserta su contenido en BBDD (tabla ??)
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los profesores de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDMatriculas($nombreCaja, $DNILogueado)
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));
                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        try {
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            $dniAlumno = Alumno::where('cod_alumno', trim($vec[array_search('ALUMNO', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\""))->get()[0]->dni;

                            $codNivel = explode(' ', trim($vec[array_search('ESTUDIOS', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\""))[2];
                            $nombreCiclo = trim(strtolower(explode('-', $vec[array_search('ESTUDIOS', self::CABECERA_MATRICULAS)])[1]));

                            $codGrupo = Grupo::where([
                                ['cod_nivel', $codNivel],
                                ['nombre_ciclo', $nombreCiclo]
                            ])->get()[0]->cod;

                            $anio = trim($vec[array_search('ANNO', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\"");

                            $matricula = trim($vec[array_search('MATRICULA', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\"");

                            $cursoAcademico = Auxiliar::obtenerCursoAcademicoPorAnio($anio);

                            Matricula::create([
                                'cod' => $matricula,
                                'cod_centro' => $codCentroEstudios,
                                'dni_alumno' => $dniAlumno,
                                'cod_grupo' => $codGrupo,
                                'curso_academico' => $cursoAcademico
                            ]);
                        } catch (Exception $th) {
                            if (str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores . 'Error en línea' . $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                        }
                    }
                }
                $numLinea++;
            }
            fclose($file);
        } else {
            return 'Error al procesar el fichero.';
        }

        if ($errores != '') {
            return $errores;
        } else {
            return 0;
        }
    }

    /**
     * Método que procesa el fichero de Profesores.csv e inserta su contenido en BBDD (tabla Profesor)
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los profesores de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDProfesores($nombreCaja, $DNILogueado)
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));
                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        try {
                            $dni = trim($vec[array_search('DNI', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\"");

                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;

                            $profe = Profesor::create([
                                'dni' => $dni,
                                'email' => trim($vec[array_search('EMAIL', self::CABECERA_PROFESORES)] != '' ? $vec[array_search('EMAIL', self::CABECERA_PROFESORES)] : $dni . '@fctfiller.com', " \t\n\r\0\x0B\""),
                                'password' => Hash::make('superman'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\""),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\""),
                                'cod_centro_estudios' => $codCentroEstudios
                            ]);
                            Auxiliar::addUser($profe, "profesor");

                            //DSB Cambio 16-04-2022: se añade el rol profesor a todos los profesores creados
                            RolProfesorAsignado::create([
                                'dni' => $dni,
                                'id_rol' => Parametros::PROFESOR
                            ]);
                        } catch (Exception $th) {
                            if (str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores . 'Error en línea' . $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                        }
                    }
                }
                $numLinea++;
            }
            fclose($file);
        } else {
            return 'Error al procesar el fichero.';
        }

        if ($errores != '') {
            return $errores;
        } else {
            return 0;
        }
    }

    /**
     * Método que procesa el fichero de Unidades.csv e inserta su contenido en BBDD (tablas OfertaGrupo y Tutoria)
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los grupos de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDUnidades($nombreCaja, $DNILogueado)
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));

                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        try {
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;

                            //Se obtiene el nombre del ciclo (columna ESTUDIO), separando la cadena por
                            //paréntesis. Nos quedamos con la cadena central y la buscamos en la tabla.
                            $estudio =  preg_split("(\(|\))", trim($vec[array_search('ESTUDIO', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\""));

                            //Esta consulta se hace "a pelo" porque no coge la funcion lower de SQL
                            $codGrupo = DB::select('select cod from grupo
                            where lower(nombre_ciclo) = ' . "'" . strtolower($estudio[1]) . "'
                            and cod_nivel = '" . $estudio[0] . "'")[0]->cod;

                            OfertaGrupo::create([
                                'cod_centro' => $codCentroEstudios,
                                'cod_grupo' => $codGrupo
                            ]);

                            $anio = trim($vec[array_search('ANNO', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\"");

                            $profesor = explode(",", str_replace("\"", "", trim($vec[array_search('TUTOR', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\"")));

                            $dniProfesor = Profesor::where(
                                [
                                    ['nombre', '=', trim($profesor[1], " \t\n\r\0\x0B\"")],
                                    ['apellidos', '=', trim($profesor[0], " \t\n\r\0\x0B\"")],
                                    ['cod_centro_estudios', '=', $codCentroEstudios]
                                ]
                            )->get()[0]->dni;

                            Tutoria::create([
                                'dni_profesor' => $dniProfesor,
                                'cod_grupo' => $codGrupo,
                                'curso_academico' => Auxiliar::obtenerCursoAcademicoPorAnio($anio),
                                'cod_centro' => $codCentroEstudios
                            ]);

                            //DSB Cambio 16-04-2022: se añade el rol tutor a los tutores de cada curso:
                            $r = RolProfesorAsignado::create([
                                'dni' => $dniProfesor,
                                'id_rol' => Parametros::TUTOR
                            ]);
                        } catch (Exception $th) {
                            if (str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores . 'Error en línea' . $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                        }
                    }
                }
                $numLinea++;
            }
            fclose($file);
        } else {
            return 'Error al procesar el fichero.';
        }

        if ($errores != '') {
            return $errores;
        } else {
            return 0;
        }
    }

    /**
     * Función intermedia que lanza todas las comprobaciones
     * @param string $nombreCaja Nombre de la caja a la que el usuario arrastra el fichero CSV para que se suba
     * @param string $nombreFichero Nombre del fichero que el usuario arrastra a la caja
     * @return mixed Devuelve 0 en caso de que haya pasado todas las validaciones, en caso contrario
     * devuelve un mensaje con la primera validación que ha fallado
     * @author David Sánchez Barragán
     */
    private function comprobarFichero($nombreCaja, $nombreFichero)
    {
        if (!$this->comprobarExtension($nombreFichero, '.csv')) {
            return 'La extensión del fichero no es la correcta. Seleccione solo ficheros .csv.';
        } else if (!$this->comprobarLineasFichero($nombreCaja)) {
            return 'El fichero no está bien formado. Compruebe todas las líneas e inténtelo de nuevo.';
        } else {
            return 0;
        }
    }

    /**
     * Comprueba la extensión del fichero
     * @param string $nombreFichero Nombre del fichero a comprobar
     * @param string $extension Extensión que se desea comprobar
     * @return boolean Devuelve true si la extensión es correcta,
     * false si no lo es
     * @author David Sánchez Barragán
     */
    private function comprobarExtension($nombreFichero, $extension)
    {
        return str_contains($nombreFichero, $extension);
    }

    /**
     * Función que comprueba que el fichero indicado en $nombreCaja
     * tenga las mismas columnas que la cabecera de dicho fichero
     * (para evitar ficheros malformados manualmente)
     * @return boolean Devuelve true si el fichero está bien formado, false si no lo está
     * @author David Sánchez Barragán
     */
    private function comprobarLineasFichero($nombreCaja)
    {
        $numLinea = 0;
        $columnasEncabezado = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);

        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));
                // Si estamos en el primer número de línea
                // ese será el encabezado
                if ($numLinea != 0) {
                    if ($columnasEncabezado != count($vec) && count($vec) > 1) {
                        return false;
                    }
                } else {
                    $columnasEncabezado = count($vec);
                }
                $numLinea++;
            }
            fclose($file);
        }

        return true;
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD de profesores

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion te devuelve todos los profesores de la base de datos
     * @return void
     */
    public function verProfesores($dni_profesor)
    {
        $datos = array();
        $roles = array();
        $centroEstudios = Profesor::select('cod_centro_estudios')->where('dni', '=', $dni_profesor)->get();

        foreach (Profesor::where('cod_centro_estudios', '=', $centroEstudios[0]->cod_centro_estudios)->get() as $p) {
            foreach (RolProfesorAsignado::select('id_rol')->where('dni', '=', $p->dni)->get() as $rol) {
                $roles[] = $rol->id_rol;
            }
            $centro_estudios = CentroEstudios::select('nombre')->where('cod', '=', $p->cod_centro_estudios)->get();
            $datos[] = [
                'dni' => $p->dni,
                'email' => $p->email,
                'nombre' => $p->nombre,
                'apellidos' => $p->apellidos,
                'centro_estudios' => $centro_estudios[0]->nombre,
                'roles' => $roles
            ];
            unset($roles);
            $roles = array();
        }

        if ($datos) {
            return response()->json($datos, 200);
        } else {
            return response()->json([
                'message' => 'Error al recuperar los profesores'
            ], 401);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion nos devuelve un profesor en concreto, con unos datos en concreto
     * que  son: dni, email, nombre, apellidos, centro de estudios y roles
     *
     * @param [string] $dni_profesor, es el dni del profesor, el cual nos ayudara a buscarlo en la bbdd
     * @return void
     */
    public function verProfesor($dni_profesor)
    {
        $datos = array();
        $roles = array();

        foreach (Profesor::where('dni', '=', $dni_profesor)->get() as $p) {
            foreach (RolProfesorAsignado::select('id_rol')->where('dni', '=', $p->dni)->get() as $rol) {
                $roles[] = $rol->id_rol;
            }

            $centro_estudios = CentroEstudios::select('nombre')->where('cod', '=', $p->cod_centro_estudios)->get();
            $datos[] = [
                'dni' => $p->dni,
                'email' => $p->email,
                'nombre' => $p->nombre,
                'apellidos' => $p->apellidos,
                'centro_estudios' => $centro_estudios[0]->nombre,
                'roles' => $roles
            ];
            unset($roles);
            $roles = array();
        }

        if ($datos) {
            return response()->json($datos, 200);
        } else {
            return response()->json([
                'message' => 'Error al recuperar el profesor'
            ], 401);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion elimina un profesor y su respectiva carpeta
     *
     * @param [string] $dni_profesor, es el dni del profesor, el cual nos ayudara a buscarlo en la bbdd y eliminarlo
     * @return void
     */
    public function eliminarProfesor($dni_profesor)
    {
        Auxiliar::deleteUser(Profesor::find($dni_profesor)->email);
        Profesor::where('dni', '=', $dni_profesor)->delete();
        $ruta = public_path($dni_profesor);
        $this->eliminarCarpetaRecursivo($ruta);
        return response()->json([
            'message' => 'Profesor Eliminado con exito'
        ], 201);
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion elimina de manera recursiva una carpeta y su contenido
     *
     * @param [string] $carpeta -> La ruta de la carpeta
     * @return void
     */
    public function eliminarCarpetaRecursivo($carpeta)
    {
        if (is_dir($carpeta)) {
            foreach (glob($carpeta . "/*") as $archivos_carpeta) {
                if (is_dir($archivos_carpeta)) {
                    $this->eliminarCarpetaRecursivo($archivos_carpeta);
                } else {
                    unlink($archivos_carpeta);
                }
            }
            rmdir($carpeta);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion recoge los datos de un nuevo profesor y el dni de la persona que lo esta creando, despues crea
     * el profesor y sus respectivos roles
     *
     * @param Request $val, recoge el dni,email,nombre,apellidos,password1, password2, roles y el dni de la persona que esta gestionando este nuevo profesor
     * @return response
     */
    public function addProfesor(Request $val)
    {
        $dni = $val->get('dni');
        $email = $val->get('email');
        $nombre = $val->get('nombre');
        $apellidos = $val->get('apellidos');
        $password1 = $val->get('password1');
        $password2 = $val->get('password2');
        $roles = $val->get('roles');
        $dniPersonaCreadora = $val->get('personaAux');
        $centroEstudios = Profesor::select('cod_centro_estudios')->where('dni', '=', $dniPersonaCreadora)->get();

        if (strcmp($password1, $password2) == 0) {
            $profesorAux = Profesor::where('dni', '=', $dni)->get();
            if (sizeof($profesorAux, 0)) {
                return response()->json([
                    'message' => 'Este profesor ya existe'
                ], 401);
            } else {
                $profe = Profesor::create(['dni' => $dni, 'email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'password' => Hash::make($password1), 'cod_centro_estudios' => $centroEstudios[0]->cod_centro_estudios]);
                Auxiliar::addUser($profe, "profesor");
                foreach ($roles as $r) {
                    RolProfesorAsignado::create(['dni' => $dni, 'id_rol' => $r]);
                }
                return response()->json([
                    'message' => 'Profesor Creado con exito'
                ], 201);
            }
        } else {
            return response()->json([
                'message' => 'Contraseñas distintas'
            ], 401);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion permite modificar un profesor
     * Para conseguir que se modifique su rol, este es borrado de la tabla donde lo tiene asignado
     * y añadido de nuevo.
     *
     * @param Request $val, recoge el dni,email,nombre,apellidos,password1, password2, roles y el dni de la persona antes de ser modificada, para poder
     * buscar su informacion
     * @return response
     */
    public function modificarProfesor(Request $val)
    {

        $dni = $val->get('dni');
        $email = $val->get('email');
        $nombre = $val->get('nombre');
        $apellidos = $val->get('apellidos');
        $password1 = $val->get('password1');
        $password2 = $val->get('password2');
        $roles = $val->get('roles');
        $dniPersonaAnt = $val->get('personaAux');

        if (strcmp($password1, $password2) == 0) {

            $email = Profesor::find($dniPersonaAnt)->email;
            Profesor::where('dni', $dniPersonaAnt)
                ->update(['dni' => $dni, 'email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'password' => $password1]);
            Auxiliar::updateUser(Profesor::find($dni), $email);
            RolProfesorAsignado::where('dni', '=', $dni)->delete();
            foreach ($roles as $r) {
                RolProfesorAsignado::create(['dni' => $dni, 'id_rol' => $r]);
            }
            return response()->json([
                'message' => 'Perfil Modificado'
            ], 201);
        } else {
            return response()->json([
                'message' => 'Las contraseñas son distintas'
            ], 401);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     * Esta funcion nos permite ver un profesor que va a ser editado posteriormente
     * y devuelve unos parametros especificos: dni, email, nombre, apellidos, password1, password2, roles
     * y el dni antiguo del profesor a editar , por si este se editara, poder editarlo a través
     * de este.
     *
     * @param [type] $dni_profesor
     * @return void
     */
    public function verProfesorEditar($dni_profesor)
    {
        $datos = array();
        $roles = array();

        foreach (Profesor::where('dni', '=', $dni_profesor)->get() as $p) {
            foreach (RolProfesorAsignado::select('id_rol')->where('dni', '=', $p->dni)->get() as $rol) {
                $roles[] = $rol->id_rol;
            }

            $datos[] = [
                'dni' => $p->dni,
                'email' => $p->email,
                'nombre' => $p->nombre,
                'apellidos' => $p->apellidos,
                'password1' => $p->password,
                'password2' => $p->password,
                'roles' => $roles,
                'personaAux' => $p->dni
            ];
            unset($roles);
            $roles = array();
        }

        if ($datos) {
            return response()->json($datos, 200);
        } else {
            return response()->json([
                'message' => 'Error al recuperar el profesor'
            ], 401);
        }
    }

    #endregion
    /***********************************************************************/

    /***********************************************************************/
    #region CRUD de alumnos

    /**
     * Devuelve una lista con los alumnos según el rol de usuario.
     * - Si es tutor, se devuelve el listado de los alumnos a los que tutoriza
     * - Si es director o jefe de estudios, devuelve los alumnos de todo el centro educativo
     * @param String $dni_logueado DNI de la persona que ha iniciado sesión en la aplicación
     * @return Response Respuesta con el array de alumnos
     * @author David Sánchez Barragán
     */
    public function listarAlumnos($dni_logueado)
    {
        try {
            //Obtengo roles del usuario que está logueado
            $roles = Auxiliar::getRolesProfesorFromEmail(Profesor::where('dni', $dni_logueado)->get()->first()->email);
            $listado = [];

            if (in_array(Parametros::DIRECTOR, $roles) || in_array(Parametros::JEFE_ESTUDIOS, $roles)) {
                $listado = Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
                    ->join('centro_estudios', 'centro_estudios.cod', '=', 'matricula.cod_centro')
                    ->join('profesor', 'profesor.cod_centro_estudios', '=', 'centro_estudios.cod')
                    ->where([
                        ['profesor.dni', '=', $dni_logueado],
                        ['matricula.curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                    ])
                    ->select([
                        'alumno.dni', 'alumno.cod_alumno', 'alumno.email',
                        'alumno.nombre', 'alumno.apellidos', 'alumno.provincia',
                        'alumno.localidad', 'alumno.va_a_fct', 'alumno.matricula_coche',
                        'alumno.cuenta_bancaria', 'alumno.curriculum', 'alumno.fecha_nacimiento',
                        'alumno.movil', 'alumno.telefono', 'alumno.domicilio',
                        'matricula.cod as matricula_cod', 'matricula.cod_grupo as matricula_cod_grupo',
                        'matricula.cod_centro as matricula_cod_centro'
                    ])
                    ->get();
            }

            if (in_array(Parametros::TUTOR, $roles)) {
                $listado = Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
                    ->join('centro_estudios', 'centro_estudios.cod', '=', 'matricula.cod_centro')
                    ->join('tutoria', 'tutoria.cod_grupo', '=', 'matricula.cod_grupo')
                    ->where([
                        ['tutoria.dni_profesor', '=', $dni_logueado],
                        ['matricula.curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                    ])
                    ->select([
                        'alumno.dni', 'alumno.cod_alumno', 'alumno.email',
                        'alumno.nombre', 'alumno.apellidos', 'alumno.provincia',
                        'alumno.localidad', 'alumno.va_a_fct', 'alumno.matricula_coche',
                        'alumno.cuenta_bancaria', 'alumno.curriculum', 'alumno.fecha_nacimiento',
                        'alumno.movil', 'alumno.telefono', 'alumno.domicilio',
                        'matricula.cod as matricula_cod', 'matricula.cod_grupo as matricula_cod_grupo',
                        'matricula.cod_centro as matricula_cod_centro'
                    ])
                    ->get();
            }


            foreach ($listado as $alumno) {
                $alumno->foto = Auxiliar::obtenerURLServidor() . '/api/descargarFotoPerfil/' . $alumno->dni . '/' . uniqid();
                $alumno->curriculum = Auxiliar::obtenerURLServidor() . '/api/descargarCurriculum/' . $alumno->dni . '/' . uniqid();
            }

            return response()->json($listado, 200);
        } catch (Exception $th) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Obtiene los detalles de un alumno según su DNI
     * @param String $dni_alumno DNI del alumno del que queremos obtener el detalle
     * @return Response Respusta con el objeto Alumno solicitado
     * @author David Sánchez Barragán
     */
    public function verAlumno($dni_alumno)
    {
        try {
            $alumno = Alumno::where('dni', '=', $dni_alumno)
                ->select([
                    'alumno.dni', 'alumno.cod_alumno',
                    'alumno.email', 'alumno.nombre', 'alumno.apellidos',
                    'alumno.provincia', 'alumno.localidad', 'alumno.va_a_fct',
                    'alumno.matricula_coche', 'alumno.cuenta_bancaria'
                ])
                ->get()->first();

            if ($alumno) {
                //Pongo a cadena vacía la contraseña por seguridad,
                //para que no viaje por la red
                $alumno->password = '';

                //Foto y CV
                $alumno->foto = Auxiliar::obtenerURLServidor() . '/api/descargarFotoPerfil/' . $alumno->dni . '/' . uniqid();
                $alumno->curriculum = Auxiliar::obtenerURLServidor() . '/api/jefatura/descargarCurriculum/' . $alumno->dni . '/' . uniqid();

                //Incorporación del ciclo formativo al que pertenece
                $alumno->ciclo = Matricula::where([
                    ['dni_alumno', '=', $dni_alumno],
                    ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                ])->select(['cod_grupo'])->get()->first()->cod_grupo;

                return response()->json($alumno, 200);
            } else {
                return response()->json(['mensaje' => 'No existe el alumno consultado'], 400);
            }
        } catch (Exception $th) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Añade el alumno al centro de estudios al que pertenece la persona que ha iniciado sesión en la aplicación
     * @param Request $r Petición con los datos del alumno del formulario de registro
     * @return Response OK o mensaje de error
     * @author David Sánchez Barragán
     */
    public function addAlumno(Request $r)
    {
        try {
            $foto = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni, 'fotoPerfil', $r->foto);
            $curriculum = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni, 'CV', $r->curriculum);

            $alu = Alumno::create([
                'dni' => $r->dni,
                'cod_alumno' => $r->cod_alumno,
                'email' => $r->email,
                'password' => Hash::make($r->password),
                'nombre' => $r->nombre,
                'apellidos' => $r->apellidos,
                'provincia' => $r->provincia,
                'localidad' => $r->localidad,
                'va_a_fct' => $r->va_a_fct,
                'foto' => $foto ? $foto : '',
                'curriculum' => $curriculum ? $curriculum : '',
                'cuenta_bancaria' => $r->cuenta_bancaria,
                'matricula_coche' => $r->matricula_coche,
                'fecha_nacimiento' => $r->fecha_nacimiento,
                'domicilio' => $r->domicilio,
                'telefono' => $r->telefono,
                'movil' => $r->movil
            ]);
            Auxiliar::addUser($alu, 'alumno');

            $matricula_cod_centro = Auxiliar::obtenerCentroPorDNIProfesor(Profesor::where('email', '=', $r->user()->email)->get()->first()->dni);
            Matricula::create([
                'cod' => $r->matricula_cod,
                'cod_centro' => $matricula_cod_centro,
                'dni_alumno' => $r->dni,
                'cod_grupo' => $r->matricula_cod_grupo,
                'curso_academico' => Auxiliar::obtenerCursoAcademico()
            ]);

            return response()->json(['message' => 'Alumno creado correctamente'], 200);
        } catch (Exception $ex) {
            if (str_contains($ex->getMessage(), 'Integrity')) {
                return response()->json(['mensaje' => 'Este alumno ya se ha registrado en la aplicación'], 400);
            } else {
                return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
            }
            //return response()->json(['mensaje' => $ex->getMessage()], 400);
        }
    }

    /**
     * Modifica los datos de un alumno
     * @param Request $r Petición con los datos del alumno del formulario de edición
     * @return Response OK o mensaje de error
     * @author David Sánchez Barragán
     */
    public function modificarAlumno(Request $r)
    {
        try {
            if (strlen($r->dni_antiguo) != 0) {
                $foto = '';
                $curriculum = '';

                //Si la foto o el curriculum contienen su parte de URL, no se guardan en la base de datos;
                //se recoge entonces el path original que tuvieran
                if (!str_contains($r->foto, "descargarFoto")) {
                    $fotoAnterior = Alumno::where('dni', '=', $r->dni_antiguo)->get()->first()->foto;
                    if (strlen($fotoAnterior) != 0) {
                        Auxiliar::borrarFichero($fotoAnterior);
                    }
                    $foto = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni, 'fotoPerfil', $r->foto);
                } else {
                    $foto = Alumno::where('dni', '=', $r->dni_antiguo)->get()->first()->foto;
                }

                if (!str_contains($r->curriculum, "descargarCurriculum")) {
                    $cvAnterior = Alumno::where('dni', '=', $r->dni_antiguo)->get()->first()->curriculum;
                    if (strlen($cvAnterior) != 0) {
                        Auxiliar::borrarFichero($cvAnterior);
                    }
                    $curriculum = Auxiliar::guardarFichero(public_path() . DIRECTORY_SEPARATOR .  $r->dni, 'CV', $r->curriculum);
                } else {
                    $curriculum = Alumno::where('dni', '=', $r->dni_antiguo)->get()->first()->curriculum;
                }

                Alumno::where('dni', '=', $r->dni_antiguo)->update([
                    'dni' => $r->dni,
                    'cod_alumno' => $r->cod_alumno,
                    'email' => $r->email,
                    'nombre' => $r->nombre,
                    'apellidos' => $r->apellidos,
                    'provincia' => $r->provincia,
                    'localidad' => $r->localidad,
                    'va_a_fct' => $r->va_a_fct,
                    'foto' => $foto != '' ? $foto : '',
                    'curriculum' => $curriculum ? $curriculum : '',
                    'cuenta_bancaria' => $r->cuenta_bancaria,
                    'matricula_coche' => $r->matricula_coche,
                    'fecha_nacimiento' => $r->fecha_nacimiento,
                    'domicilio' => $r->domicilio,
                    'telefono' => $r->telefono,
                    'movil' => $r->movil
                ]);

                if ($r->password) {
                    Alumno::where('dni', '=', $r->dni)->update([
                        'password' => Hash::make($r->password)
                    ]);
                }

                Auxiliar::updateUser(Alumno::where('dni', '=', $r->dni)->get()->first(), $r->email);

                Matricula::where([
                    ['dni_alumno', '=', $r->dni],
                    ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                ])->update([
                    'cod' => $r->matricula_cod,
                    'cod_centro' => $r->matricula_cod_centro,
                    'dni_alumno' => $r->dni,
                    'cod_grupo' => $r->matricula_cod_grupo,
                    'curso_academico' => Auxiliar::obtenerCursoAcademico()
                ]);
            } else {
                throw new Exception('El usuario no puede estar vacío');
            }

            return response()->json(['message' => 'Alumno actualizado'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }

    /**
     * Elimina de la aplicación a un alumno
     * @param String $dni_alumno DNI del alumno
     * @return Response OK o mensaje de error
     * @author David Sánchez Barragán
     */
    public function eliminarAlumno($dni_alumno)
    {
        try {
            Auxiliar::deleteUser(Alumno::find($dni_alumno));
            Alumno::where('dni', '=', $dni_alumno)->delete();

            return response()->json(['mensaje' => 'Alumno borrado correctamente'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }

    /**
     * Devuelve un objeto File para que la foto sea accesible desde el lado cliente
     * @param string $dni DNI del alumno del que se quiere obtener la foto
     * @param string $guid Universally Unique Identifier, utilizado para que en el cliente se detecte
     * el cambio de foto si se actualiza.
     * @return File Objeto File para que la foto sea accesible desde el atributo src en etiquetas img en lado cliente
     * @author David Sánchez Barragán
     */
    public function descargarFotoPerfil($dni, $guid)
    {
        $pathFoto = Alumno::where('dni', '=', $dni)->select('foto')->get()->first()->foto;
        if ($pathFoto) {
            return response()->file($pathFoto);
        } else {
            return response()->json(['mensaje' => 'Error, fichero no encontrado'], 404);
        }
    }

    /**
     * Devuelve una respuesta de tipo descarga que envía el curriculum al cliente
     * @param string $dni DNI del alumno del que se quiere obtener el curriculum
     * @return BinaryFileResponse con el curriculum del alumno
     * @author David Sánchez Barragán
     */
    public function descargarCurriculum($dni)
    {
        $pathCV = Alumno::where('dni', '=', $dni)->select('curriculum')->get()->first()->curriculum;
        if ($pathCV) {
            return response()->download($pathCV);
        } else {
            return response()->json(['mensaje' => 'Error, fichero no encontrado'], 404);
        }
    }

    /**
     * Obtiene un listado de grupos
     * @return Response JSON con grupos
     * @author David Sánchez Barragán
     */
    public function listarGrupos()
    {
        $listaGrupos = Grupo::select(['cod', 'nombre_ciclo'])->get();
        return response()->json($listaGrupos, 200);
    }

    #endregion
    /***********************************************************************/



    /***********************************************************************/
    #region cuestionarios

    /**
     * Guarda un nuevo cuestionario con los valores pasados en la request
     * @param Request
     * @return Response JSON con cuestionario y sus preguntas.
     * @author Pablo García Galán
     */
    public function crearCuestionario(Request $r)
    {
        $cuestionario = Cuestionario::create([
            'titulo' => $r->titulo,
            'destinatario' => $r->destinatario,
            'codigo_centro' => $r->codigo_centro,
        ]);

        foreach ($r->preguntas as $preg) {
            PreguntasCuestionario::create(['id_cuestionario' => $cuestionario->id ,'tipo' => $preg['tipo'], 'pregunta' => $preg['pregunta']]);
        }
        return response()->json(['message' => 'Formulario creado con éxito'], 200);
    }




    /**
     * Se obitnene el cuestionario activo en función del destinatario y del código del centro.
     * @param destinatario
     * @param codigo_centro
     * @return Response JSON con cuestionario y sus preguntas.
     * @author Pablo García Galán
     */
    public function obtenerCuestionario($destinatario, $codigo_centro)
    {
        $datos = array();
        $cuestionario = Cuestionario::where([
                ['activo', true],
                ['destinatario', $destinatario],
                ['codigo_centro', $codigo_centro]
            ])->get();

        foreach (PreguntasCuestionario::where('id_cuestionario', '=', $cuestionario[0]->id)->get() as $p) {
            $datos[] = $p;
        }

        $result = array("id"=>$cuestionario[0]->id , "titulo"=>$cuestionario[0]->titulo  , "destinatario"=>$cuestionario[0]->destinatario  , "preguntas"=>$datos , "activo"=>$cuestionario[0]->activo);

        if ($result) {
            return response()->json($result, 200);
        } else {
            return response()->json([
                'message' => 'Error al obtener cuestionario'
            ], 401);
        }
    }


    /**
     * Se obitnene un cuestionario en función de su id.
     * @param id
     * @return Response JSON con cuestionario y sus preguntas.
     * @author Pablo García Galán
     */
    public function obtenerCuestionarioEdicion($id)
    {
        $datos = array();
        $cuestionario = Cuestionario::select('*')->where('id', '=', $id)->get();

        foreach (PreguntasCuestionario::where('id_cuestionario', '=', $cuestionario[0]->id)->get() as $p) {
            $datos[] = $p;
        }

        $result = array("id"=>$cuestionario[0]->id , "titulo"=>$cuestionario[0]->titulo  , "destinatario"=>$cuestionario[0]->destinatario  , "preguntas"=>$datos );

        if ($result) {
            return response()->json($result, 200);
        } else {
            return response()->json([
                'message' => 'Error al obtener cuestionario'
            ], 401);
        }
    }

    /**
     * Almacena en base de datos un cuestionario con sus preguntas y respuestas.
     * @param id del formulario.
     * @return Response OK si no hay errores.
     * @author Pablo García Galán
     */
    public function crearCuestionarioRespondido(Request $r)
    {
        $cuestionarioRespondido = CuestionarioRespondido::create([
            'titulo' => $r->titulo,
            'destinatario' => $r->destinatario,
            'id_usuario' => $r->id_usuario,
            'codigo_centro' => $r->codigo_centro,
            'ciclo' => $r->ciclo,
            'curso_academico' => $r->curso_academico,
            'dni_tutor_empresa' =>$r->dni_tutor_empresa,
        ]);
        foreach ($r->respuestas as $resp) {
            PreguntasRespondidas::create(['id_cuestionario_respondido' => $cuestionarioRespondido->id ,'tipo' => $resp['tipo'], 'pregunta' => $resp['pregunta'], 'respuesta' => $resp['respuesta']]);
        }
        return response()->json(['message' => 'Formulario guardado con éxito'], 200);
    }


    /**
     * Obtiene cuestionario respondido para ese id_usuario.
     * @param id del usuario.
     * @return Response JSON con cuestionario o error.
     * @author Pablo García Galán
     */
    public function verificarCuestionarioRespondido($id_usuario)
    {
        try {
            $cuestionarios = CuestionarioRespondido::where('id_usuario', '=', $id_usuario)->get();
            return response()->json($cuestionarios, 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }


    /**
     * Obtiene los cuestionarios respondido para ese código centro.
     * @param codigo_centro
     * @return Response JSON con cuestionarios.
     * @author Pablo García Galán
     */
    public function listarCuestionarios($codigo_centro)
    {
        $cuestionarios = Cuestionario::where('codigo_centro', '=', $codigo_centro)->get();
        return response()->json($cuestionarios, 200);
    }

    /**
     * Elimina cuestionario con ese id.
     * @param id
     * @return Response JSON OK o error.
     * @author Pablo García Galán
     */
    public function eliminarCuestionario($id)
    {
        try {
            Cuestionario::where('id', '=', $id)->delete();
            return response()->json(['mensaje' => 'Cuestionario borrado correctamente'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }

    /**
     * Actualiza el cuestionario.
     * @param Request
     * @return Response JSON OK o error.
     * @author Pablo García Galán
     */
    public function editarCuestionario(Request $r)
    {
        try {
            Cuestionario::where('id', '=', $r->id)->update(['destinatario' => $r->destinatario, 'titulo' => $r->titulo]);
            PreguntasCuestionario::where('id_cuestionario', '=', $r->id)->delete();
            foreach ($r->preguntas as $preg) {
                PreguntasCuestionario::create(['id_cuestionario' => $r->id ,'tipo' => $preg['tipo'], 'pregunta' => $preg['pregunta']]);
            }
            return response()->json(['mensaje' => 'Cuestionario editado correctamente'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }

    /**
     * Obtiene los cuestionarios para los alumnos asociados a un tutor de empresa en función de su dni y el curso académico.
     * @param dni
     * @return Response JSON OK si no hay error.
     * @author Pablo García Galán
     */
    public function obtenerCuestionariosTutorEmpresaAlumnos($dni)
    {
        $fct = Fct::where('dni_tutor_empresa', '=', $dni)->get();
        $datos=[];

        foreach ($fct as $registroFct) {

            $cuestionarioRespondido = CuestionarioRespondido::where([
                ['dni_tutor_empresa', $dni],
                ['id_usuario', $registroFct->dni_alumno],
                ['curso_academico', $registroFct->curso_academico],
                ['destinatario', 'empresa']
            ])->get();

            $respondido=false;

            if(($cuestionarioRespondido) && (count($cuestionarioRespondido)>0)){
                $respondido=true;
            }

            $cod_centro='';
            $cod_grupo='';

            $matricula = Matricula::where('dni_alumno', '=', $registroFct->dni_alumno)
                ->select(['*'])
                ->first();
                if ($matricula){
                    $cod_centro=$matricula->cod_centro;
                    $cod_grupo=$matricula->cod_grupo;
                }

            $registroAlumno = array(
                'dni_alumno'=> $registroFct->dni_alumno,
                'curso_academico'=> $registroFct->curso_academico,
                'cod_centro'=> $cod_centro,
                'cod_grupo'=> $cod_grupo,
                'dni_alumno'=> $registroFct->dni_alumno,
                'respondido'=> $respondido);

            array_push($datos,$registroAlumno);
        }

        return response()->json($datos, 200);
    }

    /**
     * Activa el formulario en función de su id_formulario y desactiva el resto en función de su destinatario y código_centro.
     * @param dni
     * @author Pablo García Galán
     */
    public function activarCuestionario($id_formulario, $destinatario, $codigo_centro)
    {
        Cuestionario::where([
            ['destinatario', '=',$destinatario],
            ['codigo_centro', '=', $codigo_centro]
            ])->update([
            'activo' => false
        ]);

        Cuestionario::where([
            ['id', '=',$id_formulario],
            ])->update([
            'activo' => true
        ]);

    }

    /**
     * Desactiva el formulario en función de su id_formulario.
     * @param dni
     * @author Pablo García Galán
     */
    public function desactivarCuestionario($id_formulario)
    {
        Cuestionario::where([
            ['id', '=',$id_formulario],
            ])->update([
            'activo' => false
        ]);
    }


    /**
     * Obtiene todos los cursos académicos existentes.
     * @return Response JSON OK si no hay error.
     * @author Pablo García Galán
     */
    public function obtenerCursosAcademicos()
    {
        $cursosAcademicos = Matricula::select('curso_academico')->distinct()->get();
        return response()->json($cursosAcademicos, 200);
    }


    /**
     * Obtiene las medias de todas las preguntas de tipo rango filtrando por curso_academico, destinatario y codigo_centro.
     * @param Request
     * @return Response JSON con las medias por pregunta.
     * @author Pablo García Galán
     */
    public function obtenerMediasCuestionariosRespondidos(Request $r){

       $respuestasFiltradas = CuestionarioRespondido::select(DB::raw('avg(preguntas_respondidas.respuesta) as value, preguntas_respondidas.pregunta as name'))->join('preguntas_respondidas', 'cuestionario_respondidos.id', '=', 'preguntas_respondidas.id_cuestionario_respondido')
            ->where([
                ['cuestionario_respondidos.curso_academico', '=', $r->curso_academico],
                ['cuestionario_respondidos.destinatario', '=', $r->destinatario],
                ['cuestionario_respondidos.codigo_centro', '=', $r->codigo_centro],
                ['preguntas_respondidas.tipo', '=', 'rango'],
            ])
            ->groupBy('preguntas_respondidas.pregunta')
            ->get();

            return response()->json($respuestasFiltradas, 200);
    }


    /**
     * Obtiene los cuestionarios respondidos filtrados por destinatarios, codigo_centro y curso_academico.
     * @param Request
     * @return Response JSON con cuestionarios.
     * @author Pablo García Galán
     */
    public function listarCuestionariosRespondidos(Request $r){
        try {
            $cuestionarios = CuestionarioRespondido::where([
            ['destinatario', '=',$r->destinatario],
            ['codigo_centro', '=', $r->codigo_centro],
            ['curso_academico', '=', $r->curso_academico]
            ])->get();
            return response()->json($cuestionarios, 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }


    /**
     * Descarga cuestionario en .pdf por su id_cuestionario con librería DOMPDF
     * @param Request
     * @return Fichero con el cuestionario.
     * @author Pablo García Galán
     */
    public function descargarCuestionario($id_cuestionario){

        $datos = array();
        $cuestionario = CuestionarioRespondido::select('*')->where('id', '=', $id_cuestionario)->get();

        foreach (PreguntasRespondidas::where('id_cuestionario_respondido', '=', $cuestionario[0]->id)->get() as $p) {
            $datos[] = $p;
        }

        $result = array("id"=>$cuestionario[0]->id , "titulo"=>$cuestionario[0]->titulo  , "destinatario"=>$cuestionario[0]->destinatario  , "preguntas"=>$datos );


        $pdf = PDF::loadView('cuestionario', [
            'datos' =>$datos,
            'titulo' =>$cuestionario[0]->titulo,
            'id_usuario' =>$cuestionario[0]->id_usuario,
            'destinatario' =>$cuestionario[0]->destinatario,
            'codigo_centro' =>$cuestionario[0]->codigo_centro,
            'ciclo' =>$cuestionario[0]->ciclo,
            'curso_academico' =>$cuestionario[0]->curso_academico,
        ]);
        return $pdf->download('pdf_file.pdf');

    }

    #endregion
    /***********************************************************************/


    /***********************************************************************/
    #region Anexo FEM05
    /**
     * Genera y devuelve una respuesta de tipo descarga que envía el anexo FEM05 con la información del alumno.
     * @param string $dni_alumno DNI del alumno
     * @return BinaryFileResponse con el anexo del alumno
     * @author David Sánchez Barragán
     */
    public function generarAnexoFEM05($dni_alumno)
    {
        try {
            //Consultas
            $alumno = Alumno::where('dni', $dni_alumno)->first();

            $grupo = Grupo::where('cod', Matricula::where([
                ['dni_alumno', '=', $dni_alumno],
                ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
            ])->first()->cod_grupo)->first();

            $aux_curso_academico = AuxCursoAcademico::where('cod_curso', Auxiliar::obtenerCursoAcademico())->first();

            $empresa = Empresa::where([
                ['id', '=', Fct::where([
                    ['dni_alumno', '=', $dni_alumno],
                    ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
                ])->first()->id_empresa],
            ])->first();

            $idConvenio = DB::select('select cod_convenio from convenio where id_empresa = ' . $empresa->id . ' and (year(fecha_ini) >= ' . explode('-', $aux_curso_academico->fecha_inicio)[0] . ' or year(fecha_fin) <= ' . explode('-', $aux_curso_academico->fecha_fin)[0] . ')')[0]->cod_convenio;
            $convenio = Convenio::where('cod_convenio', $idConvenio)->first();

            $profesor = Profesor::where('dni', Tutoria::where([
                ['cod_grupo', $grupo->cod],
                ['curso_academico', '=', Auxiliar::obtenerCursoAcademico()]
            ])->first()->dni_profesor)->first();

            // Construyo el array con todos los datos
            $auxPrefijos = ['alumno', 'grupo', 'aux_curso_academico', 'empresa', 'convenio', 'profesor'];
            $auxDatos = [$alumno, $grupo, $aux_curso_academico, $empresa, $convenio, $profesor];
            $datos = Auxiliar::modelsToArray($auxDatos, $auxPrefijos);

            //Fecha
            $datos['dia'] = date('d');
            $datos['mes'] = strtoupper(Parametros::MESES[intval(date('m'))]);
            $datos['anio'] = date('Y');

            // Ahora genero el Word en sí
            // Establezco las variables que necesito
            $nombrePlantilla = 'AnexoFEM05';
            $rutaOrigen = 'anexos' . DIRECTORY_SEPARATOR . 'plantillas' . DIRECTORY_SEPARATOR . $nombrePlantilla . '.docx';
            Auxiliar::existeCarpeta(public_path($dni_alumno . DIRECTORY_SEPARATOR . $nombrePlantilla));
            $rutaDestino =  $dni_alumno . DIRECTORY_SEPARATOR . $nombrePlantilla . DIRECTORY_SEPARATOR . $nombrePlantilla . '_' . $dni_alumno . '.docx';

            // Creo la plantilla y la relleno (podría haber usado la función de Auxiliar, pero me hace falta
            // introducir imágenes)
            $template = new TemplateProcessor($rutaOrigen);
            if(file_exists($alumno->foto)) {
                $template->setImageValue('imagen-alumno', $alumno->foto);
            } else {
                $datos['imagen-alumno'] = '';
            }
            $template->setValues($datos);
            $template->saveAs($rutaDestino);

            return response()->download($rutaDestino);
        } catch (Exception $ex) {
            return response()->json(['la vida es dura y se ha producido un excepción' => $ex->getMessage()], 400);
        }
    }
    #endregion
    /***********************************************************************/

}
