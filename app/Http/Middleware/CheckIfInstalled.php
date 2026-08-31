<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $lockFile = storage_path('installed.lock');
        $isInstallRoute = $request->is('install*');

        // Si el archivo de bloqueo existe, el sistema ya está configurado
        if (file_exists($lockFile)) {
            if ($isInstallRoute) {
                return redirect()->route('home');
            }
            return $next($request);
        }

        // Si no está instalado y no está en las rutas de instalación, redirigir
        if (!$isInstallRoute) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
