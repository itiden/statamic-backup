<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Itiden\Backup\Facades\Backuper;

use function Illuminate\Support\defer;

final readonly class StoreBackupController
{
    public function __invoke(): JsonResponse
    {
        defer(static fn () => Backuper::backup());

        return response()->json([
            'message' => __('statamic-backup::backup.backup_started'),
        ]);
    }
}
