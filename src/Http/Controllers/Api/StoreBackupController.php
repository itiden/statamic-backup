<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Http\JsonResponse;
use Itiden\Backup\Jobs\BackupJob;
use Itiden\Backup\StateManager;
use Statamic\Contracts\Auth\User;

final readonly class StoreBackupController
{
    public function __invoke(StateManager $stateManager, #[Authenticated] User $user): JsonResponse
    {
        $stateManager->dispatch(new BackupJob($user));

        return response()->json(['message' => __('statamic-backup::backup.backup_started')]);
    }
}
