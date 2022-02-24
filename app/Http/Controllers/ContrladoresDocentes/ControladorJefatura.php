<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Auxiliar;
use App\Auxiliar\Parametros;
use App\Http\Controllers\Controller;
use App\Models\RolProfesorAsignado;
use App\Models\Alumno;
use App\Models\CentroEstudios;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\OfertaGrupo;
use App\Models\Profesor;
use App\Models\Tutoria;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControladorJefatura extends Controller
{
    const CABECERA_ALUMNOS = ["ALUMNO", "APELLIDOS", "NOMBRE", "SEXO", "DNI", "NIE", "FECHA_NACIMIENTO", "LOCALIDAD_NACIMIENTO", "PROVINCIA_NACIMIENTO", "NOMBRE_CORRESPONDENCIA", "DOMICILIO", "LOCALIDAD", "PROVINCIA", "TELEFONO", "MOVIL", "CODIGO_POSTAL", "TUTOR1", "DNI_TUTOR1", "TUTOR2", "DNI_TUTOR2", "PAIS", "NACIONALIDAD", "EMAIL_ALUMNO", "EMAIL_TUTOR2", "EMAIL_TUTOR1", "TELEFONOTUTOR1", "TELEFONOTUTOR2", "MOVILTUTOR1", "MOVILTUTOR2", "APELLIDO1", "APELLIDO2", "TIPODOM", "NTUTOR1", "NTUTOR2", "NSS"];
    const CABECERA_MATRICULAS = ["ALUMNO", "APELLIDOS", "NOMBRE", "MATRICULA", "ETAPA", "ANNO", "TIPO", "ESTUDIOS", "GRUPO", "REPETIDOR", "FECHAMATRICULA", "CENTRO", "PROCEDENCIA", "ESTADOMATRICULA", "FECHARESMATRICULA", "NUM_EXP_CENTRO", "PROGRAMA_2"];
    const CABECERA_PROFESORES = ["CODIGO", "APELLIDOS", "NOMBRE", "NRP", "DNI", "ABREVIATURA", "FECHA_NACIMIENTO", "SEXO", "TITULO", "DOMICILIO", "LOCALIDAD", "CODIGO_POSTAL", "PROVINCIA", "TELEFONO_RP", "ESPECIALIDAD", "CUERPO", "DEPARTAMENTO", "FECHA_ALTA_CENTRO", "FECHA_BAJA_CENTRO", "EMAIL", "TELEFONO"];
    const CABECERA_UNIDADES = ["ANNO", "GRUPO", "ESTUDIO", "CURSO", "TUTOR"];


    /**
     * Función que recibe el fichero CSV y lo guarda en la ruta temporal
     * para su posterior procesamiento
     *
     * @param Request $r La request debe incluir como mínimo, el fichero,
     * el nombre del fichero y el nombre de la caja en la que el usuario arrastra el fichero
     *
     * @return Response Devuelve un error en caso de que el CSV esté mal formado u ocurra algún problema
     * al guardar el fichero
     * @author David Sánchez Barragán
     */
    public function recibirCSV(Request $r)
    {
        $errores = [];

        #region Bucle
        foreach ($r->collect() as $item) {
            $nombreCaja = strtolower($item['box_file']);
            //$tipoFichero = $item['content_type'];
            $nombreFichero = $item['file_name'];
            $fichero = $item['file_content'];

            //Si se guarda el fichero satisfactoriamente, se comprueba
            //si el fichero es íntegro
            if ($this->guardarFichero($nombreCaja, $fichero)) {
                $resultado = $this->comprobarFichero($nombreCaja, $nombreFichero);
                //error_log($resultado);
                //Si el resultado es distinto de cero, el fichero no está bien
                //por lo tanto, se mete el resultado en errores
                if ($resultado != 0) {
                    $errores[$nombreCaja] = $resultado;
                } else {
                    //Si es cero, el fichero está bien y pasamos a insertar los registros en
                    //base de datos
                    $resultado = $this->procesarFicheroABBDD($nombreCaja);


                    //Si el resultado es distinto de cero, el fichero ha tenido errores al insertarse
                    //por lo tanto, se mete el resultado en errores

                    if ($resultado != 0) {
                        $errores[$nombreCaja] = $resultado;
                    }
                }

                //Borramos el fichero al final
                $this->borrarFichero($this->getCSVPathFile($nombreCaja));
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
     *
     * @param string $nombreCaja Nombre de la caja
     * @return Response Respuesta de la petición formateada en JSON con el
     * parámetro mensaje, que indicará el resultado de la llamada a esta función
     *
     * @author David Sánchez Barragán
     */
    public function procesarFicheroABBDD($nombreCaja)
    {

        $resultado = false;
        //error_log($nombreCaja);
        switch ($nombreCaja) {

            case 'alumnos':
                $resultado = $this->procesarFicheroABBDDAlumnos($nombreCaja);
                break;
                // case 'materias':
                //     $resultado =  $this->procesarFicheroABBDDMaterias($nombreCaja);
                //     break;
            case 'matriculas':
                $resultado =  $this->procesarFicheroABBDDMatriculas($nombreCaja);
                break;
                // case 'notas':
                //     $resultado =  $this->procesarFicheroABBDDNotas($nombreCaja);
                //     break;
            case 'profesores':
                $resultado =  $this->procesarFicheroABBDDProfesores($nombreCaja);
                break;
            case 'unidades':
                $resultado =  $this->procesarFicheroABBDDUnidades($nombreCaja);
                break;
            default:
                $resultado =  'Error';
                break;
        }

        return $resultado;
    }


    /**
     * Obtiene la ruta donde se alojan los ficheros temporales CSV
     *
     * @return string Ruta de la carpeta donde se alojan los ficheros CSV
     */
    private function getCSVPath()
    {
        return public_path() . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR;
    }

    /**
     * Obtiene la ruta junto con el nombre del fichero de los ficheros temporales CSV
     *
     * @param string Nombre del fichero CSV
     * @return string Ruta de la carpeta donde se alojan los ficheros CSV
     */
    private function getCSVPathFile($nombreCaja)
    {
        return $this->getCSVPath() . $nombreCaja . ".csv";
    }

    /**
     * Guarda los ficheros recibidos por la petición en formato base64
     *
     * @param string $nombreCaja
     * @param string $fichero
     * @return boolean True en caso de éxito, false en caso de error
     * @author David Sánchez Barragán
     */
    private function guardarFichero($nombreCaja, $fichero)
    {
        try {
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
     * Borra el fichero según la ruta indicada en $path
     *
     * @param string $path Ruta del fichero a eliminar
     * @author David Sánchez Barragán
     */
    private function borrarFichero($path)
    {
        unlink($path);
    }



    /**
     * Método que procesa el fichero de Alumnos.csv e inserta su contenido en BBDD (tabla Alumno)
     *
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     *
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
                        //error_log('Lo proceso. La línea es la ' . $numLinea);
                        try {
                            //Se recoge el DNI, si está vacío, se recoge el NIE
                            $dni = trim($vec[array_search('DNI', self::CABECERA_ALUMNOS)] != '' ?  $vec[array_search('DNI', self::CABECERA_ALUMNOS)] : $vec[array_search('NIE', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\"");
                            Alumno::create([
                                'dni' => $dni,
                                'cod_alumno' => trim($vec[array_search('ALUMNO', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                //Para mantener la integridad de la base de datos, si el correo
                                //está vacío, se genera uno con el DNI
                                'email' => trim($vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] != '' ? $vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] : $dni . '@fctfiller.com', " \t\n\r\0\x0B\""),
                                //Se debería crear una contraseña por defecto para todos los usuarios dados de alta automáticamente
                                'password' => md5('12345'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'provincia' => trim($vec[array_search('PROVINCIA', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'localidad' => trim($vec[array_search('LOCALIDAD', self::CABECERA_ALUMNOS)], " \t\n\r\0\x0B\""),
                                'va_a_fct' => '0'
                            ]);
                        } catch (Exception $th) {
                            if (str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores . 'Error en línea' . $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                            ////error_log($th->getMessage());
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

    // private function procesarFicheroABBDDMaterias($nombreCaja)
    // {
    //     return 0;
    // }


    /**
     * Método que procesa el fichero de Matriculas.csv e inserta su contenido en BBDD (tabla ??)
     *
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los profesores de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     *
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDMatriculas($nombreCaja, $DNILogueado = '1A')
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));

                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        ////error_log('Lo proceso. La línea es la ' . $numLinea);
                        try {
                            //Se DEBE sustituir esta variable con una select al centro de estudios
                            //según el DNI de la persona que se ha logueado.
                            //Como a esta parte solo tendrán acceso los profesores (Jefes de estudios)
                            //hacer solo la búsqueda en la tabla profesores.
                            //De momento se elegirá el centro de estudios asociado al primer profesor de la tabla.
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            // $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', Profesor::all()->first()->dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            $dniAlumno = Alumno::where('cod_alumno', trim($vec[array_search('ALUMNO', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\""))->get()[0]->dni;

                            $codNivel = explode(' ', trim($vec[array_search('ESTUDIOS', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\""))[2];
                            $nombreCiclo = trim(strtolower(explode('-', $vec[array_search('ESTUDIOS', self::CABECERA_MATRICULAS)])[1]));

                            $codGrupo = Grupo::where([
                                ['cod_nivel', $codNivel],
                                ['nombre_ciclo', $nombreCiclo]
                            ])->get()[0]->cod;

                            $anio = trim($vec[array_search('ANNO', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\"");

                            $matricula = trim($vec[array_search('MATRICULA', self::CABECERA_MATRICULAS)], " \t\n\r\0\x0B\"");
                            //error_log($matricula);

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

    // private function procesarFicheroABBDDNotas($nombreCaja)
    // {
    //     return 0;
    // }

    /**
     * Método que procesa el fichero de Profesores.csv e inserta su contenido en BBDD (tabla Profesor)
     *
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los profesores de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     *
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDProfesores($nombreCaja, $DNILogueado = '1A')
    {
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));

                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        ////error_log('Lo proceso. La línea es la ' . $numLinea);
                        try {
                            $dni = trim($vec[array_search('DNI', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\"");

                            //Se DEBE sustituir esta variable con una select al centro de estudios
                            //según el DNI de la persona que se ha logueado.
                            //Como a esta parte solo tendrán acceso los profesores (Jefes de estudios)
                            //hacer solo la búsqueda en la tabla profesores.
                            //De momento se elegirá el centro de estudios asociado al primer profesor de la tabla.
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            // $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', Profesor::all()->first()->dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;

                            Profesor::create([
                                'dni' => $dni,
                                'email' => trim($vec[array_search('EMAIL', self::CABECERA_PROFESORES)] != '' ? $vec[array_search('EMAIL', self::CABECERA_PROFESORES)] : $dni . '@fctfiller.com', " \t\n\r\0\x0B\""),
                                'password' => md5('12345'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\""),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_PROFESORES)], " \t\n\r\0\x0B\""),
                                'cod_centro_estudios' => $codCentroEstudios
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
     *
     * @param string $nombreCaja Nombre de la caja a la que se ha arrastrado el fichero
     * @param string $DNILogueado Por defecto, vacío, representa el DNI de la persona que se ha loguedo en el sistema.
     * Se utilizará para obtener el centro de estudios en el cual insertar a los grupos de este CSV
     * @return mixed Devuelve un string con el error por cada una de las líneas con errores, 0 en caso contrario
     *
     * @author David Sánchez Barragán
     */
    private function procesarFicheroABBDDUnidades($nombreCaja, $DNILogueado = '1A')
    {
        //error_log('hola');
        $numLinea = 0;
        $filePath = $this->getCSVPathFile($nombreCaja);
        $errores = '';
        if ($file = fopen($filePath, "r")) {
            while (!feof($file)) {
                $vec = explode("\t", fgets($file));

                if ($numLinea != 0) {
                    if (count($vec) > 1) {
                        ////error_log('Lo proceso. La línea es la ' . $numLinea);
                        try {
                            //Se DEBE sustituir esta variable con una select al centro de estudios
                            //según el DNI de la persona que se ha logueado.
                            //Como a esta parte solo tendrán acceso los profesores (Jefes de estudios)
                            //hacer solo la búsqueda en la tabla profesores.
                            //De momento se elegirá el centro de estudios asociado al primer profesor de la tabla.
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            // $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', Profesor::all()->first()->dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            //error_log('Cod centro estudios: ' . $codCentroEstudios);

                            //Se obtiene el nombre del ciclo (columna ESTUDIO), separando la cadena por
                            //paréntesis. Nos quedamos con la cadena central y la buscamos en la tabla.
                            $estudio =  preg_split("(\(|\))", trim($vec[array_search('ESTUDIO', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\""));
                            //error_log('estudio: ' . print_r($estudio, true));

                            //return response()->json(200);

                            //Esta consulta se hace "a pelo" porque no coge la funcion lower de SQL
                            //$codGrupo = Grupo::where('lower(nombre_ciclo)', $estudio)->get()[0]->cod;
                            $codGrupo = DB::select('select cod from grupo
                            where lower(nombre_ciclo) = ' . "'" . strtolower($estudio[1]) . "'
                            and cod_nivel = '" . $estudio[0] . "'")[0]->cod;


                            OfertaGrupo::create([
                                'cod_centro' => $codCentroEstudios,
                                'cod_grupo' => $codGrupo
                            ]);

                            $anio = trim($vec[array_search('ANNO', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\"");

                            $profesor = explode(",", str_replace("\"", "", trim($vec[array_search('TUTOR', self::CABECERA_UNIDADES)], " \t\n\r\0\x0B\"")));
                            //error_log(print_r($profesor, true));
                            // error_log(trim($profesor[1], " \t\n\r\0\x0B\""));
                            // error_log(trim($profesor[0], " \t\n\r\0\x0B\""));
                            // error_log($codCentroEstudios);

                            $dniProfesor = Profesor::where(
                                [
                                    ['nombre', '=', trim($profesor[1], " \t\n\r\0\x0B\"")],
                                    ['apellidos', '=', trim($profesor[0], " \t\n\r\0\x0B\"")],
                                    ['cod_centro_estudios', '=', $codCentroEstudios]
                                ]
                            )->get()[0]->dni;
                            error_log($dniProfesor);

                            Tutoria::create([
                                'dni_profesor' => $dniProfesor,
                                'cod_grupo' => $codGrupo,
                                'curso_academico' => Auxiliar::obtenerCursoAcademicoPorAnio($anio),
                                'cod_centro' => $codCentroEstudios
                            ]);
                        } catch (Exception $th) {
                            error_log($th);
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
     *
     * @return mixed Devuelve 0 en caso de que haya pasado todas las validaciones, en caso contrario
     * devuelve un mensaje con la primera validación que ha fallado
     *
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
     *
     * @return boolean Devuelve true si la extensión es correcta,
     * false si no lo es
     *
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
     *
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
                        //dd($vec);
                        return false;
                    }
                } else {
                    //dd($vec);
                    $columnasEncabezado = count($vec);
                }
                $numLinea++;
            }
            fclose($file);
        }

        return true;
    }

    /**
     * Devuelve una lista de los alumnos del centro al que pertenecza la persona que se haya logueado
     * @param String $dni_logueado DNI de la persona que ha iniciado sesión en la aplicación
     * @return Response Respuesta con el array de alumnos
     * @author David Sánchez Barragán
     */
    public function listarAlumnos($dni_logueado)
    {
        try {
            $listado = Alumno::join('matricula', 'matricula.dni_alumno', '=', 'alumno.dni')
                ->join('centro_estudios', 'centro_estudios.cod', '=', 'matricula.cod_centro')
                ->join('profesor', 'profesor.cod_centro_estudios', '=', 'centro_estudios.cod')
                ->where('profesor.dni', '=', $dni_logueado)
                ->select(['alumno.dni', 'alumno.cod_alumno', 'alumno.email', 'alumno.password', 'alumno.nombre', 'alumno.apellidos', 'alumno.provincia', 'alumno.localidad', 'alumno.va_a_fct'])
                ->get();

            return response()->json($listado, 200);
        } catch (Exception $th) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $th->getMessage()], 500);
        }
    }

    /**
     * @author Laura <lauramorenoramos@gmail.com>
     *Esta funcion te devuelve todos los profesores de la base de datos
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
     * Obtiene los detalles de un alumno en función de su código de alumno
     * @param String $dni_alumno DNI del alumno del que queremos obtener el detalle
     * @return Response Respusta con el objeto Alumno solicitado
     * @author David Sánchez Barragán
     */
    public function verAlumno($dni_alumno)
    {
        try {
            $alumno = Alumno::where('dni', '=', $dni_alumno)
                ->select(['alumno.dni', 'alumno.cod_alumno', 'alumno.email', 'alumno.password', 'alumno.nombre', 'alumno.apellidos', 'alumno.provincia', 'alumno.localidad', 'alumno.va_a_fct'])
                ->get()->first();

            if ($alumno) {
                //Pongo a cadena vacía la contraseña por seguridad,
                //para que no viaje por la red
                $alumno->password = '';
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
            Alumno::create([
                'dni' => $r->dni,
                'cod_alumno' => $r->cod_alumno,
                'email' => $r->email,
                'password' => $r->password,
                'nombre' => $r->nombre,
                'apellidos' => $r->apellidos,
                'provincia' => $r->provincia,
                'localidad' => $r->localidad,
                'va_a_fct' => $r->va_a_fct,
            ]);

            return response()->json(['message' => 'Alumno creado correctamente'], 200);
        } catch (Exception $ex) {
            if (str_contains($ex->getMessage(), 'Integrity')) {
                return response()->json(['mensaje' => 'Este alumno ya se ha registrado en la aplicación'], 400);
            } else {
                return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
            }
        }
    }

    /**
     * Esta funcion elimina un profesor y su respectiva carpeta
     *
     * @param [string] $dni_profesor, es el dni del profesor, el cual nos ayudara a buscarlo en la bbdd y eliminarlo
     * @return void
     */
    public function eliminarProfesor($dni_profesor)
    {

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
                Profesor::create(['dni' => $dni, 'email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'password' => $password1, 'cod_centro_estudios' => $centroEstudios[0]->cod_centro_estudios]);

                foreach ($roles as $r) {
                    error_log($r);
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
     * Modifica los datos del alumno
     * @param Request $r Petición con los datos del alumno del formulario de edición
     * @return Response OK o mensaje de error
     * @author David Sánchez Barragán
     */
    public function modificarAlumno(Request $r)
    {
        try {
            Alumno::where('dni', '=', $r->dni)->update([
                'dni' => $r->dni,
                'cod_alumno' => $r->cod_alumno,
                'email' => $r->email,
                'password' => $r->password,
                'nombre' => $r->nombre,
                'apellidos' => $r->apellidos,
                'provincia' => $r->provincia,
                'localidad' => $r->localidad,
                'va_a_fct' => $r->va_a_fct,
            ]);

            return response()->json(['message' => 'Alumno actualizado'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
        }
    }

    /**
     * Elimina de la aplicación el alumno
     * @param String $dni_alumno
     * @return Response OK o mensaje de error
     * @author David Sánchez Barragán
     */
    public function eliminarAlumno($dni_alumno)
    {
        try {
            Alumno::where('dni', '=', $dni_alumno)->delete();

            //Aquí va la eliminación de los anexos asociados al alumno, cuando los tengamos

            return response()->json(['mensaje' => 'Alumno borrado correctamente'], 200);
        } catch (Exception $ex) {
            return response()->json(['mensaje' => 'Se ha producido un error en el servidor. Detalle del error: ' . $ex->getMessage()], 500);
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

            Profesor::where('dni', $dniPersonaAnt)
                ->update(['dni' => $dni, 'email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'password' => $password1]);

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
}
