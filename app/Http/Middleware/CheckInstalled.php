<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (file_exists(storage_path('installed')) && !$request->is('install/complete')) {
            return redirect('/');
        }

        return $next($request);
    }
}
