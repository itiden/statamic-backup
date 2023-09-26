<?php

declare(strict_types=1);

namespace Itiden\Backup\Drivers;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Support\Zipper;
use ZipArchive;

class Users implements BackupDriver
{
    public static function getKey(): string
    {
        return 'users';
    }

    public function restore(string $users): void
    {
        $destination = config('statamic.stache.directories.users');

        File::copyDirectory($users, $destination);
    }

    public function backup(ZipArchive $zip): void
    {
        Zipper::zipDir(config('statamic.stache.stores.users.directory'), $zip, static::getKey());
    }
}
