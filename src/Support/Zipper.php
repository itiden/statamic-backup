<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Closure;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

class Zipper
{
    public static function zipDir(string $path, ZipArchive $zip, string $prefix)
    {
        collect(File::allFiles($path))->each(function (SplFileInfo $file) use ($zip, $prefix) {
            $zip->addFile($file->getPathname(), $prefix . '/' . $file->getRelativePathname());
        });
    }

    public static function zip(string $path, Closure $cb): ZipArchive
    {
        $zip = new ZipArchive();

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $cb($zip);

        $zip->close();

        return $zip;
    }

    public static function unzip(string $path, string $to): string
    {
        $zip = new ZipArchive();

        $zip->open($path);

        $zip->extractTo($to);

        $zip->close();

        return $to;
    }
}
