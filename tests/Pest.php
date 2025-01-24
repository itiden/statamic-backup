<?php

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Tests\TestCase;
use Statamic\Auth\User as StatamicUser;
use Statamic\Facades\Role;
use Statamic\Facades\User;

uses(TestCase::class)
    ->afterEach(fn() => app(BackupRepository::class)->empty())
    ->in(__DIR__);

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
