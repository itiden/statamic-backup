<?php

use Itiden\Backup\BackuperManager;
use Itiden\Backup\Clients\AssetsRestorer;
use Itiden\Backup\Clients\ContentRestorer;
use Itiden\Backup\Contracts\Restorer as RestorerContract;
use Itiden\Backup\Exceptions\ManagerException;
use Itiden\Backup\RestorerManager;

dataset('managers', [
    BackuperManager::class,
    RestorerManager::class
]);

describe('managers', function () {
    it('can get client keys', function (string $manager) {

        expect(new $manager)->getClients()
            ->toEqual([
                ContentRestorer::getKey(),
                AssetsRestorer::getKey(),
            ]);
    })
        ->with('managers');

    it('can get client', function (string $manager, string $client) {
        expect(new $manager)->client($client)->toBeInstanceOf(RestorerContract::class);
    })
        ->with('managers')
        ->with([
            'content',
            'assets'
        ]);

    it('throws an error when accessing client doesn\'t exist', function (string $manager) {
        expect(fn () => (new $manager)->client('idontexist'))->toThrow(ManagerException::class);
    })
        ->with('managers');
});
