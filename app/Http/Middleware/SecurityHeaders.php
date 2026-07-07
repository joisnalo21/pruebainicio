<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Eliminar la cabecera que expone la versión de PHP (si PHP lo permite)
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        // Si la respuesta es de tipo Symfony Response (lo normal en Laravel)
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN'); // Anti-clickjacking
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            
            // Políticas Cross-Origin (COOP, CORP)
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
            
            // Content Security Policy (CSP)
            // Permite cargar estilos propios y scripts propios. 
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
        }

        return $response;
    }
}