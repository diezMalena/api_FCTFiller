<?php

namespace App\Http\Controllers\ContrladoresDocentes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControladorJefatura extends Controller
{


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
        // $nombreCaja = $r->nombreCaja;
        // $nombreFichero = $r->nombreFichero;
        // $fichero = $r->fichero;

        $nombreCaja = strtolower('alumnos');
        $nombreFichero = 'perico_eldelospalotes.csv';
        $fichero = '';

        $this->guardarFichero($nombreCaja, $nombreFichero, $fichero);

        $comprobaciones = $this->comprobarFichero($nombreCaja, $nombreFichero);

        if ($comprobaciones == 0) {
            $this->procesarFicheroABBDD($nombreCaja);
            return response()->json(['mensaje' => 'OK'], 200);
        } else {
            $this->borrarFichero($nombreCaja);
            return response()->json(['mensaje' => $comprobaciones], 400);
        }
    }

    private function getCSVPath($nombreCaja)
    {
        return public_path() . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . $nombreCaja . ".csv";
    }

    private function guardarFichero($nombreCaja, $nombreFichero, $fichero)
    {
        return true;
    }

    private function borrarFichero($nombreCaja)
    {
        unlink($this->getCSVPath($nombreCaja));
    }

    private function procesarFicheroABBDD($nombreCaja)
    {
        switch ($nombreCaja) {
            case 'alumnos':
                return $this->procesarFicheroABBDDAlumnos();
                break;
            case 'materias':
                return $this->procesarFicheroABBDDMaterias();
                break;
            case 'matriculas':
                return $this->procesarFicheroABBDDAlumnos();
                break;
            case 'notas':
                return $this->procesarFicheroABBDDNotas();
                break;
            case 'profesores':
                return $this->procesarFicheroABBDDProfesores();
                break;
            case 'unidades':
                return $this->procesarFicheroABBDDUnidades();
                break;
            default:
                return false;
                break;
        }
    }

    private function procesarFicheroABBDDAlumnos()
    {

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
     */
    private function comprobarLineasFichero($nombreCaja)
    {
        $numLinea = 0;
        $columnasEncabezado = 0;
        $filePath = $this->getCSVPath($nombreCaja);


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
