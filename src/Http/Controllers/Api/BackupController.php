<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Http\Resources\BackupResource;

class BackupController extends Controller
{
    public function __invoke(BackupRepository $repo): AnonymousResourceCollection
    {
        $backups = $repo->all();

        return BackupResource::collection($backups)
            ->additional(['meta' => [
                // Required by statamic to render the table
                'columns' => [
                    [
                        'label' => 'Name',
                        'field' => 'name',
                        'visible' => true,
                    ],
                    [
                        'label' => 'Created at',
                        'field' => 'created_at',
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
            ]]);
    }
}
