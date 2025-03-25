<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests\Testbench\Bootstrappers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;

final readonly class ComposerBootstrapper
{
    /**
     * Perform any additional setup after loading the configuration.
     */
    public function bootstrap(Application $app): void
    {
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
      
        static::buildStatamicScripts($app);

        if (!file_exists($app->basePath('/users/test@example.com.yaml'))) {
            \Itiden\Backup\Tests\user()->addRole('super admin');
        }
    }

    private static function buildStatamicScripts(Application $app) {
        // Path to the directory where you want to run the npm command
        $directory =  $app->basePath('/vendor/statamic/cms');
        $buildPath = $directory . '/resources/dist';

        if (file_exists($buildPath . '/build/manifest.json')) {
            static::copyStatamicScripts($buildPath, $app);
            // Reset the working directory to the original one
            return;
        }

        // Store the original working directory
        $original_directory = getcwd();

        // Change the working directory to the desired one
        chdir($directory);

        // Run the npm command
        $output = [];
        $return_var = 0;
        exec('npm install && npm run build', $output, $return_var);

        // Check if command was successful
        if ($return_var === 0) {
            echo "Build successful!";
        } else {
            echo "Build failed! Errors: " . implode("\n", $output);
        }

        static::copyStatamicScripts($buildPath, $app);

        // Reset the working directory to the original one
        chdir($original_directory);
    }

    private static function copyStatamicScripts(string $statamicBuildDir, Application $app) {
        static::copyDirectory($statamicBuildDir, $app->basePath('public/vendor/statamic/cp/'));
    }

    private static function copyDirectory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
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
