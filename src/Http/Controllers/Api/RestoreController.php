<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;
use Itiden\Backup\Jobs\RestoreJob;
use Itiden\Backup\StateManager;
use Statamic\Contracts\Auth\User;

final readonly class RestoreController
{
    public function __invoke(string $id, StateManager $stateManager, #[Authenticated] User $user): JsonResponse
    {
        $stateManager->dispatch(new RestoreJob($id, $user));

        return response()->json(['message' => __('statamic-backup::backup.restore.started')]);
    }
}
