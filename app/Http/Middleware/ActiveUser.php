<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            // cerrar sesión y limpiar sesión
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // respuesta según tipo de petición
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu usuario está deshabilitado.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Tu usuario está deshabilitado.',
            ]);
        }

        return $next($request);
    }

}
