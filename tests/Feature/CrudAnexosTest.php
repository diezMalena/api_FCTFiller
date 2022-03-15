<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Fct;
use App\Models\Profesor;
use App\Models\Tutoria;
use Tests\TestCase;

use function PHPUnit\Framework\isNull;

class CrudAnexosTest extends TestCase
{
    /**
     *  @author Laura <lauramorenoramos97@gmail.com>
     *Comprueba que el servicio /api/listarAnexos' funciona y  devuelve una lista de anexos.
     */
    public function test_listar_anexos()
    {
        $tutor_dni = Tutoria::select('dni_profesor')->inRandomOrder()->first();
        error_log($tutor_dni);
        $tutor_dniAux=$tutor_dni->dni_profesor;
        $ruta_anexo= Fct::select('ruta_anexo')->where('ruta_anexo','like',"%$tutor_dniAux%")->first();
        $vacio = false;
        $ruta_anexo='';

        //Todo esto es para asegurarme de que recibo datos y no hago la prueba con algo vacio
        while($vacio==false){

            $tutor_dni = Tutoria::select('dni_profesor')->inRandomOrder()->first();
            $tutor_dniAux=$tutor_dni->dni_profesor;
            $ruta_anexo= Fct::select('ruta_anexo')->where('ruta_anexo','like',"%$tutor_dniAux%")->first();

            if(!empty($ruta_anexo->ruta_anexo)){
                $vacio=true;
            }
        }


        $response=$this->json('GET', '/api/listarAnexos/'.$tutor_dniAux)
        ->assertStatus(200);

        $this->assertIsObject($response);

    }


    /**
     *  @author Laura <lauramorenoramos97@gmail.com>
     *Comprueba que el servicio /api/eliminarAnexo/' funciona correctamente.
     */
    public function test_eliminar_anexo()
    {
        $tutor_dni = Tutoria::select('dni_profesor')->inRandomOrder()->first();
        $tutor_dniAux=$tutor_dni->dni_profesor;
        $vacio = false;
        $ruta_anexo='';

        //Aqui hago un bucle para asegurarme que elijo un profesor que tenga algun anexo1 en bbdd
        while($vacio==false){
            //Vuelvo a generar estas variables para sacar un profesor distinto al anterior y poder
            //ver los anexos de este nuevo profesor
            $tutor_dni = Tutoria::select('dni_profesor')->inRandomOrder()->first();
            $tutor_dniAux=$tutor_dni->dni_profesor;
            $ruta_anexo= Fct::select('ruta_anexo')->where('ruta_anexo','like',"%$tutor_dniAux%")->first();

            //Si la ruta del anexo no esta vacia, quiere decir que ese profesor tiene anexos y por
            //lo tanto, podemos continuar el test
            if(!empty($ruta_anexo->ruta_anexo)){
                $vacio=true;
            }
        }

        //SI logro un profesor que tenga Anexos1

        $Anexo=explode("/", $ruta_anexo->ruta_anexo);
        $Anexo=$Anexo[2].'.docx';

        $this->json('DELETE', '/api/eliminarAnexo/'.$tutor_dniAux.'/'.$Anexo)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'Archivo eliminado'
        ], 200);


}
}
