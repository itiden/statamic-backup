<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Auth\User as StatamicUser;
use Statamic\Facades\Role;
use Statamic\Facades\User;

/**
 * Split a file into chunks
 *
 * @return Collection<string>
 */
function chunk_file(string $file, string $path, int $buffer = 1024): Collection
{
    File::ensureDirectoryExists($path);

    $fileHandle = fopen($file, 'r');
    $fileSize = File::size($file);
    $totalChunks = ceil($fileSize / $buffer);

    $chunks = collect();

    $fileName = basename($file);

    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunk = fread($fileHandle, $buffer);

        $chunkPath = $path . $fileName . ".part$i";

        File::put($chunkPath, $chunk);

        $chunks->push($chunkPath);
    }
    fclose($fileHandle);

    return $chunks;
}

/**
 * Generate a user
 */
function user(): StatamicUser
{
    Role::make('user')
        ->title('User')
        ->addPermission('access cp')
        ->save();

    Role::make('admin')
        ->title('Admin')
        ->addPermission('access cp')
        ->addPermission('manage backups')
        ->addPermission('create backups')
        ->addPermission('download backups')
        ->save();

    Role::make('super admin')
        ->title('Super admin')
        ->addPermission('access cp')
        ->addPermission('create backups')
        ->addPermission('manage backups')
        ->addPermission('download backups')
        ->addPermission('restore backups')
        ->addPermission('delete backups')
        ->save();

    return User::make()
        ->email('test@example.com')
        ->set('password', 'password')
        ->set('roles', ['user'])
        ->save();
}
