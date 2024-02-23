<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Console\Scheduling\Schedule;
use Itiden\Backup\Console\Commands\BackupCommand;
use Itiden\Backup\Console\Commands\ClearFilesCommand;
use Itiden\Backup\Console\Commands\RestoreCommand;
use Itiden\Backup\Widgets\IsBackedUpWidget;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'itiden-backup';

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $widgets = [
        IsBackedUpWidget::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/backup.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__ . '/../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');

        $this->setUpPermissions();

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
            ClearFilesCommand::class,
        ]);
    }

    protected function schedule(Schedule $schedule)
    {
        if (!config('backup.schedule')) {
            return;
        }

        $frequency = config('backup.schedule.frequency');

        $schedule->command('statamic:backup')->$frequency(config('backup.schedule.time'));
    }

    public function register()
    {
        $this->app->bind(
            \Itiden\Backup\Contracts\Repositories\BackupRepository::class,
            \Itiden\Backup\Repositories\FileBackupRepository::class
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/backup.php',
            'backup'
        );
    }

    private function setUpPermissions()
    {
        Permission::extend(function () {
            Permission::group('itiden-backup', 'Backup', function () {
                Permission::register('manage backups')
                    ->label('Manage Backups')
                    ->children([
                        Permission::make('create backups')->label('Create Backups'),
                        Permission::make('restore backups')->label('Restore From Backups'),
                        Permission::make('download backups')->label('Download Backups'),
                        Permission::make('delete backups')->label('Delete Backups'),
                    ]);
            });
        });
    }
}
