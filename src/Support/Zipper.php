<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Closure;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

class Zipper
{
    /**
     * Create a zip archive and add files to it.
     * The zip archive will be closed after the callback is executed.
     *
     * @param string $path
     * @param Closure $cb
     * @param string|null $password
     *
     * @return ZipArchive
     */
    public static function zip(string $path, Closure $cb, ?string $password = null): ZipArchive
    {
        File::ensureDirectoryExists(dirname($path));

        $zip = new ZipArchive();

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $cb($zip);

        if ($password) {
            $zip->setPassword($password);
        }

        $zip->close();

        return $zip;
    }

    /**
     * Add a directory and all its contents to a zip archive with a prefix.
     *
     * @param string $path
     * @param ZipArchive $zip
     * @param string $prefix
     */
    public static function zipDir(string $path, ZipArchive $zip, string $prefix)
    {
        collect(File::allFiles($path))->each(function (SplFileInfo $file) use ($zip, $prefix) {
            $zip->addFile($file->getPathname(), $prefix . '/' . $file->getRelativePathname());
        });
    }


    /**
     * Extract a zip archive to a directory.
     *
     * @param string $path
     * @param string $to
     *
     * @return string
     */
    public static function unzip(string $path, string $to): string
    {
        $zip = new ZipArchive();

        $zip->open($path);

        $zip->extractTo($to);

        $zip->close();

        return $to;
    }
}
