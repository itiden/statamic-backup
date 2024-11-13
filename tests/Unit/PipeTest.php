<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Pipes\Assets;
use Itiden\Backup\Pipes\Content;
use Itiden\Backup\Pipes\Users;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\Stache;

uses()->group('pipes');

test('backup pipes can pass zipper instance', function (string $pipe) {
    $temp_zip = config('backup.temp_path') . '/backup.zip';

    $zipper = Zipper::open($temp_zip);
    expect(app()->make($pipe)->backup($zipper, fn ($z) => $z))->toBeInstanceOf(Zipper::class);

    $zipper->close();

    File::delete($temp_zip);
})->with([
    Users::class,
    Content::class,
    Assets::class,
]);

test('restore pipes can pass closure', function (string $pipe) {
    app(BackupRepository::class)->empty();
    $fixtues_path = __DIR__ . '/../__fixtures__';
    $fixtures_backup_path = Storage::path(config('backup.destination.path'));
    File::copyDirectory($fixtues_path, $fixtures_backup_path);

    $path = config('backup.temp_path') . '/backup';
    expect(app()->make($pipe)->restore($path, fn ($z) => $z))->toBe($path);

    File::deleteDirectory($fixtues_path);
    File::copyDirectory($fixtures_backup_path, $fixtues_path);
})->with([
    Users::class,
    Content::class,
    Assets::class,
]);

test('can skip a pipe', function () {
    /** @var Users::class $pipe */
    $pipe = app()->make(Users::class);

    $callable = function ($z) {
        return $z;
    };

    File::deleteDirectory(Stache::store('users')->directory());

    $zipper = Zipper::open(config('backup.temp_path') . '/backup.zip');

    $pipe->backup(zip: $zipper, next: $callable);


    expect($zipper->getMeta())->toHaveKey(Users::class);
    expect($zipper->getMeta()[Users::class])->toHaveKey('skipped', 'No users found.');

    $zipper->close();
});
