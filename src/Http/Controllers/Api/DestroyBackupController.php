<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Contracts\Repositories\BackupRepository;

class DestroyBackupController extends Controller
{
    public function __invoke(string $timestamp, BackupRepository $repo): JsonResponse|RedirectResponse
    {
        $backup = $repo->remove($timestamp);

        return response()->json([
            'message' => __('statamic-backup::backup.destroy.success', ['name' => $backup->name])
        ]);
    }
}
