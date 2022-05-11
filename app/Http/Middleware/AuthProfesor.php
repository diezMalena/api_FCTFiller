<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthProfesor
{
    /**
     * Permite el paso a los usuarios con perfil de profesor
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @author Dani J. Coello <daniel.jimenezcoello@gmail.com>
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->tipo == 'profesor') {
            return $next($request);
        } else {
            return response()->json(['message' => 'Acceso no autorizado'], 401);
        }
    }
}
