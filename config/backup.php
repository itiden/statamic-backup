<?php

return [
    /*
    / The path to the content directory
    / This is used by the default content backup driver
    */
    'content_path' => storage_path('content'),

    /*
    / The backup destination options
    */
    'destination' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
    ],

    /*
    / The path to the temp directory
    */
    'temp_path' => storage_path('temp/statamic-backups'),

    /*
    / The maximum number of backups to keep
    / when superceded the oldest backup will be deleted
    */
    'max_backups' => 10,

    /*
    / The backup password
    / set to null to disable password protection
    */
    'password' => env('BACKUP_PASSWORD', null),

    /*
    / The backup schedule options
    / set to false to disable automatic backups
    */
    'schedule' => [
        'frequency' => 'daily',
        'time' => '00:00',
    ],

    /*
    / The backup drivers to use
    / These are the drivers that will be used to backup your site
    / You can add your own drivers here
    */
    'backup_drivers' => [
        Itiden\Backup\Drivers\Content::class,
        Itiden\Backup\Drivers\Assets::class,
        Itiden\Backup\Drivers\Users::class,
    ],
];
