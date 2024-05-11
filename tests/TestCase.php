<?php

namespace Itiden\Backup\Tests;

use Illuminate\Support\Facades\File;
use Itiden\Backup\ServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Testing\AddonTestCase;

class TestCase extends AddonTestCase
{
    protected bool $shouldFakeVersion = true;

    protected string $addonServiceProvider = ServiceProvider::class;

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

        /**
         * Create directories
         */
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/content');
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/users');
    }
}
