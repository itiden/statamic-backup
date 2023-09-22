<?php

return [
    'content_path' => storage_path('content'),
    'backup' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
    ],
    'backup_clients' => [
        Itiden\Backup\Clients\ContentRestorer::class,
        Itiden\Backup\Clients\AssetsRestorer::class,
    ],
];
