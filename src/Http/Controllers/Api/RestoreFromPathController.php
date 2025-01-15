<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Http\Requests\RestoreFromPathRequest;

final readonly class RestoreFromPathController
{
    public function __invoke(RestoreFromPathRequest $request): JsonResponse
    {
        Restorer::restore(BackupDto::fromAbsolutePath($request->validated('path')));

        if ($request->input('destroyAfterRestore', false)) {
            File::delete($request->validated('path'));
        }

        return response()->json([
            'message' => __('statamic-backup::backup.restore.success'),
        ]);
    }
}
