<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Halaman daftar publik (katalog, artikel) hanya memakai parameter query berupa teks.
 * Nilai array seperti `?q[]=x` (biasanya dari bot) dibuang supaya tidak memicu error 500.
 */
class ScalarQueryParameters
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->query->all() as $key => $value) {
            if (is_array($value)) {
                $request->query->remove($key);
            }
        }

        return $next($request);
    }
}
