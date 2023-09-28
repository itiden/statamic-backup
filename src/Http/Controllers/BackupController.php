<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;

class BackupController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $backups = Backuper::getBackups();

        // Required by statamic to render the table
        // i think i want it to be in the view instead
        $meta = [
            'columns' => [
                [
                    'label' => 'Created',
                    'field' => 'name',
                    'visible' => true,
                    'sortable' => true,
                ],
                [
                    'label' => 'Size',
                    'field' => 'size',
                    'visible' => true,
                    'sortable' => true,
                ]
            ]
        ];

        $data = [
            'data' => $backups->values(),
            'meta' => $meta,
        ];

        return response()->json($data);
    }
}
