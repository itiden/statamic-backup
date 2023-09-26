<?php

return [
    'content_path' => storage_path('content'),
    'backup' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
        'max_backups' => 10,
    ],
    'backup_drivers' => [
        Itiden\Backup\Drivers\Content::class,
        Itiden\Backup\Drivers\Assets::class,
    ],
];
