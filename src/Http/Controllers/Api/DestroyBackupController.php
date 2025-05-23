<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Itiden\Backup\Contracts\Repositories\BackupRepository;

final readonly class DestroyBackupController
{
    public function __invoke(string $id, BackupRepository $repo): JsonResponse|RedirectResponse
    {
        $backup = $repo->remove($id);

        return response()->json(['message' => __('statamic-backup::backup.destroy.success', [
            'name' => $backup->name,
        ])]);
    }
}
