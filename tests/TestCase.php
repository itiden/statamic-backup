<?php

namespace Itiden\Backup\Tests;

use Facades\Statamic\Version;
use Illuminate\Support\Facades\File;
use Itiden\Backup\ServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Statamic\Console\Processes\Composer;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

class TestCase extends TestbenchTestCase
{
    protected bool $shouldFakeVersion = true;

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'itiden/statamic-backup' => [
                'id' => 'itiden/statamic-backup',
                'namespace' => 'Itiden\\Backup',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        /**
         * Set statamic config values
         */
        $app['config']->set('backup.password', null);

        /**
         * Set backup path config values
         */
        $app['config']->set('backup.content_path', __DIR__ . '/__fixtures__/content');
        $app['config']->set('backup.temp_path', __DIR__ . '/__fixtures__/temp');

        /**
         * Set statamic config values
         */
        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__ . '/__fixtures__/users',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->shouldFakeVersion) {
            Version::shouldReceive('get')
                ->andReturn(Composer::create(__DIR__ . '/../')->installedVersion(Statamic::PACKAGE));

            // $this->addToAssertionCount(-1);
        }

        /**
         * Create directories
         */
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/content');
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/users');
    }
}
