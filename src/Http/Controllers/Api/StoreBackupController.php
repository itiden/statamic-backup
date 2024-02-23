<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;

class StoreBackupController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $backup = Backuper::backup();

        return response()->json([
            'message' => 'Backup created ' . $backup->name,
        ]);
    }
}
