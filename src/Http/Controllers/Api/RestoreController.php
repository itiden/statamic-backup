<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Jobs\RestoreJob;
use Itiden\Backup\StateManager;

final readonly class RestoreController
{
    public function __invoke(
        string $id,
        StateManager $stateManager,
        #[Authenticated] Authenticatable $user,
    ): JsonResponse {
        $stateManager->dispatch(new RestoreJob(
            id: $id,
            user: $user,
        ));

        return response()->json(['message' => __('statamic-backup::backup.restore.started')]);
    }
}
