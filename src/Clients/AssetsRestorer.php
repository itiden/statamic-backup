<?php

namespace Itiden\Backup\Clients;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\Restorer;
use Itiden\Backup\Helpers;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\AssetContainer;
use ZipArchive;

class AssetsRestorer implements Restorer
{
    public static function getKey(): string
    {
        return 'assets';
    }

    public function restore(string $content): bool
    {
        AssetContainer::all()->each(function ($container) use ($content) {
            File::copyDirectory("{$content}/{$container->handle()}", $container->diskPath());
        });

        return true;
    }

    public function backup(ZipArchive $zip): bool
    {
        if (!request()->input('include_assets', false)) {
            return true;
        }

        $zip->addEmptyDir('assets');

        AssetContainer::all()->each(function ($container) use ($zip) {
            Zipper::zipDir($container->diskPath(), $zip, 'assets/' . $container->handle() . '/');
        });

        return true;
    }
}
