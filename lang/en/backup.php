<?php

return [

    'title' => 'Backups',

    'create' => 'Create Backup',
    'failed' => 'Failed to create backup on :date',
    'no_backups' => 'There is no backups yet',
    'backup_started' => 'Starting backup...',
    'success' => 'Backed up successfully',

    'state' => [
        'idle' => 'Idle',
        'initializing' => 'Fetching initial status',
        'backup_in_progress' => 'Backup in progress',
        'restore_in_progress' => 'Restore in progress',
        'backup_completed' => 'Backup completed',
        'restore_completed' => 'Restore completed',
        'backup_failed' => 'Backup failed',
        'restore_failed' => 'Restore failed',
    ],

    'upload' => [
        'label' => 'Upload Backup',

        'cancelled' => 'Upload Cancelled',
    ],

    'download' => [
        'label' => 'Download',
    ],

    'destroy' => [
        'label' => 'Delete',

        'confirm_title' => 'Remove backup',
        'confirm_body' => 'Are you sure you want to remove the backup from :name ?',

        'success' => 'Deleted :name',
    ],

    'restore' => [
        'label' => 'Restore',

        'confirm_title' => 'Restore backup',
        'confirm_body' => 'Are you sure you want to restore your site to the state it was :name ?',

        'started' => 'Starting restore...',
        'started_name' => 'Starting restore :name...',

        'success' => 'Successfully restored',
        'failed' => 'Restore to backup :name failed',

    ],

];
