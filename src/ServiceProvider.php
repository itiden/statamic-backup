<?php

namespace Itiden\Backup;

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Console\Commands\BackupCommand;
use Itiden\Backup\Console\Commands\RestoreCommand;
use Itiden\Backup\Http\Controllers\BackupController;
use Itiden\Backup\Http\Controllers\CreateBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'itiden-backup';

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__ . '/../config/backup.php' => config_path('backup.php'),
        ]);

        Permission::extend(function () {
            Permission::register('manage backups')->label('Manage Backups')
                ->children([
                    Permission::make('create backups')->label('Create Backups'),
                    Permission::make('restore from backups')->label('Restore From Backups'),
                ]);
        });

        Nav::extend(function ($nav) {
            $nav->content('Backups')
                ->can('manage backups')
                ->section('Tools')
                ->route('itiden.backup.index')
                ->icon('table');
        });

        $this->commands([
            RestoreCommand::class,
            BackupCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/backup.php',
            'backup'
        );
    }
}
