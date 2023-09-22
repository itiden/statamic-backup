<?php

namespace Itiden\Backup;

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Console\Commands\BackupCommand;
use Itiden\Backup\Console\Commands\RestoreCommand;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->publishes([
            __DIR__ . '/../config/backup.php' => config_path('backup.php'),
        ]);

        $this->registerCpRoutes(function () {
            Route::get('/download-content', DownloadBackupController::class)
                ->name('itiden.download.content');
        });

        Permission::extend(function () {
            Permission::register('download content')->label('Download Content');
        });

        Nav::extend(function ($nav) {
            $nav->content('Download Content')
                ->section('Tools')
                ->url(cp_route('itiden.download.content', ['include_assets' => true]))
                ->icon('download');
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
