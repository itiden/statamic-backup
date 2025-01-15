<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Itiden\Backup\Facades\Restorer;

final readonly class RestoreController
{
    public function __invoke(string $timestamp): JsonResponse
    {
        Restorer::restoreFromTimestamp($timestamp);

        return response()->json([
            'message' => __('statamic-backup::backup.restore.success'),
        ]);
    }
}
