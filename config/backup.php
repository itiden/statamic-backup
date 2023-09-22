<?php

return [
    'content_path' => storage_path('content'),
    'backup' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
        'max_backups' => 10,
    ],
    'backup_clients' => [
        Itiden\Backup\Clients\ContentRestorer::class,
        Itiden\Backup\Clients\AssetsRestorer::class,
    ],
];
