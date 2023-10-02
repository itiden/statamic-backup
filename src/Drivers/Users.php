<?php

declare(strict_types=1);

namespace Itiden\Backup\Drivers;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Support\Zipper;

class Users implements BackupDriver
{
    public static function getKey(): string
    {
        return 'users';
    }

    public function restore(string $users): void
    {
        $destination = config('statamic.stache.stores.users.directory');

        File::copyDirectory($users, $destination);
    }

    public function backup(Zipper $zip): void
    {
        $zip->addDirectory(config('statamic.stache.stores.users.directory'), static::getKey());
    }
}
