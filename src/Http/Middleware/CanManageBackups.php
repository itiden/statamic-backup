<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Itiden\Backup\Http\Response;
use Statamic\Facades\User;

class CanManageBackups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (User::current()->hasPermission('manage backups')) {
            return $next($request);
        }

        return Response::error('You are not authorized to manage backups.', 403);
    }
}
