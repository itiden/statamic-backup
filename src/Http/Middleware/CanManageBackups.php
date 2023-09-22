<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CanManageBackups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (User::current()->can('manage backups')) {
            return $next($request);
        }

        return redirect()->back()->with('error', "You don't have permission to manage backups.");
    }
}
