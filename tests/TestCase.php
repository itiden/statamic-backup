<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Enums\State;
use Itiden\Backup\ServiceProvider;
use Itiden\Backup\StateManager;
use Statamic\Testing\AddonTestCase;

class TestCase extends AddonTestCase
{
    protected bool $shouldFakeVersion = true;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app): void
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
    }

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Set the state to idle - in case of a previous run that failed
         */
        app(StateManager::class)->setState(State::Idle);

        /**
         * Create directories
         */
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/content');
        File::ensureDirectoryExists(__DIR__ . '/__fixtures__/users');
    }
}
