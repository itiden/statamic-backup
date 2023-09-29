<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Http\Response;

class DestroyBackupController extends Controller
{
    public function __invoke(string $timestamp): JsonResponse|RedirectResponse
    {
        $backup = Backuper::deleteBackup($timestamp);

        return Response::success('Deleted ' . $backup->name);
    }
}
