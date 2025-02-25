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
    public function __invoke(RestoreFromPathRequest $request, StateManager $stateManager): JsonResponse
    {
        $stateManager->dispatch(new RestoreFromPathJob(path: $request->validated('path')));

        return response()->json(['message' => __('statamic-backup::backup.restore.started')]);
    }
}
