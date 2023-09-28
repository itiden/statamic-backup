<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Http\Response;

class RestoreController extends Controller
{
    public function __invoke(string $timestamp): JsonResponse
    {
        Restorer::restoreFromTimestamp($timestamp);

        return Response::success('Backup restored.');
    }
}
