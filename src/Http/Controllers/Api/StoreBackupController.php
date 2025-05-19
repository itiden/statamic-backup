<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Jobs\BackupJob;
use Itiden\Backup\StateManager;

final readonly class StoreBackupController
{
    public function __invoke(StateManager $stateManager, #[Authenticated] Authenticatable $user): JsonResponse
    {
        $stateManager->dispatch(new BackupJob(user: $user));

        return response()->json(['message' => __('statamic-backup::backup.backup_started')]);
    }
}
