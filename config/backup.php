<?php

return [

    /**
     * The backup destination options
     */
    'destination' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
    ],

    /**
     * The upload chunk size
     */
    'chunk_size' => 2 * 1024 * 1024,

    /**
     * The directory where backup will put metadata files
     */
    'metadata_path' => storage_path('statamic-backups'),

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
     * time should be parameters the frequency expects
     *
     * see https://laravel.com/docs/10.x/scheduling#schedule-frequency-options
     */
    'schedule' => [
        'frequency' => 'daily',
        // 'time' => '03:00',
    ],

    /**
     * The backup name resolver
     *
     * the resolver handles generating and parsing backup names
     */
    'name_resolver' => \Itiden\Backup\GenericBackupNameResolver::class,

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
        Itiden\Backup\Pipes\StacheData::class,
        Itiden\Backup\Pipes\Assets::class,
        Itiden\Backup\Pipes\Users::class,
    ],

    /**
     * The stache stores that should be backed up by the StacheData pipe, by key
     */
    'stache_stores' => [
        // stores configurations
        'asset-containers',
        'collections',
        'navigation',
        'taxonomies',
        // content stores
        'terms',
        'entries',
        'globals',
        'global-variables',
        'collection-trees',
        'nav-trees',
        'form-submissions',
    ],
];
