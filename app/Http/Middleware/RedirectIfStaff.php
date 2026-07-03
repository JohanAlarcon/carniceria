<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfStaff
{
    /** El personal (carnicero) usa el panel /admin, no la tienda pública. */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_staff) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
