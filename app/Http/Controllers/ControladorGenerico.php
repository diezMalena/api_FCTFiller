<?php

namespace App\Http\Controllers;

use App\Auxiliar\Auxiliar;
use App\Models\Usuario_view;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Facades\File;

class ControladorGenerico extends Controller
{

    /**
     * Extrae de una vista los datos del usuario que ha introducido el correo y contraseña,
     * comprueba que ese email existe en la base de datos y después compara que la contraseña
     * que ha introducido el usuario coincida con la contraseña asociada a ese email.
     * Si todo eso es correcto llama a la función de obtener sus datos y creal el token,
     * si no, devuelve un error.
     *
     * @param int $usuario array con los datos del usuario
     * @author alvaro <alvarosantosmartin6@gmail.com>
     */
    public function login(Request $request)
    {
        //Extraigo los campos
        $email = $request->get('email');
        $pass = $request->get('pass');
        //Hago la query
        $quer = 'select * from usuarios_view'
            . ' where email = ?';
        $usuario_view = DB::select($quer, [$email]);
        // error_log(print_r($usuario_view, true));
        if (count($usuario_view) > 0) {
            $usuario_view = $usuario_view[0];
            $ckPass = Hash::check($pass, $usuario_view->password);
            if ($ckPass) {

                $usuario = Auxiliar::getDatosUsuario($usuario_view);
                // $usuario->token = auth()->user()->createToken('authToken')->accessToken;
                return response()->json($usuario, 200);
            } else {
                return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
            }
        } else {
            return response()->json(['mensaje' => 'Datos de inicio de sesión incorrectos'], 403);
        }
    }

    /**
     * Esta función pone una firma en un anexo
     * @param Request $req contiene el anexo que se firma, la parte que firma y la firma en sí, en forma de string en base 64
     */
    public function firmarAnexo(Request $req)
    {
        // Creamos la imagen y guardamos su ruta temporal
        $ruta = $this->decodificarImagen($req->contenido);

        // La ponemos en el documento


        // Eliminamos el archivo temporal


        // Devolvemos la response al cliente

    }

    private function decodificarImagen(string $img64)
    {
        //Primero decodificamos la imagen
        $img64 = str_replace('data:image/png;base64,', '', $img64);
        $img64 = str_replace(' ', '+', $img64);
        $img = base64_decode($img64);

        //Construimos una ruta temporal para la firma
        $rutaFirma = 'tmp' . DIRECTORY_SEPARATOR . 'firmas';
        Auxiliar::existeCarpeta($rutaFirma);
        $rutaFirma .= DIRECTORY_SEPARATOR . 'firma.png';

        //Creamos el archivo y devolvemos la ruta
        File::put($rutaFirma, $img);
        return $rutaFirma;
    }
}
