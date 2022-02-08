<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Auxiliar\Parametros;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\CentroEstudios;
use App\Models\Profesor;
use Exception;
use Illuminate\Http\Request;

class ControladorJefatura extends Controller
{
    const CABECERA_ALUMNOS = ["ALUMNO", "APELLIDOS", "NOMBRE", "SEXO", "DNI", "NIE", "FECHA_NACIMIENTO", "LOCALIDAD_NACIMIENTO", "PROVINCIA_NACIMIENTO", "NOMBRE_CORRESPONDENCIA", "DOMICILIO", "LOCALIDAD", "PROVINCIA", "TELEFONO", "MOVIL", "CODIGO_POSTAL", "TUTOR1", "DNI_TUTOR1", "TUTOR2", "DNI_TUTOR2", "PAIS", "NACIONALIDAD", "EMAIL_ALUMNO", "EMAIL_TUTOR2", "EMAIL_TUTOR1", "TELEFONOTUTOR1", "TELEFONOTUTOR2", "MOVILTUTOR1", "MOVILTUTOR2", "APELLIDO1", "APELLIDO2", "TIPODOM", "NTUTOR1", "NTUTOR2", "NSS"];
    //const CABECERA_MATERIAS = ["MATERIA", "DESCRIPCION", "ABREVIATURA", "DEPARTAMENTO", "CURSO"];
    const CABECERA_MATRICULAS = ["ALUMNO", "APELLIDOS", "NOMBRE", "MATRICULA", "ETAPA", "ANNO", "TIPO", "ESTUDIOS", "GRUPO", "REPETIDOR", "FECHAMATRICULA", "CENTRO", "PROCEDENCIA", "ESTADOMATRICULA", "FECHARESMATRICULA", "NUM_EXP_CENTRO", "PROGRAMA_2"];
    //const CABECERA_NOTAS = [];
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
                            $dni = trim($vec[array_search('DNI', self::CABECERA_ALUMNOS)] != '' ?  $vec[array_search('DNI', self::CABECERA_ALUMNOS)] : $vec[array_search('NIE', self::CABECERA_ALUMNOS)]);
                            Alumno::create([
                                'dni' => $dni,
                                //Para mantener la integridad de la base de datos, si el correo
                                //está vacío, se genera uno con el DNI
                                'email' => trim($vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] != '' ? $vec[array_search('EMAIL_ALUMNO', self::CABECERA_ALUMNOS)] : $dni . '@fctfiller.com'),
                                //Se debería crear una contraseña por defecto para todos los usuarios dados de alta automáticamente
                                'password' => md5('12345'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_ALUMNOS)]),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_ALUMNOS)]),
                                'provincia' => trim($vec[array_search('PROVINCIA', self::CABECERA_ALUMNOS)]),
                                'localidad' => trim($vec[array_search('LOCALIDAD', self::CABECERA_ALUMNOS)]),
                                'va_a_fct' => '0'
                            ]);
                        } catch (Exception $th) {
                            if(str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores .'Error en línea'. $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
                            }
                            //error_log($th->getMessage());
                        }
                    }
                }
                $numLinea++;
            }
            fclose($file);
        } else {
            return 'Error al procesar el fichero.';
        }

        if($errores != '') {
            return $errores;
        } else {
            return 0;
        }


    }

    // private function procesarFicheroABBDDMaterias($nombreCaja)
    // {
    //     return 0;
    // }

    private function procesarFicheroABBDDMatriculas($nombreCaja)
    {
        return 0;
    }

    private function procesarFicheroABBDDNotas($nombreCaja)
    {
        return 0;
    }

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
    private function procesarFicheroABBDDProfesores($nombreCaja, $DNILogueado = '')
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
                            $dni = trim($vec[array_search('DNI', self::CABECERA_PROFESORES)]);

                            //Se DEBE sustituir esta variable con una select al centro de estudios
                            //según el DNI de la persona que se ha logueado.
                            //Como a esta parte solo tendrán acceso los profesores (Jefes de estudios)
                            //hacer solo la búsqueda en la tabla profesores.
                            //De momento se elegirá el centro de estudios asociado al primer profesor de la tabla.
                            //$codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', Profesor::all()->first()->dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;

                            Profesor::create([
                                'dni' => $dni,
                                'email' => trim($vec[array_search('EMAIL', self::CABECERA_PROFESORES)] != '' ? $vec[array_search('EMAIL', self::CABECERA_PROFESORES)] : $dni . '@fctfiller.com'),
                                'password' => md5('12345'),
                                'nombre' => trim($vec[array_search('NOMBRE', self::CABECERA_PROFESORES)]),
                                'apellidos' => trim($vec[array_search('APELLIDOS', self::CABECERA_PROFESORES)]),
                                'cod_centro_estudios' => $codCentroEstudios
                            ]);

                        } catch (Exception $th) {
                            if(str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores .'Error en línea'. $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
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

        if($errores != '') {
            return $errores;
        } else {
            return 0;
        }


    }

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
    private function procesarFicheroABBDDUnidades($nombreCaja)
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
                            $dni = trim($vec[array_search('DNI', self::CABECERA_PROFESORES)]);

                            //Se DEBE sustituir esta variable con una select al centro de estudios
                            //según el DNI de la persona que se ha logueado.
                            //Como a esta parte solo tendrán acceso los profesores (Jefes de estudios)
                            //hacer solo la búsqueda en la tabla profesores.
                            //De momento se elegirá el centro de estudios asociado al primer profesor de la tabla.
                            //$codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', $DNILogueado)->get()->first()->cod_centro_estudios))->get()[0]->cod;
                            $codCentroEstudios = CentroEstudios::where('cod', (Profesor::where('dni', Profesor::all()->first()->dni)->get()->first()->cod_centro_estudios))->get()[0]->cod;

                            // Grupo::create([
                            //     'cod' => '2DAM',
                            //     'nombre_largo' => '2º Desarrollo de Aplicaciones Multiplataforma (LOE)',
                            //     'nombre_ciclo' => 'Desarrollo de Aplicaciones Multiplataforma',
                            //     'cod_familia_profesional' => 4,
                            //     'cod_nivel' => 'CFGS'
                            // ]);

                        } catch (Exception $th) {
                            if(str_contains($th->getMessage(), 'Integrity')) {
                                $errores = $errores . 'Registro repetido, línea ' . $numLinea . ' del CSV.' . Parametros::NUEVA_LINEA;
                            } else {
                                $errores = $errores .'Error en línea'. $numLinea . ': ' . $th->getMessage() . Parametros::NUEVA_LINEA;
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

        if($errores != '') {
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
}
