<?php

use Itiden\Backup\BackuperManager;
use Itiden\Backup\Drivers\AssetsRestorer;
use Itiden\Backup\Drivers\ContentRestorer;
use Itiden\Backup\Contracts\Restorer as RestorerContract;
use Itiden\Backup\Exceptions\ManagerException;
use Itiden\Backup\RestorerManager;

dataset('managers', [
    BackuperManager::class,
    RestorerManager::class
]);

uses()->group('managers');

it('can get drivers keys', function (string $manager) {
    expect(new $manager())->getDrivers()
        ->toEqual([
            ContentRestorer::getKey(),
            AssetsRestorer::getKey(),
        ]);
})->with('managers');

it('can get driver', function (string $manager, string $client) {
    expect(new $manager())->driver($client)->toBeInstanceOf(RestorerContract::class);
})
    ->with('managers')
    ->with([
        'content',
        'assets'
    ]);

it('throws an error when accessing driver that doesn\'t exist', function (string $manager) {
    expect(fn () => (new $manager())->driver('idontexist'))
        ->toThrow(ManagerException::class);
})->with('managers');
