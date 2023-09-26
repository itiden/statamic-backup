<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Closure;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

class Zipper
{
    public static function zipDir(string $path, ZipArchive $zip, string $prefix): bool
    {
        collect(File::allFiles($path))->each(function (SplFileInfo $file) use ($zip, $prefix) {
            $zip->addFile($file->getPathname(), $prefix . '/' . $file->getRelativePathname());
        });

        return true;
    }

    public static function zip(Closure $cb): string
    {
        $zip = new ZipArchive();

        $zip->open(storage_path('temp.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $cb($zip);

        $zip->close();

        return storage_path('temp.zip');
    }
}
