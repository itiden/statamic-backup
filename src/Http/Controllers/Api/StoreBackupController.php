<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Contracts\Cache\Repository;
use Itiden\Backup\Jobs\BackupJob;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;
use Itiden\Backup\StateManager;

final readonly class StoreBackupController
{
    public function __invoke(Repository $cache): JsonResponse
    {
        if ($cache->has(StateManager::JOB_QUEUED_KEY)) {
            throw ActionAlreadyInProgress::fromInQueue();
        }

        $cache->put(StateManager::JOB_QUEUED_KEY, true);

        dispatch(new BackupJob());

        return response()->json([
            'message' => __('statamic-backup::backup.backup_started'),
        ]);
    }
}
