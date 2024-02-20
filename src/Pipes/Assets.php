<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use Statamic\Assets\AssetContainer as Container;
use Statamic\Facades\AssetContainer;

class Assets extends BackupPipe
{
    public static function getKey(): string
    {
        return 'assets';
    }

    public function restore(string $backupPath): void
    {
        AssetContainer::all()
            ->filter(static::isLocal(...))
            ->each(function ($container) use ($backupPath) {
                File::cleanDirectory($container->diskPath());
                File::copyDirectory("{$this->getDirectoryPath($backupPath)}/{$container->handle()}", $container->diskPath());
            });
    }

    public function backup(Zipper $zip): void
    {
        AssetContainer::all()
            ->filter(static::isLocal(...))
            ->each(function ($container) use ($zip) {
                $zip->addDirectory($container->diskPath(), static::getKey() . '/' . $container->handle());
            });
    }

    public static function isLocal(Container $container): bool
    {
        return config('filesystems.disks.' . $container->diskHandle())['driver'] === 'local';
    }
}
