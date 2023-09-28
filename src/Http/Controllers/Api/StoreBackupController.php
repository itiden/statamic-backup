<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Http\Response;

class StoreBackupController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $backup = Backuper::backup();

        return Response::success('Backup created ' . $backup->name);
    }
}
