<?php

namespace Itiden\Backup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CanCreateBackups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (User::current()->can('create backups')) {
            return $next($request);
        }

        return redirect()->back()->with('error', "You don't have permission to create backups.");
    }
}
