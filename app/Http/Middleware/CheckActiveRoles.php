<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            $totalRoles = $user->roles()->count();

            // Se o utilizador tem grupos atribuídos, mas nenhum está ativo
            if ($totalRoles > 0) {
                $activeRoles = $user->roles()->where('status', true)->count();

                if ($activeRoles === 0) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('error', 'A sua conta encontra-se suspensa porque os seus grupos de permissões estão inativos.');
                }
            }
        }

        return $next($request);
    }
}
