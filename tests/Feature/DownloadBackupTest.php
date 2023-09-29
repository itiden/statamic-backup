<?php

use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses()->group('download backup');

beforeEach(function () {
    Backuper::clearBackups();
});

it('cant be downloaded by a guest', function () {
    $backup = Backuper::backup();

    $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));
    $response = get(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($responseJson->status())->toBe(401);
    expect($response->status())->toBe(302);
});

it('cant be downloaded by a user without permissons a backup', function () {
    $backup = Backuper::backup();

    actingAs(user());

    $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));
    $response = get(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($responseJson->status())->toBe(403);
    expect($response->status())->toBe(302);
});

it('can be downloaded by a user with download backups permission', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('admin')->save();

    actingAs($user);

    $response = get(cp_route('api.itiden.backup.download', $backup->timestamp));

    expect($response)->assertDownload();
});
