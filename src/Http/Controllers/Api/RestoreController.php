<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Restorer;

class RestoreController extends Controller
{
    public function __invoke(string $timestamp): JsonResponse
    {
        Restorer::restoreFromTimestamp($timestamp);

        return response()->json([
            'message' => __('statamic-backup::backup.restore.success'),
        ]);
    }
}
