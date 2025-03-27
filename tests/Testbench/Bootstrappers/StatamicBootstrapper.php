<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests\Testbench\Bootstrappers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use RuntimeException;

final readonly class StatamicBootstrapper
{
    /**
     * Perform any additional setup after loading the configuration.
     */
    public function bootstrap(Application $app): void
    {
        $app['config']->set('statamic.editions.pro', true);

        // Configure laravel to use filebased users from statamic
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('auth.providers.users.driver', 'statamic');

        $packagePath = realpath(__DIR__ . '/../../../');

        $applicationPath = $app->basePath();

        // Paths to composer.json and composer.lock in the addon
        $composerJsonPath = $packagePath . '/composer.json';
        $composerLockPath = $packagePath . '/composer.lock';

        // Destination paths in the application
        $destinationComposerJson = $applicationPath . '/composer.json';
        $destinationComposerLock = $applicationPath . '/composer.lock';

        // Check if the files exist before copying
        if (file_exists($composerJsonPath) && file_exists($composerLockPath)) {
            // Copy composer.json and composer.lock to the application path
            copy($composerJsonPath, $destinationComposerJson);
            copy($composerLockPath, $destinationComposerLock);
        } else {
            throw new \RuntimeException('composer.json or composer.lock not found in the addon directory.');
        }

        static::copyStatamicScripts($app);

        static::addBackupToInstalledPackages($app);

        if (!file_exists($app->basePath('/users/test@example.com.yaml'))) {
            $user = \Itiden\Backup\Tests\user();
            $user->makeSuper();
            $user->save();
        }
    }

    /**
     * This is required for statamic to discover the addon.
     */
    private static function addBackupToInstalledPackages(Application $app): void
    {
        $installed = File::json($app->basePath('vendor/composer/installed.json'));

        $installed['packages']['backup'] = [
            ...File::json(__DIR__ . '/../../../composer.json'),
            'version' => '1.0.0',
            'autoload' => [
                'psr-4' => [
                    'Itiden\\Backup\\' => realpath(__DIR__ . '/../../../src'),
                ],
            ],
        ];

        File::put($app->basePath('vendor/composer/installed.json'), json_encode($installed));
    }

    private static function copyStatamicScripts(Application $app): void
    {
        $buildPath = $app->basePath('/vendor/statamic/cms/resources/dist');

        if (!file_exists($buildPath . '/build/manifest.json')) {
            throw new RuntimeException('Statamic assets not found');
        }

        static::copyDirectory($buildPath, $app->basePath('public/vendor/statamic/cp/'));
    }

    private static function copyDirectory($source, $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 00755, true);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourceFile = $source . '/' . $file;
                $destinationFile = $destination . '/' . $file;
                if (is_dir($sourceFile)) {
                    static::copyDirectory($sourceFile, $destinationFile);
                } else {
                    copy($sourceFile, $destinationFile);
                }
            }
        }
    }
}
