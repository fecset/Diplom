<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->isAdmin()) {
            abort_if($request->route('user')?->isAdmin() || $request->input('role') === 'admin', 403);
        }

        return $next($request);
    }
}
