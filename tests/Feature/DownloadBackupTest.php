<?php

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses()->group('download backup');

beforeEach(function () {
    app(BackupRepository::class)->empty();
});

it('cant be downloaded by a guest', function () {
    $backup = Backuper::backup();

    $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($responseJson->status())->toBe(401);
});

it('cant be downloaded by a user without permissons a backup', function () {
    $backup = Backuper::backup();

    actingAs(user());

    $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($responseJson->status())->toBe(403);
});

it('can be downloaded by a user with download backups permission', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('admin')->save();

    actingAs($user);

    $response = get(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($response)->assertDownload();
});

it('adds a download action to the metadata', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('admin')->save();

    actingAs($user);

    get(cp_route('api.itiden.backup.download', $backup->timestamp));

    $metadata = $backup->getMetadata();

    expect($metadata->getDownloads())->toHaveCount(1);
    expect($metadata->getDownloads()[0]->getUser()->id)->toBe($user->id);
});
