<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use Statamic\Assets\AssetContainer as Container;
use Statamic\Facades\AssetContainer;

final readonly class Assets extends BackupPipe
{
    public static function getKey(): string
    {
        return 'assets';
    }

    public function restore(string $restoringFromPath, Closure $next): string
    {
        AssetContainer::all()
            ->filter(static::isLocal(...))
            ->each(function (Container $container) use ($restoringFromPath): void {
                File::cleanDirectory($container->diskPath());
                File::copyDirectory(
                    "{$this->getDirectoryPath($restoringFromPath)}/{$container->handle()}",
                    $container->diskPath(),
                );
            });

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next): Zipper
    {
        AssetContainer::all()
            ->filter(static::isLocal(...))
            ->each(function (Container $container) use ($zip): void {
                $zip->addDirectory($container->diskPath(), static::getKey() . '/' . $container->handle());
            });

        return $next($zip);
    }

    public static function isLocal(Container $container): bool
    {
        return Config::array('filesystems.disks.' . $container->diskHandle())['driver'] === 'local';
    }
}
