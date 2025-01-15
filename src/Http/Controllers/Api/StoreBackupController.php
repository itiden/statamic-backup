<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Itiden\Backup\Facades\Backuper;

final readonly class StoreBackupController
{
    public function __invoke(): JsonResponse
    {
        $backup = Backuper::backup();

        return response()->json([
            'message' => __('statamic-backup::backup.created', ['name' => $backup->name]),
        ]);
    }
}
