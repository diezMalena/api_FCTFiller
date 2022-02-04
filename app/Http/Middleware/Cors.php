<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request)
         //Url a la que se le dará acceso en las peticiones
        ->header("Access-Control-Allow-Origin","http://localhost:4200")
        ->header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE")
        ->header("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization");
    }
}
