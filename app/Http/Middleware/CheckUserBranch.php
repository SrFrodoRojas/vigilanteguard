<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserBranch
{

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && ! $user->branch_id) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu usuario no tiene una sucursal asignada. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}

