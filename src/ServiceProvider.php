<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Console\Scheduling\Schedule;
use Itiden\Backup\Console\Commands\BackupCommand;
use Itiden\Backup\Console\Commands\ClearFilesCommand;
use Itiden\Backup\Console\Commands\RestoreCommand;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Events\BackupDeleted;
use Statamic\Auth\Permissions as PermissionContract;
use Statamic\CP\Navigation\Nav as Navigation;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

final class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'itiden-backup';

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'src/main.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $listen = [
        BackupDeleted::class => [
            \Itiden\Backup\Listeners\BackupDeletedListener::class,
        ],
    ];

    public function bootAddon(): void
    {
        $this->publishes([
            __DIR__ . '/../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');

        $this->configurePermissions();
        $this->configureNavigation();
        $this->configureCommands();
    }

    public function schedule(Schedule $schedule): void
    {
        if (!config('backup.schedule')) {
            return;
        }

        $frequency = config('backup.schedule.frequency');

        $schedule->command('statamic:backup')->$frequency(config('backup.schedule.time'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/backup.php', 'backup');

        $this->app->bind(BackupRepository::class, config('backup.repository'));
        $this->app->bind(BackupNameResolver::class, config('backup.name_resolver'));
    }

    private function configureCommands(): void
    {
        $this->commands([
            RestoreCommand::class,
            BackupCommand::class,
            ClearFilesCommand::class,
        ]);
    }

    private function configureNavigation(): void
    {
        Nav::extend(static function (Navigation $nav): void {
            $nav
                ->content('Backups')
                ->can('manage backups')
                ->section('Tools')
                ->route('itiden.backup.index')
                ->icon('table');
        });
    }

    private function configurePermissions(): void
    {
        Permission::extend(static function (PermissionContract $permission): void {
            $permission->group('itiden-backup', 'Backup', static fn() => $permission
                ->register('manage backups')
                ->label('Manage Backups')
                ->children([
                    $permission->make('create backups')->label('Create Backups'),
                    $permission->make('restore backups')->label('Restore From Backups'),
                    $permission->make('download backups')->label('Download Backups'),
                    $permission->make('delete backups')->label('Delete Backups'),
                ]));
        });
    }
}
