<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Jika user belum login, atau role-nya tidak cocok, tolak akses (403)
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Maaf, halaman ini hanya untuk ' . $role);
        }

        return $next($request);
    }
}