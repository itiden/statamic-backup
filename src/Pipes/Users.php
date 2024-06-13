<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

class Users extends BackupPipe
{
    public static function getKey(): string
    {
        return 'users';
    }

    public function restore(string $restoringFromPath, Closure $next)
    {
        $destination = $this->getConfig()['users']->directory();
        $users = $this->getDirectoryPath($restoringFromPath);

        File::copyDirectory($users, $destination);

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next)
    {
        $zip->addDirectory($this->getConfig()['users']->directory(), static::getKey());

        return $next($zip);
    }

    private function getConfig()
    {
        $config = require base_path('vendor').'/statamic/cms/config/stache.php';
        $published = config('statamic.stache.stores');

        $nativeStores = collect($config['stores'])
            ->reject(fn ($config, $key) => $key === 'users' && config('statamic.users.repository') !== 'file')
            ->map(function ($config, $key) use ($published) {
                return array_merge($config, $published[$key] ?? []);
            });

        // Merge in any user defined stores that aren't native.
        $stores = $nativeStores->merge(collect($published)->diffKeys($nativeStores));

        $stores = $stores->map(function ($config) {
            return app($config['class'])->directory($config['directory'] ?? null);
        });

        return $stores->all();
    }
}
