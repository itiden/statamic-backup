<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;
use Itiden\Backup\Jobs\RestoreFromTimestampJob;
use Itiden\Backup\StateManager;
use Statamic\Contracts\Auth\User;

final readonly class RestoreController
{
    public function __invoke(string $timestamp, Repository $cache, #[Authenticated] User $user): JsonResponse
    {
        if ($cache->has(StateManager::JOB_QUEUED_KEY)) {
            throw ActionAlreadyInProgress::fromInQueue();
        }

        $cache->put(StateManager::JOB_QUEUED_KEY, true);

        dispatch(new RestoreFromTimestampJob(
            timestamp: $timestamp,
            user: $user
        ));

        return response()->json([
            'message' => __('statamic-backup::backup.restore.started'),
        ]);
    }
}
