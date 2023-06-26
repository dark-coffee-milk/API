<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIsAdmin
{

    public function handle($request, Closure $next)
    {

        if (Auth::id() == 8) {
            return $next($request);
        }

        return redirect('/');
    }
}
