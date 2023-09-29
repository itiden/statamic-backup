<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Itiden\Backup\Http\Response;
use Statamic\Facades\User;

class CanDeleteBackups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (User::current()->hasPermission('delete backups')) {
            return $next($request);
        }

        return Response::error('You are not authorized to delete backups.', 403);
    }
}
