<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Cache\Repository;
use Itiden\Backup\Jobs\BackupJob;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;
use Itiden\Backup\StateManager;
use Statamic\Contracts\Auth\User;

final readonly class StoreBackupController
{
    public function __invoke(Repository $cache, #[Authenticated] User $user): JsonResponse
    {
        if ($cache->has(StateManager::JOB_QUEUED_KEY)) {
            throw ActionAlreadyInProgress::fromInQueue();
        }

        $cache->put(StateManager::JOB_QUEUED_KEY, true);

        dispatch(new BackupJob($user));

        return response()->json(['message' => __('statamic-backup::backup.backup_started')]);
    }
}
