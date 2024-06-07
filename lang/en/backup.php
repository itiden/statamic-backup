<?php

return [

    'title' => 'Backups',

    'create' => 'Create Backup',
    'failed' => 'Failed to create backup on :date',
    'backup_started' => 'Starting backup...',
    'created' => 'Backup :name created',

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
