<?php

declare(strict_types=1);

namespace Itiden\Backup\Drivers;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\AssetContainer;
use ZipArchive;

class Assets implements BackupDriver
{
    public static function getKey(): string
    {
        return 'assets';
    }

    public function restore(string $content): void
    {
        AssetContainer::all()->each(function ($container) use ($content) {
            File::cleanDirectory($container->diskPath());
            File::copyDirectory("{$content}/{$container->handle()}", $container->diskPath());
        });
    }

    public function backup(ZipArchive $zip): void
    {
        $zip->addEmptyDir(static::getKey());

        AssetContainer::all()->each(function ($container) use ($zip) {
            Zipper::zipDir($container->diskPath(), $zip, static::getKey() . '/' . $container->handle() . '/');
        });
    }
}
