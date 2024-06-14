<?php

return [
    /**
     * The path to the content directory
     *
     * This is used by the default content backup driver
     */
    'content_path' => storage_path('content'),

    /**
     * The backup destination options
     */
    'destination' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
    ],

    /**
     * Were to store the backup registry
     *
     * This is used to keep track of the backups and not have to scan the disk every time
     */
    'registry_directory' => storage_path('backups'),

    /**
     * The backup name generator
     *
     * Must implement Itiden\Backup\Contracts\BackupNameGenerator
     */
    'name_generator' => \Itiden\Backup\Actions\GenerateName::class,

    /**
     * The path to the temp directory
     */
    'temp_path' => storage_path('framework/statamic-backup'),

    /**
     * The maximum number of backups to keep
     *
     * when exceeded the oldest backup will be deleted
     */
    'max_backups' => 10,

    /**
     * The backup password
     *
     * set to null to disable password protection
     */
    'password' => env('BACKUP_PASSWORD', null),

    /**
     * The backup schedule options
     *
     * set to null to disable automatic backups
     * frequency can be any of the laravel schedule frequencies
     * time should be what the frequency expects
     *
     * see https://laravel.com/docs/10.x/scheduling#schedule-frequency-options
     */
    'schedule' => [
        'frequency' => 'daily',
        // 'time' => '03:00',
    ],

    /**
     * The backup repository
     *
     * the repository handles resolving and managing backups
     */
    'repository' => \Itiden\Backup\Repositories\FileBackupRepository::class,

    /**
     * The backup steps to use
     *
     * these are the steps/pipes that will be used to backup your site
     * You can add your own here
     *
     * All pipes are expected to be instances of Itiden\Backup\Abtracts\BackupPipe
     */
    'pipeline' => [
        Itiden\Backup\Pipes\Content::class,
        Itiden\Backup\Pipes\Assets::class,
        Itiden\Backup\Pipes\Users::class,
    ],
];
