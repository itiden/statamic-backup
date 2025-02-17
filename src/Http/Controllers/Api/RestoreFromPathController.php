<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;
use Itiden\Backup\Http\Requests\RestoreFromPathRequest;
use Itiden\Backup\Jobs\RestoreFromPathJob;
use Itiden\Backup\StateManager;

final readonly class RestoreFromPathController
{
    public function __invoke(RestoreFromPathRequest $request, Repository $cache): JsonResponse
    {
        if ($cache->has(StateManager::JOB_QUEUED_KEY)) {
            throw new ActionAlreadyInProgress();
        }

        $cache->put(StateManager::JOB_QUEUED_KEY, true);

        dispatch(new RestoreFromPathJob(
            path: $request->validated('path'),
            deleteAfter: $request->input('destroyAfterRestore', false)
        ));

        return response()->json([
            'message' => __('statamic-backup::backup.restore.started'),
        ]);
    }
}
