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

    public function bootAddon()
    {
        $this->publishes([
            __DIR__ . '/../config/backup.php' => config_path('backup.php'),
        ]);

        $this->registerCpRoutes(function () {
            Route::get('/backup/download/{timestamp}', DownloadBackupController::class)
                ->name('itiden.backup.download');

            Route::get('/backup', BackupController::class)
                ->name('itiden.backup.index');

            Route::post('/backup', CreateBackupController::class)
                ->name('itiden.backup.create');
        });

        Permission::extend(function () {
            Permission::register('download backups')->label('Download Backup');
        });

        Nav::extend(function ($nav) {
            $nav->content('Backups')
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
