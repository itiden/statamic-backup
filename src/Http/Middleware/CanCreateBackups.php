<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Itiden\Backup\Http\Response;
use Statamic\Facades\User;

class CanCreateBackups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (User::current()->can('create backups')) {
            return $next($request);
        }

        return Response::error('You are not authorized to create backups.', 403);
    }
}
