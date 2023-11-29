<?php

use Itiden\Backup\BackuperManager;
use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Drivers\Assets;
use Itiden\Backup\Drivers\Content;
use Itiden\Backup\Drivers\Users;
use Itiden\Backup\Exceptions\ManagerException;
use Itiden\Backup\RestorerManager;

dataset('managers', [
    BackuperManager::class,
    RestorerManager::class
]);

uses()->group('managers');

it('can get drivers keys', function (string $manager) {
    expect(new $manager(app(BackupRepository::class)))->getDrivers()
        ->toEqual([
            Content::getKey(),
            Assets::getKey(),
            Users::getKey(),
        ]);
})->with('managers');

it('can get driver', function (string $manager, string $client) {
    expect(new $manager(app(BackupRepository::class)))->driver($client)->toBeInstanceOf(BackupDriver::class);
})
    ->with('managers')
    ->with([
        'content',
        'assets',
        'users',
    ]);

it('throws an error when accessing driver that doesn\'t exist', function (string $manager) {
    expect(fn () => (new $manager(app(BackupRepository::class)))->driver('idontexist'))
        ->toThrow(ManagerException::class);
})->with('managers');
